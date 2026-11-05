ESPECIFICACIÓN DE REQUISITOS DE SOFTWARE

(SRS)

Ewiflor Importaciones

Contenido

## 1. Introducción

## 1.1 Propósito del documento

El presente documento tiene como propósito especificar los requisitos funcionales y no funcionales del sistema web de gestión de inventario, productos y maquinaria, cotizaciones, ventas y clientes de **Ewiflor Importaciones**. Su contenido proviene del levantamiento de requisitos realizado mediante entrevista a la gerencia de la empresa.

El documento sirve como base para el análisis y el diseño del sistema (casos de uso y modelo entidad-relación), su construcción y la elaboración de las pruebas de aceptación. Está dirigido al equipo de desarrollo, a la gerencia de Ewiflor Importaciones y al docente evaluador.

## 1.2 Alcance del sistema

**Qué problema resuelve.** Ewiflor Importaciones controla hoy su inventario, precios y ventas en planillas de Excel, lo que dificulta conocer con exactitud cuántas máquinas quedan, en qué almacén están y quién las vendió. El sistema centralizará esa información en una aplicación web con control de acceso por roles y registro de auditoría.

**Qué procesos cubrirá.** Gestión de productos y maquinaria, control de inventario por sucursal y almacén, precios y tipo de cambio, registro de clientes, cotizaciones, ventas, reservas y ventas bajo pedido, garantías y solicitudes de servicio, comisiones de vendedores, reportes y un catálogo web público.

**Qué usuarios utilizarán el sistema.** Gerente, Administrador, Vendedor, Servicio técnico y Usuario web (visitante del catálogo), además de un servicio externo que provee el tipo de cambio.

**Dentro y fuera del alcance.**

| **Dentro del alcance**                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        | **Fuera del alcance**                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| • Gestión de productos y maquinaria (variantes, repuestos, documentación y traducción) • Gestión de inventario por sucursal y almacén, con transferencias • Gestión de precios, descuentos, promociones y tipo de cambio • Registro de clientes • Cotizaciones • Ventas, reservas y ventas bajo pedido (con anticipo) • Garantías y solicitudes de servicio técnico • Comisiones de vendedores • Reportes y auditoría de operaciones • Gestión de usuarios, roles y permisos • Catálogo web público y contacto con vendedores | • Pedidos de importación: proveedores, órdenes de compra, pagos al exterior, embarques, aduana y costos de importación • Contabilidad completa y cuentas por cobrar o pagar • Ventas a crédito • Facturación electrónica • Integración con bancos, contabilidad o agentes de carga • Seguimiento de embarques y notificaciones automáticas de importación • Aplicación móvil • Licitaciones, devoluciones y mantenimiento preventivo • Tienda en línea con pagos |

Lo que queda fuera del alcance fue indicado expresamente por la gerencia durante la entrevista. Cualquier funcionalidad de esa lista requeriría un acuerdo de cambio de alcance.

## 2. Descripción general del sistema

## 2.1 Contexto de la empresa

| **Nombre**                | Ewiflor Importaciones                                                                                                                                                  |
| ------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Actividad**             | Importación y comercialización de maquinaria y repuestos de maquinaria                                                                                                 |
| **Tamaño aproximado**     | 8 empleados (gerencia, administración y ventas); 2 sucursales y 3 almacenes (2 grandes y 1 pequeño)                                                                    |
| **Ubicación**             |                                                                                                                                                                        |
| **Productos y servicios** | Maquinaria para panadería, repostería, alimentación y carnicería; repuestos y accesorios; instalación en equipos grandes o complejos; servicio técnico y garantía      |
| **Marca y origen**        | Marca Seven, proveedor de Taiwán; se prevé ampliar proveedores                                                                                                         |
| **Importaciones**         | Aproximadamente 2 al año, con plazos de 2 a 6 meses, marítimas y aéreas (el proceso de importación no forma parte del sistema)                                         |
| **Clientes**              | Público general, distribuidores (como Hanssen Importaciones), panaderías, pastelerías, negocios de comida rápida, restaurantes y personas que planean abrir un negocio |
| **Moneda**                | Precios internos en dólares (USD); precios a clientes en bolivianos (Bs)                                                                                               |
| **Herramienta actual**    | Planillas de Excel, correo electrónico y Drive                                                                                                                         |

**Cómo funciona actualmente.** El cliente contacta a la empresa por redes sociales, WhatsApp o visita. Se le da una cotización y se acuerda una cita para ver y probar la máquina. Con la venta se emite factura o recibo, se explica la garantía y se registra el nombre y celular del cliente. Si el equipo es grande o complejo, personal de la empresa lo instala sin costo extra. Si el equipo no está en existencia, se puede importar a pedido con un anticipo del 70%, o reservar con un anticipo del 10% por un máximo de un mes.

## 2.2 Problema identificado

Actualmente la información de productos, existencias, precios y ventas se administra mediante planillas de Excel que se actualizan manualmente. Según la gerencia, el problema que más tiempo hace perder es el registro y control del inventario y la auditoría de ventas. Esto genera problemas como:

- Dificultad para saber con exactitud cuántas máquinas quedan y en qué sucursal o almacén están.
- Duplicación de información de inventario, precios, cantidades y ubicación entre planillas.
- Actualización manual diaria de los precios, porque se manejan en USD y se venden en Bs según el tipo de cambio paralelo del día.
- Registro manual de ventas, sin auditoría confiable de quién vendió qué, cuándo y por cuánto.
- Cálculo de comisiones de los vendedores a partir de una tabla física.
- Seguimiento de garantías basado en recibos, nombre y celular del cliente, sin alertas de vencimiento.
- Dificultad para identificar los productos que menos se venden o que tardan más en venderse.
- Ausencia de un registro formal de reclamos y solicitudes de servicio técnico.
- Manuales y nombres de maquinaria en otro idioma, sin una traducción disponible en el inventario.

## 2.3 Árbol de problemas

El árbol de problemas organiza la información recogida en la entrevista y la observación: en el centro se ubica el problema principal, en la parte inferior las causas que lo originan y en la parte superior los efectos que produce en la operación de la empresa.

![Imagen del documento](assets/image_001.png)

_Figura 1. Árbol de problemas de Ewiflor Importaciones_

## 2.4 Análisis FODA

El análisis se elaboró con la información de la entrevista a la gerencia y a la administración, y sirve para identificar los factores internos y externos que influyen en el sistema.

| **Fortalezas (internas)**                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    | **Debilidades (internas)**                                                                                                                                                                                                                                                                                                                                                                                                                   |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Marca propia (Seven) con proveedor establecido en Taiwán. Oferta amplia: maquinaria para panadería, repostería, alimentación y carnicería, además de repuestos y accesorios. Servicio posventa: garantía, servicio técnico e instalación sin costo en equipos grandes o complejos. Cartera de clientes diversa: público general, distribuidores, panaderías, pastelerías, restaurantes y negocios de comida rápida. Presencia en 2 sucursales y 3 almacenes, con un equipo pequeño que conoce el proceso completo. Gerencia y administración involucradas en el levantamiento de requisitos. | Inventario, precios y ventas administrados en planillas de Excel actualizadas manualmente. Información duplicada y sin vista unificada de existencias por sucursal y almacén. Sin auditoría confiable de quién vendió qué, cuándo y por cuánto. Comisiones calculadas con una tabla física. Garantías sin alertas de vencimiento y reclamos sin registro formal. Manuales y nombres de maquinaria en otro idioma, sin traducción disponible. |
| **Oportunidades (externas)**                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 | **Amenazas (externas)**                                                                                                                                                                                                                                                                                                                                                                                                                      |
| Ampliar la cartera de proveedores, como la gerencia prevé. Abrir nuevas sucursales o almacenes con un sistema que escale. Publicar un catálogo en línea con contacto y cotización por correo o WhatsApp. Automatizar el tipo de cambio mediante un servicio externo (API REST). Usar reportes de rotación y ventas para decidir qué productos mantener o importar. Mejorar la atención al cliente con historial de compras y control de garantías.                                                                                                                                           | Variación diaria del tipo de cambio paralelo (USD/Bs), que afecta los precios de venta. Plazos de importación de 2 a 6 meses con solo 2 importaciones al año, con riesgo de quedarse sin stock. Dependencia de un proveedor principal. Faltantes o daños de la mercadería al recibirla. Dependencia de un servicio externo para el tipo de cambio y de la conexión a Internet.                                                               |

## 2.5 Procesos actuales de la empresa

Además del flujo de venta descrito en la sección 2.1, la entrevista y la observación permitieron identificar cómo se realiza hoy cada proceso que el sistema deberá apoyar.

