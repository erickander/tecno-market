# Taller Grupal Unidad 4 — TECNO-MARKET

## Documento de reparto de responsabilidades y definiciones técnicas

**Asignatura:** Sistemas Distribuidos
**Proyecto:** Diseño de una tienda virtual — TECNO-MARKET
**Grupo:** ____________________
**Fecha:** ____________________

| Integrante | Rol asignado | Nombre completo |
|---|---|---|
| P1 | Análisis, arquitectura y frontend público | |
| P2 | Base de datos y persistencia | |
| P3 | API REST y JSON | |
| P4 | Seguridad, administración e integración | |

> Documento de trabajo interno. No forma parte del informe final, pero de él salen
> las secciones 2 a 9 y los cuatro casos prácticos del entregable.

---

## 1. Objetivo del documento

Definir **quién hace qué** en el taller, con entregables verificables y sin
ambigüedades, para que las cuatro personas trabajen en paralelo sin pisarse
archivos y para que el informe final sea coherente con la aplicación real.

El entregable final es **un único informe técnico** con 11 apartados más el
**sistema funcional** en PHP + MySQL sobre XAMPP.

---

## 2. Decisiones técnicas congeladas

Estas decisiones no se cambian sin avisar al grupo. Todo el informe debe
describir exactamente esto.

| Tema | Decisión |
|---|---|
| Empresa del caso | TECNO-MARKET |
| Backend | PHP 8.2 puro (sin frameworks ni Composer) |
| Base de datos | MariaDB 10.4 (XAMPP), base `tecno_market` |
| Frontend | HTML + JavaScript (`fetch`) + Bootstrap 5 por CDN |
| URL base de la API | `http://localhost/tecno_market/api/v1` |
| Formato de intercambio | JSON UTF-8, claves en `snake_case`, fechas ISO 8601 |
| Autenticación | JWT propio, algoritmo HS256, expiración 60 minutos |
| Cabecera de seguridad | `Authorization: Bearer <token>` |
| Contraseñas | `password_hash()` / `password_verify()` (bcrypt) |
| Roles | `administrador`, `cliente` |
| Moneda | USD, `DECIMAL(10,2)` |
| Paginación | `?pagina=1&limite=10` con bloque `meta` |

### 2.1 Formato uniforme de respuestas

Éxito:

```json
{
  "ok": true,
  "data": { },
  "mensaje": "Operación realizada correctamente"
}
```

Error:

```json
{
  "ok": false,
  "error": {
    "codigo": 404,
    "mensaje": "Producto no encontrado",
    "errores": { }
  }
}
```

### 2.2 Códigos HTTP en uso

`200` OK · `201` Creado · `204` Sin contenido · `400` Solicitud mal formada ·
`401` No autenticado · `403` Sin permisos · `404` No encontrado ·
`409` Conflicto (stock o dependencia) · `422` Datos inválidos · `500` Error interno

---

## 3. Tabla maestra de responsabilidades

