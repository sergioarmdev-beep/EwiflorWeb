# Documentación técnica de EwiflorWeb

## 1. Información general

**Proyecto:** EwiflorWeb  
**Tipo de sistema:** Aplicación web empresarial para la gestión de inventario y operaciones comerciales.  
**Arquitectura prevista:** Monolito modular.  
**Tecnologías principales:** Laravel 13, PHP, Blade y PostgreSQL.  
**Frontend:** Blade, JavaScript, CSS, Tailwind CSS y Vite.  
**Gestor de paquetes frontend:** pnpm.  
**Estado del proyecto:** Configuración inicial del entorno y documentación técnica.

## 2. Propósito de la documentación

Este directorio contiene la documentación técnica y funcional de EwiflorWeb. Su propósito es establecer una referencia común para el análisis, diseño, desarrollo, pruebas, mantenimiento y futura operación del sistema.

La documentación debe permitir que los integrantes del equipo comprendan las decisiones del proyecto sin depender exclusivamente de explicaciones verbales o del conocimiento de un desarrollador específico.

Los documentos deberán mantenerse alineados con los requisitos aprobados, las decisiones de arquitectura, las migraciones de base de datos y el comportamiento real de la aplicación.

## 3. Estructura documental

La documentación se organiza en las siguientes áreas:

| Directorio          | Contenido                                                                                                |
| ------------------- | -------------------------------------------------------------------------------------------------------- |
| `01-proyecto/`      | Visión, alcance, objetivos, actores, glosario y cuestiones pendientes.                                   |
| `02-requisitos/`    | Especificación de requisitos, requisitos funcionales y no funcionales, reglas de negocio y trazabilidad. |
| `03-arquitectura/`  | Arquitectura general, organización del código, dominios del sistema y decisiones arquitectónicas.        |
| `04-base-de-datos/` | Modelos de datos, diccionario, relaciones y políticas de integridad.                                     |
| `05-desarrollo/`    | Instalación local, convenciones de código, flujo de Git y estrategia de pruebas.                         |
| `06-seguridad/`     | Autenticación, autorización, auditoría y gestión de secretos.                                            |
| `07-operaciones/`   | Despliegue, copias de seguridad y mantenimiento.                                                         |
| `08-manuales/`      | Instrucciones de uso para usuarios y administradores.                                                    |

La estructura es una organización objetivo. Los archivos se incorporarán conforme exista información suficiente para documentar su contenido con precisión.

## 4. Índice de documentos

### 4.1. Proyecto

Ubicación: `01-proyecto/`

| Documento                         | Descripción                                                                                         | Estado    |
| --------------------------------- | --------------------------------------------------------------------------------------------------- | --------- |
| `vision-y-alcance.md`             | Define el problema, los objetivos generales, los límites y el alcance de las versiones del sistema. | Pendiente |
| `objetivos-y-actores.md`          | Identifica los objetivos operativos y los actores que interactuarán con el sistema.                 | Pendiente |
| `glosario.md`                     | Establece el significado de los términos empresariales y técnicos utilizados.                       | Pendiente |
| `pendientes-y-contradicciones.md` | Registra ambigüedades, contradicciones, decisiones pendientes y su resolución.                      | Pendiente |

### 4.2. Requisitos

Ubicación: `02-requisitos/`

| Documento                      | Descripción                                                                                                  | Estado    |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------ | --------- |
| `SRS.md`                       | Especificación general de requisitos de software.                                                            | Pendiente |
| `requisitos-funcionales.md`    | Describe las capacidades y operaciones que deberá proporcionar el sistema.                                   | Pendiente |
| `requisitos-no-funcionales.md` | Define requisitos de seguridad, rendimiento, disponibilidad, usabilidad y mantenibilidad, según corresponda. | Pendiente |
| `reglas-de-negocio.md`         | Documenta las reglas que condicionan las operaciones empresariales.                                          | Pendiente |
| `trazabilidad.md`              | Relaciona requisitos con diseño, implementación y pruebas.                                                   | Pendiente |