| **Proceso**                     | **Cómo se realiza hoy**                                                                                                                                                                                                               | **Observación**                                                                                 |
| ------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| **Consulta de inventario**      | Se revisan las planillas de Excel. El control es por cantidad y código genérico, en 3 almacenes y 2 sucursales, con transferencias frecuentes entre ellos.                                                                            | No se sabe con exactitud cuántas máquinas quedan ni dónde están.                                |
| **Precios y tipo de cambio**    | Los precios se manejan en USD y se venden en Bs con el dólar paralelo del día. El Gerente o los administradores los modifican a diario.                                                                                               | Actualización manual diaria y riesgo de precios desactualizados.                                |
| **Cotización**                  | Se solicita por redes sociales o WhatsApp. Su vigencia por defecto es de 1 semana y puede variar.                                                                                                                                     | Sin registro ni seguimiento formal de las cotizaciones abiertas.                                |
| **Venta**                       | Cotización, cita para ver y probar la máquina, pago en efectivo, QR o transferencia, recibo o factura y nota de garantía. Los descuentos los autoriza el Administrador o el Gerente. Se registran el nombre y el celular del cliente. | Registro manual, sin auditoría confiable del vendedor, la fecha y el monto.                     |
| **Reserva y venta bajo pedido** | La reserva exige un anticipo del 10% por un máximo de 1 mes. La importación a pedido exige un anticipo del 70% y se avisa al cliente cuando el producto está disponible.                                                              | Control manual de anticipos, plazos y avisos.                                                   |
| **Entrega e instalación**       | El equipo se entrega al cliente; si es grande o complejo, el personal de la empresa lo instala sin costo extra.                                                                                                                       | Sin registro de la entrega ni de la instalación.                                                |
| **Garantía y servicio técnico** | La garantía cubre solo defectos de fábrica y se resuelve con un equipo nuevo. Se identifica por recibo, nombre y celular. Se asigna un técnico especialista.                                                                          | No hay alertas de vencimiento ni órdenes de servicio; los reclamos no se registran formalmente. |
| **Comisiones de vendedores**    | Se calculan con una tabla física, según el vendedor sea permanente o temporal.                                                                                                                                                        | Cálculo manual y propenso a errores.                                                            |
| **Reportes**                    | Se envían en PDF por correo electrónico: ventas, más y menos vendidos, stock, comisiones, garantías, márgenes y cotizaciones.                                                                                                         | Se arman a partir de las planillas, sin datos consolidados.                                     |
| **Accesos y seguridad**         | Existen cinco perfiles con permisos diferenciados. Cuando un empleado sale, se da de baja su cuenta.                                                                                                                                  | Sin registro automático de quién modifica precios, costos o ventas.                             |

## 2.6 Justificación del sistema

La gestión actual en planillas de Excel genera duplicación, errores y pérdida de tiempo, y no permite auditar las ventas ni conocer el stock real. El sistema se justifica por las razones siguientes.

**Justificación operativa.** Centraliza el inventario por sucursal, almacén y ubicación, y elimina la duplicación de datos entre planillas. Convierte los precios de USD a Bs con el tipo de cambio del día, lo que evita la actualización manual diaria.

**Justificación de control y seguridad.** Registra de forma automática quién vende, quién modifica precios o existencias y cuándo lo hace, con permisos por rol. Los costos, los precios del proveedor y los márgenes quedan restringidos al Gerente.

**Justificación de gestión.** Entrega reportes de ventas, rotación de productos, stock, comisiones, garantías y cotizaciones, que hoy no se obtienen con facilidad y que la gerencia necesita para decidir.

**Justificación de servicio al cliente.** Conserva el historial de compras, genera la garantía con su fecha de vencimiento y registra las solicitudes de servicio técnico, con lo que mejora la atención posventa.

**Justificación de crecimiento.** Permite agregar sucursales, almacenes, usuarios y productos, y deja preparado un futuro catálogo en línea, sin rediseñar la aplicación.

La siguiente tabla relaciona cada problema de la sección 2.2 con la solución que aporta el sistema.

| **Problema**                                             | **Aporte del sistema**                                                | **Requisito**  |
| -------------------------------------------------------- | --------------------------------------------------------------------- | -------------- |
| **Desconocimiento del stock por sucursal y almacén**     | Existencias en tiempo real con cantidad total, reservada y disponible | RF-005, RF-006 |
| **Duplicación de inventario, precios y ubicación**       | Base de datos única y movimientos de inventario registrados           | RF-005, RF-006 |
| **Actualización manual diaria de precios en USD y Bs**   | Tipo de cambio automático, modificable y con historial                | RF-003, RF-004 |
| **Registro manual de ventas sin auditoría**              | Venta con vendedor, fecha y monto, y auditoría no editable            | RF-009, RF-014 |
| **Comisiones calculadas con una tabla física**           | Cálculo automático con tabla configurable                             | RF-012         |
| **Garantías sin alertas y reclamos sin registro**        | Garantía generada en la venta, alertas y solicitudes de servicio      | RF-011         |
| **Productos de menor rotación difíciles de identificar** | Reportes de equipos más y menos vendidos                              | RF-006, RF-013 |
| **Manuales y nombres sin traducción**                    | Documentación técnica y traducción en el inventario                   | RF-002         |

## 2.7 Objetivos del sistema

**Objetivo general**

Desarrollar un sistema web que permita a Ewiflor Importaciones gestionar su inventario, productos y maquinaria, cotizaciones, ventas y clientes de forma centralizada, con control de acceso por roles y registro de auditoría.

**Objetivos específicos**

- Centralizar el inventario por sucursal, almacén y ubicación, con existencias consultables en tiempo real.
- Administrar el catálogo de productos con variantes, repuestos compatibles, documentación técnica y traducción de manuales y nombres.
- Automatizar la conversión de precios de USD a Bs con el tipo de cambio del día, permitiendo su modificación manual.
- Registrar cotizaciones, reservas, ventas bajo pedido y ventas con trazabilidad del vendedor, y facilitar el cálculo de comisiones.
- Dar seguimiento a clientes, garantías y solicitudes de servicio técnico.
- Controlar el acceso por roles y entregar reportes y estadísticas para la toma de decisiones.

## 3. Actores y usuarios del sistema

Este capítulo identifica a las personas y sistemas que interactúan con el sistema web de Ewiflor Importaciones. La matriz de actores relaciona a cada actor con las funcionalidades, y la sección de roles describe sus responsabilidades y restricciones de acceso.

## 3.1 Matriz de actores

● el actor gestiona (crea, modifica o ejecuta) la funcionalidad; ○ el actor solo consulta o participa.

| **ID** | **Funcionalidad**                       | **Gerente** | **Admin.** | **Vendedor** | **Serv. técnico** | **Usuario web** | **API** |
| ------ | --------------------------------------- | ----------- | ---------- | ------------ | ----------------- | --------------- | ------- |
| RF-001 | Autenticar y gestionar usuarios         | ●           | ○          | ○            | ○                 |                 |         |
| RF-002 | Gestionar productos y documentación     | ●           | ●          | ○            | ○                 | ○               |         |
| RF-003 | Gestionar precios y promociones         | ●           | ●          | ○            |                   |                 |         |
| RF-004 | Gestionar tipo de cambio                | ●           | ●          | ○            |                   |                 | ●       |
| RF-005 | Registrar movimientos de inventario     | ●           | ●          |              |                   |                 |         |
| RF-006 | Consultar existencias y ubicación       | ○           | ○          | ○            | ○                 |                 |         |
| RF-007 | Gestionar clientes                      | ○           | ○          | ●            |                   |                 |         |
| RF-008 | Gestionar cotizaciones                  | ○           | ○          | ●            |                   |                 |         |
| RF-009 | Registrar ventas                        | ●           | ●          | ●            |                   |                 |         |
| RF-010 | Gestionar reservas y ventas bajo pedido | ○           | ○          | ●            |                   |                 |         |
| RF-011 | Gestionar garantías y servicio técnico  | ○           | ●          | ○            | ●                 | ●               |         |
| RF-012 | Gestionar comisiones                    | ●           | ●          |              |                   |                 |         |
| RF-013 | Generar reportes                        | ●           | ●          |              |                   |                 |         |
| RF-014 | Registrar auditoría                     | ○           | ○          |              |                   |                 |         |
| RF-015 | Publicar catálogo web y contactos       |             |            | ○            |                   | ●               |         |

## 3.2 Roles y responsabilidades de los usuarios

La siguiente tabla describe a cada actor y las responsabilidades que tiene en el sistema.

| **Actor**                              | **Descripción**                                                              | **Responsabilidades**                                                                                                                                             |
| -------------------------------------- | ---------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Gerente**                            | Dirección de la empresa; accede a toda la información                        | Usuarios y permisos, precios, costos y márgenes, tipo de cambio, autorización de descuentos, importaciones y reclamos al proveedor, reportes y auditoría completa |
| **Administrador**                      | Responsable de la administración; no ve proveedores ni precios del proveedor | Productos, precios, inventario y movimientos, tipo de cambio, descuentos, anulación de ventas, comisiones, reportes y auditoría de ventas e inventario            |
| **Vendedor**                           | Personal de ventas, permanente o temporal                                    | Consultar inventario y precios, registrar clientes, cotizaciones, reservas y ventas, y atender los contactos del catálogo                                         |
| **Servicio técnico**                   | Técnico especialista en el área del problema                                 | Consultar inventario y catálogo, recibir solicitudes de servicio en su panel y registrar la atención                                                              |
| **Usuario web**                        | Visitante o cliente del catálogo en línea                                    | Consultar el catálogo, contactar a un vendedor y solicitar un servicio                                                                                            |
| **Servicio externo de tipo de cambio** | Sistema externo (API REST)                                                   | Entregar diariamente el tipo de cambio paralelo y oficial                                                                                                         |

Las restricciones de acceso por rol se derivan de las reglas de negocio RN-004, RN-005, RN-007 y RN-009 y se aplican en el servidor (RNF-002).

| **Actor**                              | **Puede**                                                                                                                                               | **No puede**                                                                                    |
| -------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| **Gerente**                            | Acceso a toda la información, incluidos costos, precios del proveedor y márgenes. Gestiona usuarios y permisos.                                         | Ninguna.                                                                                        |
| **Administrador**                      | Gestiona productos, precios, inventario, tipo de cambio, descuentos, comisiones y reportes; anula ventas; consulta la auditoría de ventas e inventario. | No ve proveedores, precios del proveedor ni márgenes.                                           |
| **Vendedor**                           | Consulta inventario y precios; registra clientes, cotizaciones, reservas y ventas; aplica descuentos con autorización.                                  | No modifica precios de lista ni datos de productos. Solo cancela la venta que está registrando. |
| **Servicio técnico**                   | Consulta inventario y catálogo, recibe solicitudes en su panel y registra la atención.                                                                  | No modifica inventario ni precios.                                                              |
| **Usuario web**                        | Consulta el catálogo público, contacta a un vendedor y solicita un servicio.                                                                            | Sin acceso a costos, proveedores ni existencias internas.                                       |
| **Servicio externo de tipo de cambio** | Entrega diariamente el tipo de cambio paralelo y oficial.                                                                                               | Sin acceso a otros datos del sistema.                                                           |