| Orden | Integrante / Rol | Qué debe hacer | Qué debe entregar | Criterio de aceptación |
|---|---|---|---|---|
| **1°** | **P1 — Análisis, arquitectura y frontend público** | Definir la empresa, los usuarios, las funciones y los requisitos; diseñar el diagrama cliente-servidor; crear el esqueleto del proyecto y verificar el entorno XAMPP; construir catálogo, detalle, login y registro | Documento de análisis (propósito, usuarios, funciones, RF/RNF) · Diagrama de arquitectura (imagen + versión editable) · Descripción de componentes y del flujo solicitud/respuesta · Estructura de carpetas creada · Guía de instalación paso a paso · Frontend público funcional · Caso 1 | Abrir `http://localhost/tecno_market/public/` muestra el catálogo real leído desde MySQL; la búsqueda y el filtro por categoría funcionan; cualquier ruta inexistente de la API devuelve JSON y no HTML |
| **2°** | **P2 — Base de datos y persistencia** | Diseñar el modelo E-R y el modelo relacional; escribir el script con las tablas, claves, CHECKs y triggers; cargar datos de prueba; implementar la conexión PDO y los modelos de productos y pedidos | `sql/01_esquema.sql` y `sql/02_datos_prueba.sql` · Diccionario de datos (campo, tipo, PK/FK, restricción) · Diagrama E-R · Semilla de administrador y cliente con hash · `api/config/Database.php` · Modelos `Producto` y `Pedido` con transacción de stock · Caso 3 (diseño de la solución) | El script se importa en phpMyAdmin sin errores; las tablas quedan con sus relaciones; un `INSERT` con precio o stock negativo es rechazado por la base; la conexión responde correctamente |
| **3°** | **P3 — API REST y JSON** | Construir el enrutamiento y los servicios de productos, clientes y pedidos; validar las entradas; devolver JSON con el código HTTP correcto; configurar `.htaccess`; documentar los endpoints y probarlos en Postman | `api/index.php` y núcleo (`Router`, `Request`, `Response`, `Validador`) · Controladores de Producto, Cliente y Pedido · `api/.htaccess` · Tabla de endpoints (método, ruta, finalidad, datos, código) · Ejemplos JSON (éxito, 201, 404, 422) · Colección Postman · Caso 2 | Productos responde 200/201/204/404 según corresponda; `GET /productos/999` devuelve 404 con el JSON uniforme; la tabla de endpoints coincide exactamente con lo que responde Postman |
| **4°** | **P4 — Seguridad, administración e integración** | Implementar registro, login, hash de contraseñas y JWT; crear el middleware que protege rutas por rol; auditar el control de inventario; construir el panel de administración; documentar seguridad y comparativa SOAP/REST | `core/Jwt.php`, `core/Auth.php`, `AuthController` · Middleware de validación de token · Panel admin (`admin.html`, `admin.js`) · Prueba de concurrencia documentada · Documento del esquema de autenticación · Documento de cabeceras HTTP · Cuadro SOAP vs REST con conclusión · Caso 4 | El login entrega token; una ruta protegida sin token responde **401**; un token de cliente en ruta de administrador responde **403**; dos compras de 7 unidades con stock 10 terminan con una **201**, una **409** y stock final **3** |
| **Cierre** | **Todo el grupo** | Unir el informe, revisar que documento y aplicación digan lo mismo, reunir evidencias y preparar la exposición | Informe único con los 11 entregables en orden · Conclusiones del equipo · Capturas reales (login, catálogo, errores 401, 403 y 409) · Presentación con cuatro partes | El informe sigue el orden solicitado y cada integrante explica y demuestra su parte en vivo |

---

## 4. Detalle por integrante

### 4.1 P1 — Análisis, arquitectura y frontend público

**Archivos que posee**

```
public/index.html
public/producto.html
public/login.html
public/registro.html
public/css/app.css
public/js/api.js
public/js/catalogo.js
public/js/auth.js
docs/guia_instalacion.md
```

**Qué debe hacer**

1. Describir la empresa, los usuarios (visitante, cliente, administrador),
   las funciones principales y los requisitos funcionales y no funcionales.
2. Diseñar el diagrama cliente-servidor con seis componentes: usuario → interfaz
   → servidor → API REST → base de datos MySQL → respuesta.
3. Crear el esqueleto de carpetas `api/`, `public/`, `sql/`, `docs/`.
   Esto desbloquea el trabajo de los demás.
4. Verificar el entorno: PHP 8.2, extensiones `pdo_mysql`, `openssl`,
   `mbstring`, Apache y MySQL activos.
5. Escribir `public/js/api.js` como único envoltorio de `fetch`
   (`apiGet`, `apiPost`, `apiPut`, `apiDelete`, manejo del token).
   Es la interfaz que P4 consumirá en el panel de administración.
6. Construir el catálogo con paginación, buscador y filtro por categoría,
   la vista de detalle, el login y el registro con validación en el navegador.
7. Resolver el **Caso 1** con una tabla de validaciones en cliente y en servidor.

**Checklist**

- [ ] Documento de análisis con propósito, usuarios, funciones y RF-01 a RF-10
- [ ] Requisitos no funcionales (seguridad, integridad, rendimiento, usabilidad)
- [ ] Diagrama de arquitectura con los seis componentes identificados
- [ ] Descripción del flujo solicitud/respuesta con un ejemplo concreto
- [ ] Estructura de carpetas creada y documentada
- [ ] Guía de instalación (XAMPP, importar SQL, abrir el sitio)
- [ ] `api.js` terminado y comunicado a P4
- [ ] Catálogo, detalle, login y registro funcionando
- [ ] Caso 1 redactado con validaciones de cliente y servidor

