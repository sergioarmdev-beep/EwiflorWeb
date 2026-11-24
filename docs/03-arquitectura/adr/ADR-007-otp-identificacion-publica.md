# ADR-007 — OTP por correo en la identificación pública

- **Estado:** Aceptado (aprobado por el usuario el 2026-11-24)
- **Fecha:** 2026-11-24
- **Última revisión:** 2026-11-24
- **Decisores:** Equipo EwiflorWeb + usuario
- **Módulo afectado:** `Modules/ServicioTecnico/`

## Contexto

El panel público de Servicio Técnico identificaba a un cliente solo con nombre y celular. Esos dos datos son fácilmente observables y cualquiera podía abrir la sesión de otro cliente y consultar sus compras, garantías y solicitudes. La oleada 3 cerró el IDOR interno y dejó explícitamente diferido este punto.

El proyecto no tiene proveedor de SMS ni de WhatsApp API: `.env.example` solo define `MAIL_MAILER=log` y `WA_CONTACT` para enlaces `wa.me`. El modelo `cliente` sí tiene `correo`, aunque puede estar vacío en datos antiguos.

## Decisión

1. **Canal:** el código se entrega por correo a `cliente.correo` mediante `CodigoVerificacion` (Mail). En desarrollo queda en el log; en producción se requiere configurar un mailer real. No se introduce dependencia ni proveedor nuevo.
2. **Formulario:** la identificación pasa a exigir nombre, celular y correo. El correo se compara con el registrado (insensible a mayúsculas/espacios) con el mismo criterio que nombre y teléfono en `SolicitudService::buscarCliente()`. Si no coincide, no se envía nada y se muestra un error genérico.
3. **Estado pendiente:** `VerificacionOtp` guarda en sesión el `id_cliente`, los datos y el hash del código (nunca el código en claro), con vigencia de 10 minutos y 5 intentos. Expirar o agotar los intentos descarta el estado y obliga a empezar de nuevo.
4. **Dos pasos:** `validar()` solo emite y envía el código; `verificarCodigo()` lo canjea por la sesión del cliente. Se regenera el ID de sesión antes de llamar a `SesionCliente::establecer()`. El middleware `sesion.cliente.publica` no cambia: sigue exigiendo `serviciotecnico.cliente_id`.
5. **Reenvío y límites:** `reenviar()` emite un código nuevo y reinicia el contador de intentos. Las rutas POST conservan `throttle` (10/min para validar y verificar, 3/min para reenviar), además del límite por sesión.
6. **Sin correo registrado:** el cliente debe indicar el correo con el que está registrado. No existe ruta alternativa de verificación; los datos desactualizados deben corregirse con Ewiflor. La validación se mantiene obligatoria (no hay acceso sin OTP).

## Consecuencias

- El alta de una solicitud pública sigue requiriendo sesión verificada; `crearPublica()` y el middleware no necesitan cambios.
- Los tests PG cubren emisión, acierto, error, expiración, agotamiento, reenvío y correo no coincidente (`VerificacionOtpPublicaTest`).
- Si un cliente tiene el correo mal registrado, no puede identificarse hasta corregirlo: es una restricción deliberada para no debilitar la verificación.
- El código viaja por correo en texto plano, como es habitual en OTP; el hash en sesión evita que quede expuesto en el navegador o en logs de aplicación.
- Queda fuera de alcance un proveedor SMS/WhatsApp y la verificación de propiedad del celular.

## Estado de implementación

- [x] Decisión del canal y del caso sin correo (usuario, 2026-11-24).
- [x] Formulario, envío y verificación en dos pasos.
- [x] Suite PG ampliada y verificada: `VerificacionOtpPublicaTest` (8 tests), suite completa en **33 tests / 139 aserciones OK**.