## 4. Levantamiento de información

## 4.1 Técnicas utilizadas

| **Técnica**             | **Aplicación en el proyecto**                             | **Estado** |
| ----------------------- | --------------------------------------------------------- | ---------- |
| **Entrevista**          | Entrevista estructurada a la gerente                      | Aplicada   |
| **Cuestionario**        | Preguntas del formato por módulo                          | Aplicada   |
| **Observación**         | Observación del trabajo diario en sucursales y almacenes  | Aplicada   |
| **Revisión documental** | Planillas de Excel, recibos, facturas y notas de garantía | Aplicada   |

## 4.2 Entrevista

| **Fecha**           | 29-09-2026                                                                                                                                      |
| ------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| **Entrevistado(a)** | Ana Mayta                                                                                                                                       |
| **Cargo**           | Gerente                                                                                                                                         |
| **Entrevistador**   | Sergio A. Ramirez M.                                                                                                                            |
| **Objetivo**        | Conocer los procesos de la empresa, los problemas actuales y las necesidades para el sistema web de inventario, cotizaciones, ventas y clientes |

| **Fecha**           | 29-09-2026                                                                                                                                                             |
| ------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Entrevistado(a)** | Evelin Chávez                                                                                                                                                          |
| **Cargo**           | Administrador                                                                                                                                                          |
| **Entrevistador**   | Sergio A. Ramirez M.                                                                                                                                                   |
| **Objetivo**        | Conocer los procesos de la empresa, los problemas actuales y las necesidades para el sistema web de inventario, cotizaciones, ventas y clientes desde otra perspectiva |

**Módulos cubiertos**

| **Módulo** | **Tema**                           | **Aspectos consultados**                                                                             |
| ---------- | ---------------------------------- | ---------------------------------------------------------------------------------------------------- |
| **A**      | Información general y organización | Actividad, líneas de producto, ingresos, personal, sucursales, clientes y día normal de trabajo      |
| **B**      | Productos y catálogo               | Clasificación, identificación, series, especificaciones, variantes, repuestos, kits y precios        |
| **C**      | Proveedores del exterior           | Fabricantes, datos del proveedor, cotizaciones y medios de comunicación                              |
| **D**      | Proceso de importación y costos    | Flujo de importación, anticipos, pagos, tipo de cambio y plazos                                      |
| **E**      | Recepción, almacén e inventario    | Recepción, daños, almacenes, series, reservas, inventario físico y transferencias                    |
| **F**      | Ventas                             | Proceso de venta, cotizaciones, comprobantes, pagos, descuentos, entrega, cancelaciones y comisiones |
| **G**      | Clientes y crédito                 | Datos de clientes, tipos, historial y seguimiento                                                    |
| **H**      | Servicio técnico y garantías       | Instalación, garantías, reclamos, técnicos y repuestos                                               |
| **I**      | Pagos, cobros y contabilidad       | Registro de ingresos, moneda y sistema contable                                                      |
| **J**      | Usuarios, permisos y seguridad     | Roles, permisos, eliminación, anulación y registro de operaciones                                    |
| **K**      | Reportes                           | Reportes requeridos y su frecuencia                                                                  |
| **L**      | Reglas de negocio                  | Reglas con excepciones                                                                               |
| **M**      | Problemas actuales                 | Problemas que más tiempo restan y errores frecuentes                                                 |
| **O**      | Necesidades futuras y validación   | Nuevas sucursales, catálogo en línea, acceso móvil e integraciones                                   |

**Preguntas clave realizadas**

| **Pregunta**                                                                                | **Respuesta resumida**                                                                                                                                                                                                                           |
| ------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Descríbame un día normal de trabajo de principio a fin                                      | Revisión de inventarios y movimientos, contacto con posibles clientes, venta y promoción con facturación y entrega, revisión periódica de disponibilidad y planificación de publicaciones en redes sociales                                      |
| Muéstreme cómo realizan una venta desde que llega el cliente hasta que termina la operación | Contacto por redes sociales o WhatsApp, cotización, cita para ver y probar la máquina, recibo o factura, explicación de garantía, registro de nombre y celular, entrega e instalación si es grande                                               |
| ¿Cómo obtiene hoy el stock de un producto?                                                  | Mediante revisión de planillas en Excel                                                                                                                                                                                                          |
| ¿Quiénes utilizan la información y qué puede hacer cada persona?                            | Gerente: toda la información; Administrador: gastos, costos, ventas e inventario, sin proveedores; Vendedor: inventario, precios y registro de ventas; Servicio técnico: inventario, catálogo y notificaciones; Usuario web: catálogo y contacto |
| ¿Cómo manejan el tipo de cambio?                                                            | Precios internos en USD y venta en Bs al tipo de cambio paralelo del día; desean automatización modificable manualmente                                                                                                                          |
| ¿Cuál es el problema que más tiempo les hace perder?                                        | El registro y control de inventario y la auditoría de ventas                                                                                                                                                                                     |

## 4.3 Resultados del levantamiento

Las respuestas de la entrevista se transformaron en hallazgos y requisitos:

| **Tema**                | **Hallazgo**                                                                                                                                                                                 | **Se traduce en**                      |
| ----------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------- |
| **Inventario**          | El stock se consulta revisando planillas de Excel. Se controla por cantidad y Código genérico, en 3 almacenes y 2 sucursales, con transferencias frecuentes entre ellos                      | RF-005, RF-006                         |
| **Productos**           | Categorías alimentaria, panadería, repostería y cárnica; variantes por color, modelo mecánico y características; repuestos asociados a modelos compatibles; kits como promociones            | RF-002, RF-003                         |
| **Precios y moneda**    | Precios internos en USD y venta en Bs con el dólar paralelo del día; los precios cambian a diario y los modifican el Gerente o los administradores                                           | RF-003, RF-004, RN-006                 |
| **Cotizaciones**        | Se solicitan por redes sociales o WhatsApp, con vigencia por defecto de 1 semana que debe poder variar                                                                                       | RF-008, RN-003                         |
| **Ventas**              | Pago en efectivo, QR o transferencia; recibo, factura y nota de garantía; descuentos autorizados por Administrador y Gerente; sin devoluciones y con cancelaciones que devuelven el anticipo | RF-009, RN-008, RN-009                 |
| **Reservas y pedidos**  | Reserva con anticipo del 10% por un máximo de 1 mes; importación a pedido con el 70%; se avisa al cliente cuando el producto esté disponible                                                 | RF-010, RN-001, RN-002                 |
| **Clientes**            | Se guardan nombre y teléfono; se conserva el historial para la garantía; hay descuentos para mayoristas; no se otorga crédito                                                                | RF-007, RN-008                         |
| **Garantía y servicio** | La garantía cubre solo defectos de fábrica y se reemplaza por un equipo nuevo; no se emiten órdenes de servicio; se asigna un técnico especialista                                           | RF-011, RN-010                         |
| **Comisiones**          | Se calculan con una tabla física, según el vendedor sea permanente o temporal                                                                                                                | RF-012                                 |
| **Seguridad**           | Cinco perfiles con permisos diferenciados, registro de quién vende y modifica precios y costos, y baja de cuentas de empleados que salen                                                     | RF-001, RF-014, RN-004, RN-005, RN-011 |
| **Reportes**            | Se envían en PDF por correo electrónico: ventas, más y menos vendidos, stock, comisiones, garantías, márgenes y cotizaciones                                                                 | RF-013                                 |
| **Documentación**       | Se requiere traducción de manuales y nombres de maquinaria, con un apartado de lectura en el inventario                                                                                      | RF-002                                 |
| **Problemas**           | No saber en qué almacén ni cuántas máquinas quedan; registro manual de ventas; inventario, precios y ubicación duplicados                                                                    | RF-005, RF-006, RF-009, RF-014         |
| **Futuro**              | Nuevas sucursales o almacenes; catálogo en línea con cotizaciones por correo o WhatsApp; solo acceso web, sin aplicación móvil ni integraciones a mediano plazo                              | RNF-012, RNF-013, RF-015               |

## 4.4 Evidencias

- Acta o constancia de la entrevista: Documento Aparte

## 4.5 Necesidades identificadas

Las necesidades resumen lo que la empresa espera del sistema según los hallazgos de la sección 4.3. Cada una se enlaza con el módulo de la entrevista del que proviene y con los requisitos que la atienden.

| **ID**     | **Necesidad**                                                                                           | **Módulo** | **Requisito**   |
| ---------- | ------------------------------------------------------------------------------------------------------- | ---------- | --------------- |
| **NEC-01** | Saber en tiempo real cuántas máquinas hay y en qué sucursal o almacén están.                            | E, M       | RF-005, RF-006  |
| **NEC-02** | Eliminar la duplicación de inventario, precios, cantidades y ubicación.                                 | M          | RF-005, RF-006  |
| **NEC-03** | Convertir los precios de USD a Bs con el dólar del día, con posibilidad de modificarlo a mano.          | D, F       | RF-003, RF-004  |
| **NEC-04** | Administrar productos con variantes, repuestos compatibles, kits y promociones.                         | B          | RF-002, RF-003  |
| **NEC-05** | Consultar manuales y nombres de maquinaria traducidos desde el inventario.                              | B          | RF-002          |
| **NEC-06** | Emitir cotizaciones con vigencia variable y enviarlas por correo o WhatsApp.                            | F          | RF-008          |
| **NEC-07** | Registrar ventas con trazabilidad del vendedor y una auditoría confiable.                               | F, J, M    | RF-009, RF-014  |
| **NEC-08** | Controlar reservas y ventas bajo pedido con sus anticipos y el aviso de disponibilidad.                 | E, F       | RF-010          |
| **NEC-09** | Registrar clientes y su historial de compras, con descuentos para mayoristas.                           | G          | RF-007          |
| **NEC-10** | Controlar garantías con alertas de vencimiento y registrar los reclamos para el técnico especialista.   | H          | RF-011          |
| **NEC-11** | Calcular las comisiones de los vendedores sin la tabla física.                                          | F          | RF-012          |
| **NEC-12** | Controlar el acceso con cinco perfiles y dar de baja las cuentas de quienes salen.                      | J          | RF-001, RNF-002 |
| **NEC-13** | Obtener reportes de ventas, stock, comisiones, garantías, márgenes y cotizaciones, en PDF y por correo. | K          | RF-013          |
| **NEC-14** | Identificar los productos que menos se venden o que más tardan en venderse.                             | M          | RF-006, RF-013  |
| **NEC-15** | Poder crecer con nuevas sucursales o almacenes y un catálogo en línea.                                  | O          | RF-015, RNF-012 |
| **NEC-16** | Usar el sistema solo desde el navegador, sin aplicación móvil.                                          | O          | RNF-013         |