**Depende de:** nada, arranca primero.
**Alimenta a:** P2 (requisitos), P3 (pantallas y campos), P4 (interfaz de `api.js`).

---

### 4.2 P2 — Base de datos y persistencia

**Archivos que posee**

```
sql/01_esquema.sql
sql/02_datos_prueba.sql
api/config/config.php
api/config/Database.php
api/models/Producto.php
api/models/Pedido.php
```

**Qué debe hacer**

1. Diseñar el modelo entidad-relación y el modelo relacional completo.
2. Escribir el script de creación con claves primarias, foráneas,
   restricciones `UNIQUE`, `CHECK` y comportamientos `ON DELETE` / `ON UPDATE`.
3. Implementar `subtotal` como columna generada y `pedidos.total`
   actualizado por trigger.
4. Cargar datos de prueba coherentes: categorías, 15 productos, 10 clientes,
   5 pedidos con detalle y dos usuarios con hash bcrypt real.
5. Implementar `api/config/Database.php` (PDO, UTF-8, excepciones activadas).
6. Implementar los modelos `Producto` y `Pedido`, incluida la transacción
   de descuento de stock con `SELECT ... FOR UPDATE`.
7. Redactar el diccionario de datos y el Caso 3.

**Interfaz que debe exponer a P3** (nombres de métodos acordados)

```php
Producto::listar($pagina, $limite, $busqueda, $id_categoria)
Producto::buscar($id)
Producto::crear(array $datos)
Producto::actualizar($id, array $datos)
Producto::eliminar($id)
Producto::tienePedidos($id)

Pedido::crear($id_cliente, array $items)
Pedido::buscar($id)
Pedido::porCliente($id_cliente)
Pedido::cambiarEstado($id, $estado)
```

**Checklist**

- [ ] Tablas: `categorias`, `productos`, `clientes`, `usuarios`, `pedidos`, `detalle_pedido`
- [ ] Claves primarias y foráneas con comportamiento definido
- [ ] `CHECK (precio >= 0)`, `CHECK (stock >= 0)`, `CHECK (cantidad > 0)`
- [ ] `UNIQUE` en `clientes.correo` y `usuarios.nombre_usuario`
- [ ] Campos de auditoría `created_at` / `updated_at`
- [ ] `detalle_pedido.precio_unitario` y `subtotal` generado
- [ ] Trigger que recalcula `pedidos.total`
- [ ] Semilla con administrador y cliente (contraseñas hasheadas)
- [ ] Diccionario de datos campo por campo
- [ ] Diagrama E-R
- [ ] `Database.php` probado
- [ ] `Pedido::crear()` con transacción y bloqueo de fila
- [ ] Caso 3 explicado (condición de carrera y solución)

**Depende de:** requisitos de P1.
**Alimenta a:** P3 (esquema congelado y modelos) y P4 (consulta de stock).

---

### 4.3 P3 — API REST y JSON

**Archivos que posee**

```
api/index.php
api/.htaccess
api/core/Router.php
api/core/Request.php
api/core/Response.php
api/core/Validador.php
api/controllers/ProductoController.php
api/controllers/ClienteController.php
api/controllers/PedidoController.php
api/models/Cliente.php
api/routes/api.php
```

**Qué debe hacer**

1. Construir el enrutador con soporte para `GET`, `POST`, `PUT`, `PATCH`,
   `DELETE` y parámetros de ruta `{id}`.
2. Implementar las respuestas uniformes de éxito y error.
3. Implementar el CRUD de productos con paginación, búsqueda y filtro.
4. Implementar el registro y la consulta de clientes.
5. Implementar la creación de pedidos, la consulta de pedidos por cliente
   y el cambio de estado.
6. Configurar `.htaccess` con `RewriteEngine`, exclusión de archivos reales
   y cabecera `Content-Type: application/json`.
7. Elaborar la tabla de endpoints, los ejemplos JSON y la colección Postman.
8. Resolver el **Caso 2**.

**Checklist**