### 4.3. Arquitectura

Ubicación: `03-arquitectura/`

| Documento                                    | Descripción                                                                              | Estado    |
| -------------------------------------------- | ---------------------------------------------------------------------------------------- | --------- |
| `arquitectura-general.md`                    | Describe los componentes principales y sus responsabilidades.                            | Pendiente |
| `estructura-del-codigo.md`                   | Define la organización del código fuente y las convenciones de ubicación de componentes. | Pendiente |
| `dominios-del-sistema.md`                    | Identifica los dominios funcionales y sus límites.                                       | Pendiente |
| `decisiones/ADR-001-arquitectura-modular.md` | Registra la decisión de organizar la aplicación como un monolito modular.                | Pendiente |
| `decisiones/ADR-002-stack-tecnologico.md`    | Documenta las tecnologías elegidas y las razones de su selección.                        | Pendiente |

### 4.4. Base de datos

Ubicación: `04-base-de-datos/`

| Documento                    | Descripción                                                                             | Estado    |
| ---------------------------- | --------------------------------------------------------------------------------------- | --------- |
| `modelo-conceptual.md`       | Representa las entidades principales y sus relaciones desde la perspectiva del negocio. | Pendiente |
| `modelo-logico.md`           | Describe las entidades, atributos, claves y relaciones del modelo lógico.               | Pendiente |
| `diccionario-de-datos.md`    | Define las columnas, tipos, restricciones y significados de los datos.                  | Pendiente |
| `politicas-de-integridad.md` | Documenta las reglas de integridad y consistencia que debe garantizar la base de datos. | Pendiente |

### 4.5. Desarrollo

Ubicación: `05-desarrollo/`

| Documento                   | Descripción                                                                                                | Estado                                                                 |
| --------------------------- | ---------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------- |
| `instalacion-local.md`      | Explica cómo preparar el entorno, instalar dependencias, configurar la aplicación y ejecutarla localmente. | Redactado; pendiente de incorporar al repositorio y validar PostgreSQL |
| `convenciones-de-codigo.md` | Establece criterios de nomenclatura, organización y estilo de programación.                                | Pendiente                                                              |
| `git-y-flujo-de-trabajo.md` | Define las ramas, los commits, las revisiones y el procedimiento para integrar cambios.                    | Pendiente                                                              |
| `estrategia-de-pruebas.md`  | Establece los tipos de pruebas y los criterios mínimos de verificación.                                    | Pendiente                                                              |

### 4.6. Seguridad

Ubicación: `06-seguridad/`

| Documento                         | Descripción                                                                   | Estado    |
| --------------------------------- | ----------------------------------------------------------------------------- | --------- |
| `autenticacion-y-autorizacion.md` | Define los criterios para identificar usuarios y controlar sus permisos.      | Pendiente |
| `auditoria.md`                    | Establece qué operaciones deben registrarse y cómo preservar la trazabilidad. | Pendiente |
| `gestion-de-secretos.md`          | Describe el tratamiento de credenciales, claves y variables sensibles.        | Pendiente |

### 4.7. Operaciones

Ubicación: `07-operaciones/`

| Documento                | Descripción                                                         | Estado    |
| ------------------------ | ------------------------------------------------------------------- | --------- |
| `despliegue.md`          | Describe el procedimiento previsto para desplegar nuevas versiones. | Pendiente |
| `copias-de-seguridad.md` | Define la estrategia de respaldo y recuperación de datos.           | Pendiente |
| `mantenimiento.md`       | Establece procedimientos de mantenimiento y actualización.          | Pendiente |

### 4.8. Manuales

Ubicación: `08-manuales/`