## 5. Requisitos del sistema

## 5.1 Requisitos funcionales

RF-001 — Autenticar y gestionar usuarios, roles y permisos

El sistema deberá permitir a los usuarios iniciar sesión con sus credenciales y restringir el acceso a funciones e información según su rol (Gerente, Administrador, Vendedor y Servicio técnico). El Gerente podrá crear usuarios, asignarles un rol y permisos, y deshabilitar de inmediato la cuenta de un empleado que deja la empresa.

**Prioridad:** Alta | **Actor:** Gerente (gestiona); todos los usuarios (inician sesión)

RF-002 — Gestionar productos, variantes, repuestos y documentación técnica

El sistema deberá permitir registrar, modificar, consultar y dar de baja productos (maquinaria, repuestos y accesorios) indicando código, nombre, categoría (alimentaria, panadería, repostería o cárnica), marca, modelo, tipo (inventariable o bajo pedido), variantes (color, modelo mecánico, características), especificaciones técnicas (voltaje, potencia, dimensiones, peso, material), stock mínimo, duración de garantía y fotografías. Deberá asociar repuestos y accesorios a los modelos compatibles, permitir adjuntar manuales y fichas técnicas, y registrar el nombre y los manuales de cada equipo con su traducción al español, consultables desde el apartado de inventario. Cada especificación podrá marcarse como pública o interna.

**Prioridad:** Alta | **Actor:** Gerente, Administrador

RF-003 — Gestionar precios, descuentos y promociones

El sistema deberá permitir definir y modificar el precio de lista de cada producto en dólares (USD), precios por volumen o mayorista, descuentos y promociones o kits (conjuntos de productos con precio promocional), conservando el historial de cambios. Los vendedores podrán consultar los precios, pero no modificarlos.

**Prioridad:** Alta | **Actor:** Gerente, Administrador

RF-004 — Gestionar el tipo de cambio

El sistema deberá obtener cada día el tipo de cambio USD/Bs mediante un servicio externo (API REST), con el dólar paralelo como valor por defecto y el oficial disponible como referencia. Deberá permitir al Gerente y al Administrador modificarlo manualmente, aplicar el valor vigente para convertir los precios de USD a Bs en cotizaciones y ventas, y conservar el historial de valores.

**Prioridad:** Alta | **Actor:** Gerente, Administrador; servicio externo de tipo de cambio

RF-005 — Registrar movimientos de inventario

El sistema deberá permitir registrar entradas (recepción de mercadería ya nacionalizada), salidas (por venta, daño o pérdida), ajustes por inventario físico y transferencias entre almacenes y sucursales. Cada movimiento indicará producto, cantidad, almacén de origen y destino, número de serie(generado) o código cuando corresponda, motivo, fecha y usuario, y actualizará las existencias automáticamente. En la recepción deberá poder registrarse la diferencia de cantidad o los daños para su reclamo al proveedor.

**Prioridad:** Alta | **Actor:** Administrador, Gerente

RF-006 — Consultar existencias y ubicación

El sistema deberá permitir consultar en tiempo real la disponibilidad de cada producto por sucursal y almacén, mostrando cantidad total, reservada y disponible. Deberá permitir filtrar por categoría, marca o modelo, alertar los productos por debajo del stock mínimo e identificar los productos de menor rotación o que tardan más en venderse.

**Prioridad:** Alta | **Actor:** Vendedor, Administrador, Gerente, Servicio técnico

RF-007 — Gestionar clientes

El sistema deberá permitir registrar, modificar y consultar clientes con nombre y teléfono como datos obligatorios, y correo electrónico, tipo de cliente (distribuidor, panadería, pastelería, comida rápida, restaurante o persona natural) y condición de mayorista como datos opcionales. Deberá mostrar el historial de compras y los equipos adquiridos por cada cliente.

**Prioridad:** Alta | **Actor:** Vendedor

RF-008 — Gestionar cotizaciones

El sistema deberá permitir crear cotizaciones con productos, cantidades, precios en bolivianos (convertidos con el tipo de cambio del día) y descuentos autorizados. Cada cotización tendrá una vigencia por defecto de 1 semana, modificable. Deberá generar un documento PDF para enviarlo por correo electrónico o WhatsApp, manejar los estados (vigente, vencida, convertida en venta, anulada) y permitir su seguimiento y conversión en venta o reserva.

**Prioridad:** Alta | **Actor:** Vendedor

RF-009 — Registrar ventas

El sistema deberá permitir registrar una venta, directa o a partir de una cotización, con cliente (nombre y celular), productos con su código, precio en bolivianos y su equivalente en USD, tipo de cambio aplicado, descuentos autorizados, forma de pago (efectivo, QR o transferencia bancaria), vendedor, sucursal y fecha. Al confirmarla, deberá descontar el inventario y emitir el recibo o nota de venta y la nota de garantía, registrando el número de factura. Deberá permitir cancelar ventas según las reglas de negocio, devolviendo el anticipo cuando corresponda.

**Prioridad:** Alta | **Actor:** Vendedor; Administrador y Gerente (anulación)

RF-010 — Gestionar reservas y ventas bajo pedido

El sistema deberá permitir reservar equipos en existencia con un anticipo mínimo del 10% y una vigencia máxima de 1 mes (modificable), liberando el stock cuando la reserva vence. También deberá permitir registrar ventas bajo pedido de equipos no disponibles con un anticipo del 70%, guardando nombre, celular, cantidad, monto y porcentaje del anticipo, y avisar al vendedor cuando el producto esté disponible para notificar al cliente. Las reservas y los pedidos podrán cancelarse con devolución del anticipo.

**Prioridad:** Alta | **Actor:** Vendedor

RF-011 — Gestionar garantías y solicitudes de servicio técnico

El sistema deberá generar la garantía al registrar la venta, vinculando cliente, producto, número de serie o código y comprobante, con vigencia según el modelo desde la fecha de entrega. Deberá permitir consultar la vigencia, alertar las garantías por vencer, registrar solicitudes de servicio con la descripción de la falla, notificar al técnico especialista en su panel, programar la visita y registrar el resultado: reparación, servicio con costo cuando la garantía no está vigente, o reemplazo por un equipo nuevo cuando se trata de un defecto de fábrica.

**Prioridad:** Alta | **Actor:** Servicio técnico, Administrador; Usuario web y Vendedor (registran la solicitud)

RF-012 — Gestionar comisiones de vendedores

El sistema deberá calcular la comisión de cada venta según una tabla de comisiones configurable que considere la condición del vendedor (permanente o temporal), registrar las comisiones obtenidas y por pagar por vendedor y período, y revertir la comisión cuando se cancele la venta.

**Prioridad:** Media | **Actor:** Gerente, Administrador

RF-013 — Generar reportes

El sistema deberá generar reportes de ventas por período, vendedor y sucursal; equipos más y menos vendidos; stock por producto y almacén; productos por agotarse; comisiones obtenidas y por pagar; garantías vigentes y por vencer; margen de ganancia por producto y venta (solo Gerente); cotizaciones abiertas y tasa de conversión; solicitudes de servicio pendientes; y repuestos más usados o equipos sin movimiento. Los reportes podrán visualizarse en pantalla, descargarse en PDF y enviarse por correo electrónico.

**Prioridad:** Alta | **Actor:** Gerente, Administrador

RF-014 — Registrar auditoría de operaciones

El sistema deberá registrar de forma automática, sin posibilidad de edición, las operaciones críticas (ventas y cancelaciones, cambios de precios y costos, movimientos de inventario, cambios del tipo de cambio, garantías y gestión de usuarios) con usuario, fecha, hora y valores anterior y nuevo. El Gerente podrá consultar todos los registros y el Administrador solo los de ventas e inventario.

**Prioridad:** Alta | **Actor:** Gerente, Administrador

RF-015 — Publicar catálogo web y recibir contactos

El sistema deberá exponer un catálogo web público con los productos, fotografías, descripciones y especificaciones marcadas como públicas. El usuario web podrá contactar a un vendedor y solicitar un servicio, sin acceso a costos, proveedores ni existencias internas.

**Prioridad:** Media | **Actor:** Usuario web; Vendedor

## 5.2 Matriz de requisitos funcionales

