# Seguridad — Autenticación con JWT

Implementación real del módulo de seguridad de TECNO-MARKET (antes asignado a la Persona 4).

## 1. Flujo de autenticación

```
Cliente                          API                         Base de datos
   |                              |                                |
   |-- POST /auth/login --------->|                                |
   |   { usuario, contraseña }    |-- SELECT usuario ------------->|
   |                              |<-- hash bcrypt ----------------|
   |                              |-- password_verify()            |
   |                              |-- UPDATE ultimo_acceso ------->|
   |<-- 200 { token, usuario } ---|                                |
   |                              |                                |
   |-- GET /productos (público) ->|                                |
   |                              |                                |
   |-- POST /productos ---------->|                                |
   |   Authorization: Bearer ...  |-- Jwt::validar()               |
   |                              |-- verifica rol administrador   |
   |<-- 201 / 401 / 403 ----------|                                |
```

## 2. Token

- **Algoritmo:** HMAC SHA-256 (`HS256`), implementación propia sin librerías.
- **Duración:** 3600 segundos (1 hora).
- **Claims:**

```json
{
  "sub": 1,
  "rol": "administrador",
  "id_cliente": null,
  "iss": "tecno-market",
  "iat": 1730000000,
  "exp": 1730003600
}
```

- **Secreto:** `api/config/config.php` → `jwt.secreto` (no versionado).
- **Validación:** formato de 3 partes → firma con `hash_equals` → expiración → emisor.

## 3. Credenciales

Las contraseñas se guardan con `password_hash()` (bcrypt) y se verifican con `password_verify()`.
Nunca se devuelve el hash en las respuestas.

| Usuario | Contraseña | Rol | id_cliente |
|---|---|---|---|
| `admin` | `Admin123*` | administrador | — |
| `cliente` | `Cliente123*` | cliente | 1 |

## 4. Operaciones públicas y protegidas

| Endpoint | Acceso |
|---|---|
| `POST /auth/login` | Público |
| `GET /productos`, `GET /productos/{id}` | Público |
| `POST /clientes` | Público |
| `POST /pedidos` | Cliente autenticado |
| `POST/PUT/DELETE /productos` | Administrador |
| `GET /pedidos` | Administrador |
| `PATCH /pedidos/{id}/estado` | Administrador |
| `GET /clientes/{id}` | Administrador o el propio cliente |
| `GET /pedidos/{id}` | Administrador o dueño del pedido |
| `GET /clientes/{id}/pedidos` | Administrador o el propio cliente |
| `GET /auth/me` | Cualquier usuario autenticado |

## 5. Códigos de respuesta

| Situación | Código | Mensaje |
|---|---|---|
| Sin cabecera `Authorization` | 401 | Token de autorizacion requerido |
| Firma inválida o token vencido | 401 | Token invalido o vencido |
| Credenciales incorrectas | 401 | Credenciales incorrectas |
| Usuario inactivo | 401 | Usuario inactivo |
| Rol sin permiso | 403 | No tiene permisos para realizar esta operacion |
| Recurso de otro cliente | 403 | No puede acceder a recursos de otro cliente |

## 6. Implementación

| Archivo | Responsabilidad |
|---|---|
| `api/core/Jwt.php` | Generar y validar el token |
| `api/core/Auth.php` | Middleware `requerir($roles)`, `usuario()`, `autorizarPropietario()` |
| `api/controllers/AuthController.php` | Login y consulta de sesión |
| `api/models/Usuario.php` | Buscar usuario y actualizar `ultimo_acceso` |
| `api/routes/api.php` | Declara los roles de cada ruta |
| `public/login.html` + `js/login.js` | Formulario de acceso |
| `public/js/api.js` | Envía `Authorization: Bearer` si hay token |
| `public/admin.html` + `js/admin.js` | Guardián de sesión y cierre de sesión |

## 7. Cliente

- El token se guarda en `localStorage` con la clave `tecno_token`.
- El panel admin verifica la sesión con `GET /auth/me` al cargar.
- Si el token falta o expiró, redirige a `login.html?destino=admin.html`.
- Al cerrar sesión se eliminan `tecno_token` y `tecno_usuario`.

**Nota de producción:** en un despliegue real el token debería viajar en una cookie
`HttpOnly` + `Secure` y servirse siempre por HTTPS.

## 8. Pruebas ejecutadas

| Prueba | Resultado |
|---|---|
| Login admin | 200 + token |
| Login con contraseña incorrecta | 401 |
| `POST /productos` sin token | 401 |
| `POST /productos` con token de cliente | 403 |
| `POST /productos` con token de admin | 201 |
| `GET /clientes/1` con cliente dueño | 200 |
| `GET /clientes/2` con cliente ajeno | 403 |
| `POST /pedidos` con cliente (fuerza su `id_cliente`) | 201 |
| `GET /pedidos` con cliente | 403 |
| `GET /pedidos` con admin | 200 |
| Token con firma inválida | 401 |
| `GET /auth/me` con token válido | 200 |
