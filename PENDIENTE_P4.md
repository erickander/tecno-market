# Entrega para la Persona 4 — Módulo de seguridad

Estado del proyecto: **API REST y frontend funcionando sin autenticación**.
Tu tarea es agregar JWT, login y control de roles **sin refactorizar** lo existente.

---

## 1. Qué ya está hecho

| Componente | Estado |
|---|---|
| Base de datos (5 tablas, CHECKs, triggers, `precio_unitario`, `total`) | ✅ |
| API REST: productos, clientes, pedidos (`/api/v1`) | ✅ |
| Respuestas uniformes `{ok, data}` / `{ok, error}` | ✅ |
| Validaciones del servidor (422) | ✅ |
| Transacción de stock con `SELECT ... FOR UPDATE` (201 / 409) | ✅ |
| Frontend: catálogo, detalle, carrito, panel admin | ✅ |
| **Usuarios semilla con bcrypt** | ✅ listos para tu login |
| **Auth::requerir()** | ⚠️ esqueleto (deja pasar todo) |
| **JWT / login / roles** | ❌ pendiente (tu parte) |

Credenciales de prueba ya cargadas en la base:

| Usuario | Contraseña | Rol |
|---|---|---|
| `admin` | `Admin123*` | administrador |
| `cliente` | `Cliente123*` | cliente (vinculado al cliente #1) |

---

## 2. Archivos que debes crear o completar

```
api/core/Jwt.php                  (nuevo)      generar / validar HS256
api/controllers/AuthController.php (nuevo)     POST /auth/login
api/core/Auth.php                 (completar)  requerir() + usuario()
api/routes/api.php                (agregar)    POST /auth/login
api/models/Usuario.php            (nuevo)      buscarPorNombre(), actualizarUltimoAcceso()
public/login.html                 (nuevo)      formulario de acceso
public/js/login.js                (nuevo)      guardar token en localStorage
public/js/api.js                  (ya envía)   Authorization: Bearer si existe tecno_token
public/admin.html                 (ajustar)    redirigir a login si no hay token
```

La configuración del JWT ya existe en `api/config/config.php`:

```php
'jwt' => [
    'secreto'    => getenv('JWT_SECRET') ?: 'CAMBIA_ESTE_SECRETO_EN_PRODUCCION',
    'expiracion' => 3600,
    'emisor'     => 'tecno-market',
];
```

---

## 3. Contrato de seguridad

| Situación | Código | Mensaje sugerido |
|---|---|---|
| Ruta protegida sin cabecera `Authorization` | **401** | Token de autorizacion requerido |
| Token con firma inválida o vencido | **401** | Token invalido o vencido |
| Token válido pero rol sin permiso | **403** | No tiene permisos para realizar esta operacion |
| Login correcto | **200** | Token + datos del usuario |
| Credenciales incorrectas | **401** | Credenciales incorrectas (sin especificar cuál falló) |
| Usuario inactivo | **401** | Usuario inactivo |

Payload del token sugerido:

```json
{
  "iss": "tecno-market",
  "sub": 1,
  "rol": "administrador",
  "id_cliente": null,
  "iat": 1730000000,
  "exp": 1730003600
}
```

Respuesta del login:

```json
{
  "ok": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "expira_en": 3600,
    "usuario": {
      "id_usuario": 1,
      "nombre_usuario": "admin",
      "rol": "administrador",
      "id_cliente": null
    }
  },
  "mensaje": "Sesion iniciada correctamente"
}
```

---

## 4. Rutas que ya declaran sus roles

En `api/routes/api.php` cada ruta protegida ya lleva el tercer argumento:

```php
$router->post('/productos', [ProductoController::class, 'crear'], ['administrador']);
$router->get('/clientes/{id}', [ClienteController::class, 'ver'], ['administrador', 'propietario']);
$router->post('/pedidos', [PedidoController::class, 'crear'], ['cliente']);
$router->get('/pedidos', [PedidoController::class, 'listar'], ['administrador']);
```

`Router::dispatch()` ya llama a `Auth::requerir($roles)` antes de ejecutar el controlador.
Solo debes implementar ese método. **No cambies el router.**

Para `propietario` necesitas comparar `id_cliente` del token con el `{id}` de la ruta.
Sugerencia: en `Auth::requerir()` guarda el payload en una propiedad estática y expón
`Auth::usuario()`; en los controladores, si el rol es `cliente`, compara los IDs.

---

## 5. Pruebas de aceptación

| # | Prueba | Esperado |
|---|---|---|
| 1 | `POST /auth/login` con `admin` / `Admin123*` | 200 + token |
| 2 | `POST /auth/login` con contraseña incorrecta | 401 |
| 3 | `POST /productos` sin token | 401 |
| 4 | `POST /productos` con token de `cliente` | 403 |
| 5 | `POST /productos` con token de `admin` y datos válidos | 201 |
| 6 | `GET /clientes/1` con token de `cliente` cuyo `id_cliente` es 1 | 200 |
| 7 | `GET /clientes/2` con token de `cliente` cuyo `id_cliente` es 1 | 403 |
| 8 | `POST /pedidos` con token de `cliente` | 201 |

### Ejemplos con curl

```bash
# Login
curl -i -X POST http://localhost/tecno_market/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"nombre_usuario\":\"admin\",\"contrasena\":\"Admin123*\"}"

# Ruta protegida
curl -i -X POST http://localhost/tecno_market/api/v1/productos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d "{\"nombre\":\"Producto de prueba\",\"precio\":10,\"stock\":5}"

# Sin token (debe ser 401)
curl -i -X POST http://localhost/tecno_market/api/v1/productos \
  -H "Content-Type: application/json" \
  -d "{\"nombre\":\"Sin token\",\"precio\":10,\"stock\":5}"
```

---

## 6. Frontend

- `public/js/api.js` **ya** agrega `Authorization: Bearer <token>` si existe la clave
  `tecno_token` en `localStorage`. No hace falta tocarlo.
- Crea `public/login.html` y guarda el token con:
  `localStorage.setItem('tecno_token', data.token)`.
- En `public/admin.html` agrega un guardián al inicio:
  si no hay `tecno_token`, redirige a `login.html`.
- Para cerrar sesión: `localStorage.removeItem('tecno_token')`.

---

## 7. Base de datos

No necesitas modificar el esquema. La tabla `usuarios` ya tiene:
`id_usuario`, `nombre_usuario` (UNIQUE), `contrasena` (bcrypt), `rol`
(`administrador` / `cliente`), `id_cliente` (UNIQUE, nullable), `activo`, `ultimo_acceso`.

Al hacer login correcto, actualiza `ultimo_acceso = NOW()`.

---

## 8. Cómo levantar el proyecto

1. Iniciar Apache y MySQL desde XAMPP.
2. Importar `sql/01_esquema.sql` y `sql/02_datos_prueba.sql` (phpMyAdmin o consola).
3. Copiar `api/config/config.example.php` a `api/config/config.php` y ajustar credenciales.
4. Abrir `http://localhost/tecno_market/public/`.

API: `http://localhost/tecno_market/api/v1`