| **ID** | **Requisito**                                                     | **Prioridad** | **Actor**                                                                        |
| ------ | ----------------------------------------------------------------- | ------------- | -------------------------------------------------------------------------------- |
| RF-001 | Autenticar y gestionar usuarios, roles y permisos                 | Alta          | Gerente (gestiona); todos los usuarios (inician sesión)                          |
| RF-002 | Gestionar productos, variantes, repuestos y documentación técnica | Alta          | Gerente, Administrador                                                           |
| RF-003 | Gestionar precios, descuentos y promociones                       | Alta          | Gerente, Administrador                                                           |
| RF-004 | Gestionar el tipo de cambio                                       | Alta          | Gerente, Administrador; servicio externo de tipo de cambio                       |
| RF-005 | Registrar movimientos de inventario                               | Alta          | Administrador, Gerente                                                           |
| RF-006 | Consultar existencias y ubicación                                 | Alta          | Vendedor, Administrador, Gerente, Servicio técnico                               |
| RF-007 | Gestionar clientes                                                | Alta          | Vendedor                                                                         |
| RF-008 | Gestionar cotizaciones                                            | Alta          | Vendedor                                                                         |
| RF-009 | Registrar ventas                                                  | Alta          | Vendedor; Administrador y Gerente (anulación)                                    |
| RF-010 | Gestionar reservas y ventas bajo pedido                           | Alta          | Vendedor                                                                         |
| RF-011 | Gestionar garantías y solicitudes de servicio técnico             | Alta          | Servicio técnico, Administrador; Usuario web y Vendedor (registran la solicitud) |
| RF-012 | Gestionar comisiones de vendedores                                | Media         | Gerente, Administrador                                                           |
| RF-013 | Generar reportes                                                  | Alta          | Gerente, Administrador                                                           |
| RF-014 | Registrar auditoría de operaciones                                | Alta          | Gerente, Administrador                                                           |
| RF-015 | Publicar catálogo web y recibir contactos                         | Media         | Usuario web; Vendedor (recibe el contacto)                                       |

## 6. Requisitos no funcionales

## 6.1 Seguridad

| **ID**  | **Requisito**                                                                                                                                                             |
| ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| RNF-001 | El sistema deberá autenticar a los usuarios con credenciales y almacenar las contraseñas cifradas, nunca en texto plano.                                                  |
| RNF-002 | El sistema deberá aplicar la autorización por rol en el servidor para cada función y dato; los costos, precios del proveedor y márgenes solo serán accesibles al Gerente. |
| RNF-003 | El sistema deberá cerrar la sesión tras 30 minutos de inactividad.                                                                                                        |
| RNF-004 | La comunicación entre el navegador y el servidor deberá estar cifrada mediante HTTPS.                                                                                     |
| RNF-005 | Los registros de auditoría no podrán ser modificados ni eliminados desde la aplicación.                                                                                   |

## 6.2 Rendimiento

| **ID**  | **Requisito**                                                                                                                                                                 |
| ------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| RNF-006 | El sistema deberá responder a las consultas comunes (existencias, catálogo y cotizaciones) en menos de 3 segundos con hasta 10 usuarios concurrentes en condiciones normales. |
| RNF-007 | El sistema deberá generar los reportes en menos de 10 segundos para un período de hasta un año.                                                                               |

## 6.3 Disponibilidad

| **ID**  | **Requisito**                                                                                                                                                                                                                                     |
| ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| RNF-008 | El sistema deberá estar disponible al menos el 95% del horario operativo de la empresa. Si el servicio externo de tipo de cambio no responde, el sistema deberá seguir operando con el último valor registrado y permitir su modificación manual. |

## 6.4 Usabilidad

| **ID**  | **Requisito**                                                                                                        |
| ------- | -------------------------------------------------------------------------------------------------------------------- |
| RNF-009 | La interfaz deberá estar en español y ser utilizable por personal sin formación técnica, con una capacitación breve. |
| RNF-010 | El sistema deberá mostrar mensajes claros ante errores de validación e indicar qué dato debe corregirse.             |

## 6.5 Mantenibilidad

| **ID**  | **Requisito**                                                                                                                                                                                                          |
| ------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| RNF-011 | El sistema deberá tener una estructura modular y documentada, y los parámetros del negocio (vigencia de cotizaciones, porcentajes de anticipo, tabla de comisiones) deberán ser configurables sin modificar el código. |

## 6.6 Escalabilidad

| **ID**  | **Requisito**                                                                                                                                                                            |
| ------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| RNF-012 | El sistema deberá permitir agregar nuevas sucursales, almacenes, usuarios y productos sin rediseñar la aplicación, y quedar preparado para un futuro catálogo con cotizaciones en línea. |

## 6.7 Compatibilidad

| **ID**  | **Requisito**                                                                                                                                                              |
| ------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| RNF-013 | El sistema deberá funcionar en los navegadores actuales (Chrome, Edge y Firefox) con un diseño adaptable a computadora y tableta. No se desarrollará una aplicación móvil. |
| RNF-014 | El sistema deberá permitir importar el inventario inicial desde las planillas de Excel actuales y exportar los reportes en PDF.                                            |

## 6.8 Respaldo y recuperación

| **ID**  | **Requisito**                                                                                                                                                                          |
| ------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| RNF-015 | El sistema deberá realizar un respaldo automático diario de la base de datos, conservar al menos los últimos 30 respaldos y permitir restaurar la información en un máximo de 4 horas. |

## 7. Reglas de negocio

Las reglas alimentan el modelo entidad-relación y las validaciones del sistema. La relación es: regla de negocio → requisito → modelo de datos.

| **ID** | **Regla**                                                                                                                                                                                                           | **Excepción**                                                                           |
| ------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| RN-001 | Una venta bajo pedido (equipo que se importa a solicitud del cliente) solo puede registrarse con un anticipo previo del 70%.                                                                                        | Ninguna                                                                                 |
| RN-002 | Una reserva de equipo en existencia solo puede registrarse con un anticipo mínimo del 10% y tiene una vigencia máxima de 1 mes.                                                                                     | Al vencer, la reserva se libera                                                         |
| RN-003 | Una cotización tiene una vigencia por defecto de 1 semana, modificable en cada cotización.                                                                                                                          | —                                                                                       |
| RN-004 | Los vendedores no pueden modificar los precios de lista ni los datos o la descripción de los productos; solo el Gerente y el Administrador.                                                                         | El vendedor puede aplicar un descuento con autorización del Administrador o del Gerente |
| RN-005 | Los costos, los precios del proveedor y los márgenes de ganancia solo pueden ser vistos por el Gerente.                                                                                                             | —                                                                                       |
| RN-006 | Los precios se manejan internamente en USD y se muestran a los clientes en Bs, convertidos con el tipo de cambio del día (paralelo por defecto). Solo el Gerente y el Administrador pueden modificarlo manualmente. | —                                                                                       |
| RN-007 | El stock de un producto inventariable no puede ser negativo, y solo el Gerente y el Administrador pueden modificar las existencias.                                                                                 | Los productos bajo pedido pueden venderse sin stock                                     |
| RN-008 | Toda venta es al contado y debe registrar cliente (nombre y celular), vendedor, sucursal, fecha, forma de pago y comprobante. No existen ventas a crédito.                                                          | —                                                                                       |
| RN-009 | Una venta efectuada no admite devolución; solo puede cancelarse, devolviendo el anticipo. El vendedor solo puede cancelar la venta que está registrando; una vez efectuada, solo el Administrador o el Gerente.     | —                                                                                       |
| RN-010 | La garantía cubre únicamente defectos de fábrica, su duración depende del modelo y se cuenta desde la entrega. Ante un defecto de fábrica se entrega al cliente un equipo nuevo.                                    | —                                                                                       |
| RN-011 | Cuando un empleado deja la empresa, su cuenta y sus permisos deben deshabilitarse; su historial de operaciones se conserva para auditoría.                                                                          | —                                                                                       |

## 8. Casos de uso

Se documentan los 15 casos de uso del sistema y se especifican en detalle los cinco principales.

## 8.1 Diagrama general de casos de uso

![Imagen del documento](assets/image_002.png)

| **ID** | **Caso de uso**                         | **Actor principal**    | **Requisito** |
| ------ | --------------------------------------- | ---------------------- | ------------- |
| CU-001 | Gestionar usuarios y permisos           | Gerente                | RF-001        |
| CU-002 | Registrar producto                      | Gerente, Administrador | RF-002        |
| CU-003 | Gestionar precios y promociones         | Gerente, Administrador | RF-003        |
| CU-004 | Actualizar tipo de cambio               | Gerente, Administrador | RF-004        |
| CU-005 | Registrar movimiento de inventario      | Administrador          | RF-005        |
| CU-006 | Consultar existencias y ubicación       | Vendedor               | RF-006        |
| CU-007 | Registrar cliente                       | Vendedor               | RF-007        |
| CU-008 | Generar cotización                      | Vendedor               | RF-008        |
| CU-009 | Registrar venta                         | Vendedor               | RF-009        |
| CU-010 | Gestionar reserva o venta bajo pedido   | Vendedor               | RF-010        |
| CU-011 | Gestionar garantía y servicio técnico   | Servicio técnico       | RF-011        |
| CU-012 | Calcular comisiones                     | Administrador          | RF-012        |
| CU-013 | Generar reportes                        | Gerente                | RF-013        |
| CU-014 | Consultar auditoría                     | Gerente                | RF-014        |
| CU-015 | Consultar catálogo y solicitar contacto | Usuario web            | RF-015        |

## 8.2 Especificación de casos de uso

CU-002 — Registrar producto

**Actor principal:** Gerente (también el Administrador)

**Requisito y reglas relacionadas:** RF-002; RN-004, RN-005

**Precondición:** El usuario inició sesión con rol de Gerente o Administrador.

**Flujo principal:**

El usuario selecciona "Nuevo producto".

El sistema muestra el formulario con código, nombre, categoría, marca, modelo, tipo (inventariable o bajo pedido), especificaciones, stock mínimo, garantía y precio en USD.

El usuario introduce los datos y, opcionalmente, variantes, repuestos compatibles, fotografías, manuales y su traducción.

El sistema valida los campos obligatorios y genera un código único

El sistema registra el producto y el evento de auditoría.

El sistema confirma la operación.

**Flujos alternativos:**

Datos incompletos: el sistema señala los campos obligatorios.

Precio inválido (cero o negativo): el sistema solicita corregirlo.

Archivo adjunto con formato no permitido: el sistema lo rechaza.

**Postcondición:** El producto queda registrado y disponible en la consulta de existencias y, según su visibilidad, en el catálogo.

CU-005 — Registrar movimiento de inventario