| Documento          | Descripción                                                             | Estado                                |
| ------------------ | ----------------------------------------------------------------------- | ------------------------------------- |
| `usuario.md`       | Explica las operaciones disponibles para los usuarios de la aplicación. | Pendiente de implementación funcional |
| `administrador.md` | Explica las tareas administrativas y de configuración del sistema.      | Pendiente de implementación funcional |

## 5. Criterios de documentación

Todos los documentos deberán respetar los siguientes criterios:

1. **Precisión:** describir decisiones confirmadas y comportamientos verificables.
2. **Trazabilidad:** relacionar los requisitos con las decisiones, la implementación y las pruebas cuando corresponda.
3. **Consistencia:** utilizar los mismos términos para las mismas entidades y conceptos.
4. **Actualización:** revisar la documentación cuando un cambio afecte su contenido.
5. **Separación de responsabilidades:** distinguir requisitos, diseño técnico, procedimientos operativos y manuales de usuario.
6. **Seguridad:** no incluir contraseñas reales, claves privadas, tokens ni otros secretos.
7. **Transparencia:** registrar las dudas y contradicciones sin resolverlas mediante suposiciones.
8. **Control de versiones:** mantener los documentos técnicos junto con el código para revisar su evolución mediante Git.

## 6. Estados documentales

Para facilitar el seguimiento, se utilizarán los siguientes estados:

| Estado         | Significado                                                                     |
| -------------- | ------------------------------------------------------------------------------- |
| Pendiente      | El documento todavía no ha sido redactado.                                      |
| En elaboración | El documento está siendo preparado.                                             |
| En revisión    | El contenido está redactado y requiere validación.                              |
| Aprobado       | El contenido ha sido revisado y aceptado por los responsables correspondientes. |
| Desactualizado | El contenido ya no refleja completamente el estado actual del proyecto.         |

Un documento redactado no debe considerarse automáticamente aprobado. Los cambios que afecten requisitos, seguridad o arquitectura deben revisarse antes de considerarse definitivos.

## 7. Gestión de decisiones y contradicciones

Las decisiones técnicas importantes deberán registrarse en documentos ADR (_Architecture Decision Records_), incluyendo el contexto, las alternativas consideradas, la decisión adoptada y sus consecuencias.

Las dudas funcionales, contradicciones entre requisitos y decisiones empresariales pendientes deberán registrarse en `01-proyecto/pendientes-y-contradicciones.md`.

No se deberá interpretar la ausencia de información como autorización para elegir arbitrariamente una solución definitiva.

## 8. Mantenimiento de la documentación

La documentación forma parte del proyecto y debe revisarse junto con los cambios relevantes del código.

Antes de integrar una modificación, el responsable deberá comprobar si afecta a:

- Requisitos funcionales o no funcionales.
- Reglas de negocio.
- Arquitectura y dependencias entre dominios.
- Modelo de datos y restricciones.
- Seguridad y permisos.
- Procedimientos de instalación, pruebas o despliegue.
- Instrucciones de uso.

Cuando corresponda, los cambios de código y documentación deberán incorporarse en el mismo conjunto de trabajo para evitar que la referencia técnica quede desactualizada.

## 9. Orden recomendado de elaboración

La documentación se desarrollará progresivamente, siguiendo este orden general:

1. Visión, alcance, objetivos y actores.
2. Registro de pendientes y contradicciones.
3. Especificación de requisitos y reglas de negocio.
4. Arquitectura general y decisiones técnicas.
5. Modelo de datos y diccionario.
6. Convenciones de desarrollo y estrategia de pruebas.
7. Seguridad, operaciones y despliegue.
8. Manuales de usuario y administración.

Este orden podrá ajustarse cuando una dependencia técnica o una necesidad de validación lo justifique.

## 10. Principio rector

La documentación de EwiflorWeb debe representar fielmente el estado del proyecto. Su finalidad no es demostrar que se ha producido una gran cantidad de documentos, sino proporcionar información fiable que facilite las decisiones, la colaboración y el mantenimiento del sistema.