- [ ] Router funcionando con los cinco métodos
- [ ] Respuesta de ruta inexistente en JSON con código 404
- [ ] Validación de entrada con mensajes por campo (422)
- [ ] Paginación con bloque `meta` (página, límite, total, páginas)
- [ ] CRUD de productos completo
- [ ] Registro y consulta de clientes con control de propiedad
- [ ] Creación de pedido con respuesta 201 y 409 cuando falta stock
- [ ] `.htaccess` probado
- [ ] Tabla de endpoints definitiva
- [ ] Ejemplos JSON válidos y coherentes con la base
- [ ] Colección Postman exportada
- [ ] Caso 2 redactado

**Depende de:** esquema y modelos de P2 (no se pueden probar endpoints sin tablas).
**Alimenta a:** P4 (rutas que debe proteger) y P1 (contrato que consume el frontend).

---

### 4.4 P4 — Seguridad, administración e integración

**Archivos que posee**

```
api/core/Jwt.php
api/core/Auth.php
api/controllers/AuthController.php
public/admin.html
public/js/admin.js
```

**Qué debe hacer**

1. Implementar `Jwt::generar()` y `Jwt::validar()` con HS256,
   `base64url`, firma HMAC y verificación de expiración.
2. Implementar el registro con `password_hash()` y el login con
   `password_verify()`, devolviendo el token y los datos del usuario.
3. Crear el middleware que exige token válido y rol suficiente:
   sin token → 401, token vencido o firma inválida → 401,
   rol insuficiente → 403.
4. Construir el panel de administración con pestañas de Productos y Pedidos,
   reutilizando `public/js/api.js` de P1.
5. Auditar la transacción de stock de P2 y ejecutar la prueba de concurrencia
   (10 unidades, dos compras de 7).
6. Redactar el esquema de autenticación, el documento de cabeceras HTTP,
   el cuadro comparativo SOAP vs REST y el Caso 4.

**Checklist**

- [ ] `Jwt.php` con generación y validación probadas
- [ ] Secreto del token definido en `config.php` (bloque de seguridad)
- [ ] Registro con hash verificable en la base
- [ ] Login que devuelve token, rol e identificadores
- [ ] Middleware aplicado a todas las rutas protegidas
- [ ] Pruebas 401 (sin token), 401 (token vencido) y 403 (rol incorrecto)
- [ ] Panel admin con CRUD de productos y cambio de estado de pedidos
- [ ] Prueba de concurrencia con evidencia (dos solicitudes simultáneas)
- [ ] Documento del esquema de autenticación (login → token → autorización)
- [ ] Documento de `.htaccess` y cabeceras `Content-Type` y `Authorization`
- [ ] Cuadro comparativo SOAP vs REST con conclusión técnica
- [ ] Caso 4 redactado (integración SOAP → REST)

**Depende de:** rutas de P3 y modelo de P2.
**Alimenta a:** evidencias de seguridad y secciones 7, 8 y 9 del informe.

---

## 5. Matriz resumen de entregables

| Integrante | Entrega en total |
|---|---|
| **P1** | 2 documentos (análisis del sistema y guía de instalación) + 1 diagrama de arquitectura + esqueleto del proyecto + 4 pantallas con su JavaScript + Caso 1 |
| **P2** | 1 script de base de datos + 1 diccionario de datos + 1 diagrama E-R + datos de prueba + módulo de conexión + 2 modelos con transacción + Caso 3 |
| **P3** | Núcleo del router + 3 controladores + tabla de endpoints + ejemplos JSON + colección Postman + `.htaccess` + Caso 2 |
| **P4** | 3 módulos de seguridad (JWT, Auth, middleware) + panel admin + prueba de concurrencia + 4 documentos (tokens, cabeceras, comparativa y Caso 4) |
| **Todos** | Informe final + conclusiones + evidencias + presentación |

### 5.1 Correspondencia con las secciones del informe

| Sección del informe | Responsable | Apoyo |
|---|---|---|
| 1. Portada | P1 | Todos (datos) |
| 2. Descripción del sistema y requisitos | P1 | P2 |
| 3. Diagrama de arquitectura cliente-servidor | P1 | P3 |
| 4. Diseño de base de datos y diagrama E-R | P2 | P1 |
| 5. Tabla de endpoints de la API REST | P3 | P4 |
| 6. Ejemplos de solicitudes y respuestas JSON | P3 | P2 |
| 7. Esquema de autenticación mediante tokens | P4 | P3 |
| 8. `.htaccess` y cabeceras HTTP | P3 | P4 (cabecera `Authorization`) |
| 9. Cuadro comparativo SOAP y REST | P4 | — |
| 10. Casos prácticos 1 a 4 | P1, P2, P3, P4 (uno cada uno) | P4 revisa formato |
| 11. Conclusiones técnicas del equipo | P1 redacta | Aportes de todos |