**Actor principal:** Administrador (también el Gerente)

**Requisito y reglas relacionadas:** RF-005, RF-006; RN-007

**Precondición:** El usuario inició sesión; existen el producto y los almacenes involucrados.

**Flujo principal:**

El usuario selecciona "Nuevo movimiento" y elige el tipo (entrada, salida, ajuste o transferencia).

El sistema muestra el formulario correspondiente al tipo elegido.

El usuario selecciona el producto, la cantidad, el almacén de origen y destino, el número de serie si corresponde y el motivo.

El sistema valida que exista stock suficiente en el origen.

El sistema registra el movimiento y actualiza las existencias de los almacenes involucrados.

El sistema registra el evento de auditoría y confirma la operación.

**Flujos alternativos:**

Stock insuficiente: el sistema rechaza el movimiento.

Origen y destino iguales: el sistema solicita corregir.

Número de serie inexistente o repetido: el sistema rechaza el movimiento.

Recepción con faltante o daño: el usuario registra la diferencia y la observación para el reclamo al proveedor.

**Postcondición:** Las existencias quedan actualizadas y el movimiento queda en el historial.

CU-008 — Generar cotización

**Actor principal:** Vendedor

**Requisito y reglas relacionadas:** RF-008; RN-003, RN-004, RN-006

**Precondición:** El vendedor inició sesión y existe un tipo de cambio vigente.

**Flujo principal:**

El vendedor selecciona "Nueva cotización".

El vendedor busca y selecciona al cliente.

El vendedor agrega productos y cantidades; el sistema muestra la disponibilidad y el precio en Bs.

Si corresponde, el vendedor aplica un descuento autorizado.

El sistema calcula los totales con el tipo de cambio del día y asigna la vigencia por defecto de 1 semana.

El vendedor guarda la cotización.

El sistema genera el PDF y permite enviarlo por correo electrónico o WhatsApp.

**Flujos alternativos:**

Cliente nuevo: el vendedor lo registra con CU-007 y continúa.

Producto sin existencias: el sistema ofrece registrar una reserva o venta bajo pedido (CU-010).

Descuento sin autorización: el sistema solicita la aprobación del Administrador o del Gerente.

Vigencia distinta: el vendedor modifica la fecha de vencimiento.

**Postcondición:** La cotización queda registrada con estado vigente.

CU-009 — Registrar venta

**Actor principal:** Vendedor

**Requisito y reglas relacionadas:** RF-009, RF-011, RF-012; RN-004, RN-006, RN-007, RN-008, RN-009

**Precondición:** El vendedor inició sesión y los productos están en existencia, o existe una cotización vigente.

**Flujo principal:**

El vendedor selecciona "Nueva venta" o convierte una cotización vigente.

El vendedor identifica al cliente con su nombre y celular.

El vendedor agrega los productos con su número de serie o código.

El sistema calcula el total en Bs con el tipo de cambio del día y muestra su equivalente en USD.

El vendedor registra la forma de pago.

El vendedor confirma la venta.

El sistema valida las existencias, registra la venta y descuenta el inventario.

El sistema genera la garantía, la comisión y el registro de auditoría.

El sistema emite el recibo o nota de venta y la nota de garantía.

**Flujos alternativos:**

Stock insuficiente: el sistema impide la venta, salvo productos bajo pedido.

Descuento sin autorización: el sistema solicita la aprobación correspondiente.

Pago incompleto: el sistema no permite cerrar la venta porque es al contado.

Cancelación durante el registro: el vendedor puede cancelarla; una vez efectuada, solo el Administrador o el Gerente.

**Postcondición:** La venta queda registrada, el inventario actualizado y la garantía generada.

CU-011 — Gestionar garantía y servicio técnico

**Actor principal:** Servicio técnico (también el Administrador)

**Requisito y reglas relacionadas:** RF-011; RN-010

**Precondición:** La venta está registrada con su garantía y el técnico inició sesión. La solicitud puede ser ingresada por el Usuario web, un Vendedor o el Administrador.

**Flujo principal:**

Se ingresa una solicitud de servicio con el equipo o código y la descripción de la falla.

El sistema localiza la venta y verifica si la garantía está vigente.

El sistema notifica en su panel al técnico especialista.

El técnico revisa la solicitud y programa la visita con el cliente.

El técnico registra el diagnóstico y el resultado de la atención.

Si es un defecto de fábrica con garantía vigente, se registra el equipo de reemplazo y su salida de inventario (CU-005).

El técnico cierra la solicitud.

**Flujos alternativos:**

Garantía vencida: el sistema informa la situación y el servicio se registra con un monto a cobrar.

Venta no encontrada: se busca por nombre o celular del cliente.

No hay equipo disponible para el reemplazo: se ofrece una venta bajo pedido (CU-010) si el cliente lo desea.

**Postcondición:** La solicitud queda cerrada y registrada en el historial del equipo y del cliente.

## 9. Restricciones del sistema

- El sistema deberá ser una aplicación web con arquitectura cliente-servidor y base de datos relacional; las tecnologías específicas se definirán en la fase de diseño.
- El acceso será únicamente por navegador; no se desarrollará una aplicación móvil a mediano plazo.
- El sistema no incluirá el proceso de importación, la contabilidad, las ventas a crédito ni la facturación electrónica.
- Los precios internos se manejarán en USD y los precios a clientes en bolivianos.
- Los costos, precios del proveedor y márgenes estarán restringidos al Gerente.
- El tipo de cambio depende de un servicio externo cuya fuente debe definirse.

## 10. Criterios de aceptación

Cada requisito funcional se conecta con su implementación y su prueba mediante los siguientes criterios.

**RF-001 — Autenticar y gestionar usuarios, roles y permisos**

Criterio de aceptación:

El sistema rechaza credenciales incorrectas.

Cada rol accede solo a las funciones y datos que le corresponden según la matriz de permisos.

El Gerente puede crear un usuario y asignarle un rol.

Al deshabilitar una cuenta, el usuario ya no puede iniciar sesión y su historial se conserva.

**RF-002 — Gestionar productos, variantes, repuestos y documentación técnica**

Criterio de aceptación:

El sistema rechaza campos obligatorios vacíos y códigos duplicados.

Se pueden registrar variantes, repuestos compatibles y especificaciones técnicas.

Se pueden adjuntar un manual y su traducción, y consultarlos desde el inventario.

Un vendedor no puede modificar los datos de un producto.

El producto registrado aparece en la consulta de existencias.

**RF-003 — Gestionar precios, descuentos y promociones**

Criterio de aceptación:

El Gerente y el Administrador pueden modificar el precio de un producto; el Vendedor no.

Cada cambio de precio queda en el historial con usuario y fecha.

Se puede definir un kit o promoción con varios productos y un precio promocional.

**RF-004 — Gestionar el tipo de cambio**

Criterio de aceptación:

Cada día el sistema registra el tipo de cambio obtenido del servicio externo.

El Gerente y el Administrador pueden reemplazarlo manualmente y el sistema usa el valor modificado.

Si el servicio externo no responde, el sistema mantiene el último valor registrado y lo informa.

Las cotizaciones y ventas muestran el tipo de cambio aplicado.

**RF-005 — Registrar movimientos de inventario**

Criterio de aceptación:

Una entrada incrementa la existencia del almacén indicado.

Una transferencia descuenta del almacén de origen y suma al de destino.

El sistema rechaza una salida o transferencia mayor a la existencia disponible (salvo productos bajo pedido).

Cada movimiento queda con usuario, fecha y motivo, y puede registrarse la diferencia o el daño en la recepción.

**RF-006 — Consultar existencias y ubicación**

Criterio de aceptación:

La consulta muestra cantidad total, reservada y disponible por almacén y sucursal.

El sistema alerta los productos por debajo del stock mínimo.

El sistema permite identificar los productos de menor rotación.

El Servicio técnico puede consultar el inventario sin modificarlo.

**RF-007 — Gestionar clientes**

Criterio de aceptación:

El sistema exige nombre y teléfono para registrar un cliente.

Se puede consultar el historial de compras y equipos de un cliente.

El cliente registrado puede seleccionarse en cotizaciones, ventas y reservas.

**RF-008 — Gestionar cotizaciones**

Criterio de aceptación:

La cotización calcula los precios en Bs con el tipo de cambio del día.

La vigencia por defecto es de 1 semana y puede modificarse.

La cotización puede descargarse en PDF y enviarse por correo electrónico o WhatsApp.

Una cotización vencida no puede convertirse en venta sin actualizarla.

Se puede convertir una cotización vigente en venta o reserva.

**RF-009 — Registrar ventas**

Criterio de aceptación:

Al confirmar la venta se descuenta el inventario y se genera el comprobante y la nota de garantía.

La venta registra vendedor, cliente, forma de pago, tipo de cambio, sucursal y fecha.

El vendedor solo puede cancelar la venta que está registrando; una venta efectuada solo la cancela el Administrador o el Gerente.

Al cancelar una venta se restituye el inventario y se devuelve el anticipo si lo hubo.

La operación queda registrada en la auditoría.

**RF-010 — Gestionar reservas y ventas bajo pedido**

Criterio de aceptación:

El sistema no permite registrar una reserva con anticipo menor al 10% ni una venta bajo pedido con anticipo menor al 70%.

Una reserva vencida libera el stock automáticamente.

El sistema avisa al vendedor cuando el producto pedido está disponible.

La cancelación registra la devolución del anticipo.

**RF-011 — Gestionar garantías y solicitudes de servicio técnico**

Criterio de aceptación:

Al registrar una venta se crea la garantía con su fecha de vencimiento.

Al ingresar una solicitud, el sistema verifica si la garantía está vigente.

El técnico recibe la notificación en su panel y puede registrar el resultado de la atención.

Ante un defecto de fábrica se registra el equipo de reemplazo y su salida de inventario.

El sistema lista las garantías por vencer.

**RF-012 — Gestionar comisiones de vendedores**

