# ADR-008 — Login con Google OAuth sin creación de cuentas

- **Estado:** Aceptado e implementado
- **Fecha:** 2026-11-24
- **Decisores:** Equipo EwiflorWeb + usuario
- **Módulo afectado:** `Modules/Auth`, `config/services.php`, `.env.example`
- **Relacionado:** ADR-004 (bloqueo por intentos fallidos)

## Contexto

El login tradicional usa `nombre_usuario` o `correo` + contraseña y aplica
bloqueo por intentos fallidos (ADR-004). Se pidió la opción de entrar con la
cuenta de Google corporativa, sin permitir que cualquier persona con correo
de Google se registre automáticamente en el sistema.

## Decisión

Google Socialite se usa **solo para autenticar cuentas ya existentes**:

1. `usuario` incorpora `google_id` (único, nullable), `proveedor`
   (`tradicional` | `google` | `ambos`) y `avatar_url`, añadidos en la
   migración `Modules/Auth/Database/Migrations/2026_11_20_000002_google_oauth_usuarios.php`.
2. `GET /login/google` inicia el flujo; `GET /login/google/callback`
   (rutas `login.google` y `login.google.callback`) lo completa.
3. El callback busca el usuario por correo (normalizado a minúsculas). Si el
   correo no existe o la cuenta está inactiva, se rechaza con un error
   genérico; **nunca se crea un usuario nuevo**.
4. Si el correo existe y no tiene `google_id`, se vincula y se marca
   `proveedor = ambos`. Si ya tiene el mismo `google_id`, entra directo. Si
   tiene otro `google_id`, se rechaza para evitar apropiación de cuenta.
5. El login por OAuth respeta `estaBloqueado()` y registra el éxito con
   `registrarExitoso(...)`, de modo que no bypassa ADR-004.
6. Se regenera el ID de sesión y el destino `return_to` se valida como ruta
   interna relativa: se rechazan URLs absolutas, `//evil.com`, barras
   invertidas, caracteres de control y hosts en `parse_url`.
7. Las credenciales se leen de `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` y
   `GOOGLE_CALLBACK_URL` (`config/services.php`); no se versionan valores.

## Consecuencias

- Positivo: reduce contraseñas compartidas y mantiene el control de altas en
  manos del administrador.
- Positivo: un usuario tradicional puede vincular Google conservando su
  contraseña; el estado `ambos` lo refleja.
- Negativo: si una persona cambia su correo en Google, debe intervenir un
  administrador para actualizar `usuario.correo`.
- Negativo: el proyecto depende de Socialite y de la configuración del
  proveedor en cada entorno; la CI no ejerce el flujo OAuth (requiere un
  proveedor externo), por lo que se cubre con tests unitarios del helper de
  destino seguro y revisión manual.