> **Diferencia respecto al reparto de ejemplo:** allí P4 resolvía los cuatro
> casos prácticos. Aquí se asigna cada caso al dueño del módulo correspondiente
> (Caso 1 → P1, Caso 3 → P2, Caso 2 → P3, Caso 4 → P4) para equilibrar la carga.
> P4 hace la revisión final de formato y coherencia.

---

## 6. Cronograma por semanas

| Semana | P1 | P2 | P3 | P4 |
|---|---|---|---|---|
| 1 | Análisis, esqueleto y verificación del entorno | Esquema SQL y datos de prueba | Router base y respuesta JSON | JWT y `AuthController` |
| 2 | Catálogo y detalle de producto | Modelos y transacción de stock | Endpoints de productos y clientes | Middleware y login probado |
| 3 | Login y registro en la interfaz; Caso 1 | Trigger de total y pruebas de integridad | Pedidos y colección Postman | Panel admin y prueba 409 |
| 4 | Integrar informe (portada, secciones 2 y 3) | Cerrar sección 4 | Cerrar secciones 5, 6 y 8 | Cerrar secciones 7 y 9; evidencias |

**Regla de oro:** el esquema de la base de datos es el único bloqueo real.
P2 lo entrega en la semana 1 y a partir de ahí P3 y P4 trabajan en paralelo.

---

## 7. Mapa de dependencias

```
P1 (requisitos + esqueleto)
        |
        v
P2 (modelo de datos + modelos)
        |
        v
P3 (endpoints + JSON + .htaccess)  <---- P1 (frontend que consume)
        |
        v
P4 (token + middleware + panel admin)
        |
        v
Cierre: informe, evidencias y exposición
```

Nadie empieza una tarea cuyo insumo no esté entregado. Si un insumo se retrasa,
se avisa el mismo día para reacomodar el trabajo.

---

## 8. Reglas de trabajo

1. **Un archivo, un dueño.** Si alguien necesita cambiar un archivo ajeno,
   lo solicita; no lo edita por su cuenta.
2. **El contrato manda.** Endpoints, campos y códigos de error son los de este
   documento. Si alguien necesita uno nuevo, se avisa al grupo y se actualiza
   el contrato antes de programar.
3. **`config.php` es compartido con cuidado.** P2 es dueño del archivo, pero el
   bloque de seguridad (secreto JWT y expiración) lo define P4 y solo se toca
   avisando.
4. **Cada quien documenta lo que programó.** El informe se redacta en paralelo
   al código, no al final.
5. **Toda afirmación del informe debe ser demostrable.** Si el documento dice
   que una ruta devuelve 409, debe existir la captura o la prueba en Postman.
6. **Revisión cruzada obligatoria.** Cada sección la revisa alguien distinto
   de quien la escribió antes del cierre.

---

## Anexo A. Contrato de la API (12 endpoints)

| Método | Endpoint | Rol | Éxito | Errores posibles |
|---|---|---|---|---|
| POST | `/auth/login` | Público | 200 | 401, 422 |
| GET | `/productos` | Público | 200 | 400 |
| GET | `/productos/{id}` | Público | 200 | 404 |
| POST | `/productos` | Administrador | 201 | 401, 403, 422 |
| PUT | `/productos/{id}` | Administrador | 200 | 401, 403, 404, 422 |
| DELETE | `/productos/{id}` | Administrador | 204 | 401, 403, 404, 409 |
| POST | `/clientes` | Público | 201 | 409, 422 |
| GET | `/clientes/{id}` | Admin o el propio cliente | 200 | 401, 403, 404 |
| POST | `/pedidos` | Cliente autenticado | 201 | 401, 409, 422 |
| GET | `/clientes/{id}/pedidos` | Admin o el propio cliente | 200 | 401, 403, 404 |
| GET | `/pedidos/{id}` | Admin o dueño del pedido | 200 | 401, 403, 404 |
| PATCH | `/pedidos/{id}/estado` | Administrador | 200 | 401, 403, 404, 422 |