Criterio de aceptación:

La comisión se calcula según la tabla configurada y la condición del vendedor.

Se pueden consultar comisiones obtenidas y por pagar por vendedor y período.

Al cancelar una venta la comisión se revierte.

**RF-013 — Generar reportes**

Criterio de aceptación:

Cada reporte se puede generar para el período seleccionado.

Los reportes se pueden descargar en PDF y enviar por correo electrónico.

El margen de ganancia y los costos solo se muestran al Gerente.

Los datos de los reportes coinciden con los registros de ventas e inventario.

**RF-014 — Registrar auditoría de operaciones**

Criterio de aceptación:

Las operaciones críticas generan un registro con usuario, fecha, hora y valores anterior y nuevo.

Los registros de auditoría no pueden editarse ni eliminarse desde el sistema.

El Gerente ve todos los registros y el Administrador solo los de ventas e inventario.

**RF-015 — Publicar catálogo web y recibir contactos**

Criterio de aceptación:

Un visitante puede ver el catálogo sin iniciar sesión.

El catálogo no muestra costos, proveedores ni existencias internas.

El visitante puede enviar una solicitud de contacto o de servicio y esta llega al vendedor o al técnico.

## 12. Trazabilidad de requisitos

| **Requisito** | **Caso de uso** | **Reglas de negocio**                  | **Entidades / tablas**                                                              |
| ------------- | --------------- | -------------------------------------- | ----------------------------------------------------------------------------------- |
| RF-001        | CU-001          | RN-011                                 | Usuario, Rol, Permiso                                                               |
| RF-002        | CU-002          | RN-004, RN-005, RN-007                 | Producto, Categoría, VarianteProducto, RepuestoCompatible, DocumentoTecnico         |
| RF-003        | CU-003          | RN-004, RN-005, RN-006                 | Precio, HistorialPrecio, Promoción, DetallePromoción                                |
| RF-004        | CU-004          | RN-006                                 | TipoCambio                                                                          |
| RF-005        | CU-005          | RN-007                                 | MovimientoInventario, DetalleMovimiento, Existencia, Almacén, Sucursal, UnidadSerie |
| RF-006        | CU-006          | RN-002, RN-007                         | Existencia, Almacén, Sucursal, Producto                                             |
| RF-007        | CU-007          | RN-008                                 | Cliente                                                                             |
| RF-008        | CU-008          | RN-003, RN-004, RN-006                 | Cotización, DetalleCotización, Cliente                                              |
| RF-009        | CU-009          | RN-004, RN-006, RN-007, RN-008, RN-009 | Venta, DetalleVenta, Pago, Comprobante, Cliente                                     |
| RF-010        | CU-010          | RN-001, RN-002, RN-009                 | Reserva, Anticipo, Cliente, Producto                                                |
| RF-011        | CU-011          | RN-010                                 | Garantía, SolicitudServicio, Venta, Cliente                                         |
| RF-012        | CU-012          | RN-008, RN-009                         | Comisión, TablaComisión, Venta                                                      |
| RF-013        | CU-013          | RN-005                                 | Consultas sobre Venta, Existencia, Garantía, Comisión, Cotización                   |
| RF-014        | CU-014          | RN-005, RN-008                         | Auditoría                                                                           |
| RF-015        | CU-015          | RN-004                                 | Producto, SolicitudContacto, SolicitudServicio                                      |

## 13. Versión anticipada (MVP): requisitos, alcance, limitaciones y decisiones

## 13.1 Propósito y relación con el resto del SRS

Este apartado se incorpora al final del SRS y **no modifica ni sustituye** ninguna de las secciones anteriores. Define el subconjunto del sistema que se construirá en una **versión anticipada (MVP, producto mínimo viable)** orientada a la demostración, y registra su alcance, sus limitaciones y las decisiones tomadas.

Las secciones 1 a 12 continúan describiendo el sistema completo y son la referencia de la versión final. Cuando exista una diferencia, la sección 13 indica qué se implementa en el MVP y las secciones 1 a 12 indican lo que el sistema debe cumplir en su versión final.

## 13.2 Alcance del MVP

El MVP se compone de dos niveles de funcionalidades del SRS. El Nivel 1 se implementa por completo; el Nivel 2 se implementa de forma simplificada.

**Nivel 1 — Imprescindible para la demostración (se implementa)**

| ID     | Funcionalidad del MVP                                        | Requisito del SRS | Reglas y requisitos no funcionales |
| ------ | ------------------------------------------------------------ | ----------------- | ---------------------------------- |
| MVP-01 | Inicio de sesión y acceso por roles básicos.                 | RF-001            | RN-011; RNF-001, RNF-002, RNF-003  |
| MVP-02 | Productos, marcas, categorías y variantes.                   | RF-002            | RN-004, RN-007                     |
| MVP-03 | Sucursales, almacenes y existencias.                         | RF-005, RF-006    | RN-007                             |
| MVP-04 | Entradas, salidas, ajustes y transferencias de inventario.   | RF-005            | RN-007                             |
| MVP-05 | Clientes.                                                    | RF-007            | RN-008                             |
| MVP-06 | Tipo de cambio introducido manualmente.                      | RF-004            | RN-006; RNF-008                    |
| MVP-07 | Cotizaciones con detalle y cálculo de totales.               | RF-008            | RN-003, RN-006                     |
| MVP-08 | Ventas con detalle, vendedor, cliente y registro de pago.    | RF-009            | RN-006, RN-007, RN-008, RN-009     |
| MVP-09 | Consulta de stock disponible y reportes básicos.             | RF-006, RF-013    | RN-007; RNF-006, RNF-007           |
| MVP-10 | Registro básico de auditoría de las operaciones importantes. | RF-014            | RNF-005                            |

**Nivel 2 — Implementar de forma simplificada**

| ID     | Funcionalidad del MVP                                           | Simplificación aplicada                                                                                                                                              | Requisito del SRS |
| ------ | --------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------- |
| MVP-11 | Reservas con cantidad, fecha de vencimiento, estado y anticipo. | Se registran cantidad, fecha de vencimiento, estado y anticipo, aplicando la RN-002. No incluye ventas bajo pedido ni la gestión completa de anticipos y reembolsos. | RF-010            |
| MVP-12 | Garantías vinculadas a ventas.                                  | La garantía se registra asociada a la venta, según la RN-010. Sin avisos automáticos de vencimiento.                                                                 | RF-011            |
| MVP-13 | Solicitudes de servicio técnico vinculadas a garantías.         | La solicitud se asocia a una garantía existente y tiene un estado básico; la atiende el rol Servicio técnico.                                                        | RF-011            |
| MVP-14 | Catálogo público de solo lectura.                               | Consulta sin iniciar sesión, sin costos, proveedores ni existencias internas. Sin formulario de contacto ni modo sin conexión.                                       | RF-015            |
| MVP-15 | Comprobantes sencillos para las ventas.                         | Comprobante simple generado desde la venta, en formato imprimible. Sin facturación electrónica.                                                                      | RF-009; RN-008    |

## 13.3 Requisitos funcionales del MVP

La tabla indica cómo se trata cada requisito funcional de la sección 5 en el MVP.

| ID     | Requisito                                                         | Estado en el MVP | Alcance en el MVP                                                                                                                                                                                               |
| ------ | ----------------------------------------------------------------- | ---------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| RF-001 | Autenticar y gestionar usuarios, roles y permisos                 | Parcial          | Inicio de sesión y acceso por roles básicos (Gerente, Administrador, Vendedor y Servicio técnico), con deshabilitación de cuentas (RN-011). La administración avanzada de permisos queda para la versión final. |
| RF-002 | Gestionar productos, variantes, repuestos y documentación técnica | Parcial          | Productos, marcas, categorías y variantes. Quedan fuera los repuestos compatibles, los manuales y fichas técnicas con sus traducciones, y las especificaciones públicas e internas.                             |
| RF-003 | Gestionar precios, descuentos y promociones                       | Excluido         | Fuera del MVP. Se usa un precio de lista simple por variante, necesario para cotizar y vender (decisión D-03).                                                                                                  |
| RF-004 | Gestionar el tipo de cambio                                       | Parcial          | Tipo de cambio introducido manualmente. Sin consulta automática a un servicio externo.                                                                                                                          |
| RF-005 | Registrar movimientos de inventario                               | Incluido         | Entradas, salidas, ajustes y transferencias.                                                                                                                                                                    |
| RF-006 | Consultar existencias y ubicación                                 | Incluido         | Stock disponible por sucursal, almacén y variante.                                                                                                                                                              |
| RF-007 | Gestionar clientes                                                | Incluido         | Registro, modificación y consulta de clientes.                                                                                                                                                                  |
| RF-008 | Gestionar cotizaciones                                            | Incluido         | Cotizaciones con detalle y cálculo de totales. Sin envío por correo.                                                                                                                                            |
| RF-009 | Registrar ventas                                                  | Incluido         | Ventas con detalle, vendedor, cliente y registro de pago, más comprobantes sencillos (Nivel 2).                                                                                                                 |
| RF-010 | Gestionar reservas y ventas bajo pedido                           | Simplificado     | Solo reservas con cantidad, vencimiento, estado y anticipo. Ventas bajo pedido fuera del MVP.                                                                                                                   |
| RF-011 | Gestionar garantías y solicitudes de servicio técnico             | Simplificado     | Garantías vinculadas a ventas y solicitudes de servicio técnico vinculadas a garantías.                                                                                                                         |
| RF-012 | Gestionar comisiones de vendedores                                | Excluido         | Fuera del MVP.                                                                                                                                                                                                  |
| RF-013 | Generar reportes                                                  | Parcial          | Consulta de stock disponible y reportes básicos. Sin exportación a PDF.                                                                                                                                         |
| RF-014 | Registrar auditoría de operaciones                                | Parcial          | Registro básico de las operaciones importantes (ver D-12).                                                                                                                                                      |
| RF-015 | Publicar catálogo web y recibir contactos                         | Simplificado     | Catálogo público de solo lectura. Sin recepción de contactos ni solicitudes desde la web.                                                                                                                       |

## 13.4 Requisitos no funcionales aplicables al MVP

| ID                | Estado en el MVP | Observación                                                                                                                                                                         |
| ----------------- | ---------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| RNF-001 a RNF-005 | Aplican          | Seguridad: autenticación, autorización por rol en el servidor, cierre de sesión por inactividad, HTTPS y auditoría no modificable desde la aplicación.                              |
| RNF-006, RNF-007  | Aplican          | Tiempos de respuesta con 10 usuarios concurrentes y reportes básicos en menos de 10 segundos.                                                                                       |
| RNF-008           | Parcial          | Disponibilidad del 95 % del horario operativo. El tipo de cambio es manual: se conserva el último valor registrado.                                                                 |
| RNF-009, RNF-010  | Aplican          | Interfaz en español y mensajes de error claros.                                                                                                                                     |
| RNF-011           | Parcial          | Estructura modular; se parametrizan los valores del negocio de las funcionalidades incluidas (vigencia de cotizaciones y anticipos de reserva). La tabla de comisiones queda fuera. |
| RNF-012           | Aplica           | Permite agregar sucursales, almacenes, usuarios y productos. El catálogo con cotizaciones en línea queda para la versión final.                                                     |
| RNF-013           | Aplica           | Chrome, Edge y Firefox, con diseño adaptable a computadora y tableta.                                                                                                               |
| RNF-014           | Excluido         | Importación de inventario desde Excel y exportación de reportes a PDF quedan fuera del MVP.                                                                                         |
| RNF-015           | Aplica           | Respaldo automático diario con configuración básica; las pruebas de retención y restauración se ejecutan antes de operar con datos reales.                                          |

## 13.5 Reglas de negocio en el MVP

| ID     | Estado en el MVP | Observación                                                                                                              |
| ------ | ---------------- | ------------------------------------------------------------------------------------------------------------------------ |
| RN-001 | Fuera del MVP    | Venta bajo pedido con anticipo del 70 %: no se implementa.                                                               |
| RN-002 | Aplica           | Reserva con anticipo mínimo del 10 % y vigencia máxima de 1 mes.                                                         |
| RN-003 | Aplica           | Cotización con vigencia por defecto de 1 semana, modificable en cada cotización.                                         |
| RN-004 | Parcial          | Solo el Gerente y el Administrador modifican precios y datos de productos. Los descuentos con autorización quedan fuera. |
| RN-005 | No se ejercita   | El MVP no registra costos, precios del proveedor ni márgenes (decisión D-04).                                            |
| RN-006 | Aplica           | Precios internos en USD y conversión a Bs con el tipo de cambio registrado manualmente.                                  |
| RN-007 | Aplica           | El stock de un producto inventariable no puede ser negativo.                                                             |
| RN-008 | Aplica           | Venta al contado con cliente, vendedor, sucursal, fecha, forma de pago y comprobante.                                    |
| RN-009 | Aplica           | Una venta efectuada no admite devolución. La cancelación se confirma como pendiente                                      |
| RN-010 | Aplica           | Garantía solo por defectos de fábrica, vinculada a la venta.                                                             |
| RN-011 | Aplica           | Cuenta deshabilitada al salir un empleado; se conserva su historial.                                                     |

## 13.6 Funcionalidades fuera del alcance del MVP

- Descuentos, promociones y gestión avanzada de precios (RF-003).
- Repuestos compatibles, manuales, fichas técnicas y traducciones (RF-002).
- Actualización automática del tipo de cambio desde un servicio externo (RF-004).
- Ventas bajo pedido (RN-001) y gestión completa de anticipos y reembolsos.
- Comisiones de vendedores (RF-012).
- Formulario de contacto, modo sin conexión del catálogo y recepción de solicitudes desde la web (RF-015).
- Notificaciones automáticas (stock mínimo y garantías por vencer) y envío de correos.
- Importación del inventario inicial desde Excel y exportación de reportes a PDF (RNF-014).
- Registro de costos, precios del proveedor y márgenes.
  Las exclusiones de la sección 1.2 y de la sección 9 (proceso de importación, contabilidad, ventas a crédito, facturación electrónica y aplicación móvil) se mantienen.

## 13.7 Limitaciones del MVP

- **Uso:** el MVP está orientado a la demostración. Su uso con datos reales requiere completar las funcionalidades pendientes y las pruebas de aceptación.
- **Tipo de cambio:** depende de que un usuario autorizado lo registre; las cotizaciones y ventas usan el último valor registrado.
- **Comunicaciones:** las cotizaciones y comprobantes no se envían por correo; se consultan o imprimen desde el sistema.
- **Reservas:** no cubren ventas bajo pedido ni la gestión completa de anticipos y reembolsos.
- **Garantías:** no generan avisos automáticos de vencimiento.
- **Catálogo público:** es de solo lectura, requiere conexión y no recibe contactos.
- **Reportes:** son básicos, sin exportación a PDF; el inventario inicial se registra mediante entradas de inventario.
- **Precios:** un precio de lista simple por variante, sin descuentos ni promociones.
- **Rendimiento:** se valida con 10 usuarios concurrentes (RNF-006); no se garantiza la capacidad de diseño de 30 a 60 usuarios.
- **Operación:** las operaciones internas requieren conexión; no existe aplicación móvil.

## 13.8 Decisiones del MVP

Las decisiones siguientes se toman para esta versión y deben confirmarse con el equipo. Las de carácter técnico se detallan en el Documento Técnico de Arquitectura.

| ID   | Decisión                                                                                                                                                                                           | Justificación                                                                                                                                        |
| ---- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| D-01 | Implementar el Nivel 1 por completo y el Nivel 2 de forma simplificada.                                                                                                                            | Priorizar un flujo completo de punta a punta (inventario, cotización, venta y garantía) para la demostración.                                        |
| D-02 | Introducir el tipo de cambio manualmente, sin servicio externo.                                                                                                                                    | Reduce la dependencia externa y es coherente con la edición manual prevista en RNF-008. Se registran el origen y el usuario.                         |
| D-03 | Usar un precio de lista simple por variante, en USD.                                                                                                                                               | Las cotizaciones y ventas necesitan un precio; los descuentos y promociones quedan fuera. Pendiente de validar.                                      |
| D-04 | No registrar costos, precios del proveedor ni márgenes.                                                                                                                                            | Evita ejercer la RN-005 en el MVP. Pendiente de validar.                                                                                             |
| D-05 | Construir un monolito modular con Laravel 13 sobre PHP 8.5.12-1, PostgreSQL 18 y renderizado en servidor con Blade, en un VPS único sin contenedores.                                              | Menor complejidad operativa para un equipo pequeño y un alcance acotado.                                                                             |
| D-06 | Fijar PHP 8.5.12-1 como versión mínima de PHP.                                                                                                                                                     | Incorpora los parches y correcciones de seguridad de la rama 8.5; no se instalan versiones 8.5.x anteriores. Laravel 13 requiere PHP 8.3 o superior. |
| D-07 | Mantener los cuatro roles funcionales con permisos básicos por módulo y acción.                                                                                                                    | Cumple RNF-002 sin incluir administración avanzada de permisos.                                                                                      |
| D-08 | Aplicar la RN-002 a las reservas y liberar automáticamente el stock de las reservas vencidas.                                                                                                      | La reserva conserva su sentido con cantidad, vencimiento, estado y anticipo.                                                                         |
| D-09 | Vincular la garantía a la venta; la modalidad por unidad (número de serie) queda pendiente.                                                                                                        | Alcance simplificado del Nivel 2.                                                                                                                    |
| D-10 | Publicar un catálogo de solo lectura, sin contacto ni modo sin conexión.                                                                                                                           | Evita Service Worker y almacenamiento local en el MVP.                                                                                               |
| D-11 | Emitir un comprobante sencillo en formato imprimible.                                                                                                                                              | La facturación electrónica ya está fuera del alcance del SRS.                                                                                        |
| D-12 | Auditar un conjunto básico de operaciones: inicio de sesión, movimientos de inventario, cotizaciones, ventas y pagos, reservas, garantías y solicitudes de servicio, y cambios del tipo de cambio. | Cumple RF-014 y RNF-005 con un alcance reducido. La aplicación escribe los registros y la base de datos impide alterarlos.                           |
| D-13 | Excluir el envío de correos y las notificaciones automáticas.                                                                                                                                      | No son necesarios para la demostración.                                                                                                              |
| D-14 | Probar los flujos de demostración sobre PostgreSQL real.                                                                                                                                           | Las reglas de integridad dependen de funciones, triggers y restricciones de la base de datos.                                                        |

## 13.9 Criterios de aceptación del MVP

El MVP se considera aceptable cuando se demuestran los escenarios siguientes:

- Un usuario inicia sesión y solo accede a las funciones de su rol.
- Se registran productos con marca, categoría y variantes, y se consultan sus existencias por almacén.
- Se registran una entrada, una salida, un ajuste y una transferencia, y el sistema no permite stock negativo.
- Se registra el tipo de cambio manualmente y se usa en cotizaciones y ventas.
- Se emite una cotización con detalle y total correcto.
- Se registra una venta con detalle, vendedor, cliente y pago, se descuenta el stock y se emite un comprobante sencillo.
- Se crea una reserva con anticipo mínimo y fecha de vencimiento; al vencer, se libera el stock.
- Se registra una garantía vinculada a una venta y una solicitud de servicio técnico vinculada a esa garantía.
- El catálogo público se consulta sin iniciar sesión y no muestra costos, proveedores ni existencias internas.
- Las operaciones importantes quedan registradas en la auditoría y no pueden modificarse desde la aplicación.
- Las consultas de stock y los reportes básicos cumplen los tiempos de RNF-006 y RNF-007.