**Reglas asociadas**

- `DELETE /productos/{id}` devuelve **409** si el producto tiene pedidos;
  en ese caso se ofrece desactivarlo (`activo = 0`) en lugar de borrarlo.
- `GET /productos` acepta `pagina`, `limite`, `q` y `id_categoria`.
- Los listados incluyen `meta`: página actual, límite, total de registros
  y total de páginas.
- `POST /pedidos` recibe un arreglo de ítems (`id_producto`, `cantidad`) y
  descuenta stock dentro de una transacción.

---

## Anexo B. Modelo de datos v2 (resumen)

| Tabla | Campos principales | Observaciones |
|---|---|---|
| `categorias` | `id_categoria`, `nombre` (UNIQUE), `descripcion` | Nueva, organiza el catálogo |
| `productos` | `id_producto`, `id_categoria` (FK), `nombre`, `descripcion`, `precio` (CHECK ≥ 0), `stock` (CHECK ≥ 0), `activo`, `created_at`, `updated_at` | Integridad de precio y stock |
| `clientes` | `id_cliente`, `nombre`, `correo` (UNIQUE), `telefono`, `direccion`, `created_at` | Sin duplicados de correo |
| `usuarios` | `id_usuario`, `nombre_usuario` (UNIQUE), `contrasena` VARCHAR(255), `rol` ENUM, `id_cliente` (FK UNIQUE, NULL), `activo`, `ultimo_acceso` | Hash bcrypt |
| `pedidos` | `id_pedido`, `id_cliente` (FK), `fecha`, `estado` ENUM, `total` DECIMAL(12,2), `updated_at` | `total` recalculado por trigger |
| `detalle_pedido` | `id_detalle`, `id_pedido` (FK CASCADE), `id_producto` (FK), `cantidad` (CHECK > 0), `precio_unitario`, `subtotal` AS (`cantidad * precio_unitario`) STORED | Precio histórico del pedido |

**Justificación de `precio_unitario`:** si el precio del producto cambia, los
pedidos anteriores deben conservar el precio con el que se vendieron.

**Estados del pedido:** `pendiente`, `procesando`, `enviado`, `entregado`, `cancelado`.

**Solución al control de inventario (Caso 3):** dentro de una transacción,
se bloquea la fila del producto con `SELECT stock FROM productos WHERE
id_producto = ? FOR UPDATE`, se verifica que el stock alcance, se descuenta y
se confirma. Si no alcanza, se hace `ROLLBACK` y se responde **409 Conflict**.
El `CHECK (stock >= 0)` actúa como última barrera de integridad.

---

## Anexo C. Pruebas de aceptación

| # | Prueba | Resultado esperado | Responsable |
|---|---|---|---|
| 1 | Importar `01_esquema.sql` y `02_datos_prueba.sql` | Sin errores; tablas y relaciones creadas | P2 |
| 2 | `INSERT` de producto con precio negativo | Rechazado por la base | P2 |
| 3 | `GET /productos` | 200 con listado, paginación y `meta` | P3 |
| 4 | `GET /productos/999` | 404 con JSON de error uniforme | P3 |
| 5 | `POST /productos` sin token | 401 | P4 |
| 6 | `POST /productos` con token de cliente | 403 | P4 |
| 7 | `POST /productos` con token de administrador y datos válidos | 201 y registro visible en la base | P3 / P4 |
| 8 | `POST /productos` con precio 0 o nombre vacío | 422 con errores por campo | P3 |
| 9 | Abrir el catálogo en el navegador | Productos reales desde MySQL, búsqueda y filtro operativos | P1 |
| 10 | Compra de 7 unidades con stock 10 (dos veces simultáneas) | Una 201 y una 409; stock final 3 | P4 |
| 11 | `DELETE` de producto con pedidos | 409 y sugerencia de desactivar | P3 |
| 12 | Login con contraseña incorrecta | 401 con mensaje genérico | P4 |

---

## Anexo D. Marcadores por completar

- [ ] Nombres reales de los cuatro integrantes
- [ ] Número de grupo
- [ ] Fecha de entrega
- [ ] Nombre comercial definitivo (el caso propone TECNO-MARKET)
- [ ] Enlace o captura de la colección Postman
- [ ] Fecha de la exposición y orden de participación
