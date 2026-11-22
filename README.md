# TECNO-MARKET

Tienda virtual de productos tecnológicos desarrollada para el taller grupal de
**Sistemas Distribuidos – Unidad 4**.

Incluye una API REST en PHP 8.2 puro, base de datos MySQL/MariaDB, frontend en
HTML/CSS/JavaScript y autenticación con JWT.

---

## Funcionalidades

- Catálogo con búsqueda, orden y paginación
- Detalle de producto y carrito de compras
- Registro de clientes y creación de pedidos
- Control de stock transaccional (evita vender más unidades de las disponibles)
- Autenticación con JWT y roles `administrador` / `cliente`
- Panel de administración: CRUD de productos y cambio de estado de pedidos
- Imágenes locales y soporte de imágenes por URL

---

## Requisitos

| Opción | Necesitas |
|---|---|
| **A. XAMPP** | XAMPP con PHP 8.2+ y MySQL/MariaDB |
| **B. Docker** | Docker Desktop con WSL2 |

---

## Opción A — XAMPP

1. Clona el repositorio dentro de `htdocs`:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/erickander/tecno-market.git
```

2. Crea la base de datos e importa los scripts:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS tecno_market CHARACTER SET utf8mb4;"
cmd /c "C:\xampp\mysql\bin\mysql.exe -u root < C:\xampp\htdocs\tecno-market\sql\01_esquema.sql"
cmd /c "C:\xampp\mysql\bin\mysql.exe -u root < C:\xampp\htdocs\tecno-market\sql\02_datos_prueba.sql"
```

3. Copia la configuración:

```powershell
copy api\config\config.example.php api\config\config.php
```

4. Enciende **Apache** y **MySQL** desde XAMPP.

5. Abre:

```
http://localhost/tecno-market/public/
```

> Si la carpeta se llama `tecno_market`, ajusta la URL.

---

## Opción B — Docker

```powershell
docker compose up -d --build
```

| Servicio | URL / puerto |
|---|---|
| Tienda | http://localhost:8080/public/ |
| API | http://localhost:8080/api/v1 |
| MySQL | localhost:3307 (usuario `tecno` / `tecno123`) |

La primera vez importa automáticamente el esquema y los datos de prueba.

Comandos útiles:

```powershell
docker compose ps
docker compose logs -f web
docker compose down          # detener
docker compose down -v       # detener y borrar la base
```

---

## Credenciales de prueba

| Usuario | Contraseña | Rol |
|---|---|---|
| `admin` | `Admin123*` | administrador |
| `cliente` | `Cliente123*` | cliente (id_cliente 1) |

---

## Endpoints principales

| Método | Endpoint | Acceso |
|---|---|---|
| POST | `/auth/login` | Público |
| GET | `/auth/me` | Autenticado |
| GET | `/productos` | Público |
| GET | `/productos/{id}` | Público |
| POST / PUT / DELETE | `/productos` | Administrador |
| POST | `/clientes` | Público |
| GET | `/clientes/{id}` | Admin o dueño |
| POST | `/pedidos` | Cliente |
| GET | `/pedidos` | Administrador |
| GET | `/pedidos/{id}` | Admin o dueño |
| GET | `/clientes/{id}/pedidos` | Admin o dueño |
| PATCH | `/pedidos/{id}/estado` | Administrador |

Respuesta uniforme:

```json
{ "ok": true, "data": {}, "mensaje": "..." }
{ "ok": false, "error": { "codigo": 404, "mensaje": "..." } }
```

---

## Estructura del proyecto

```
tecno_market/
├── api/                  API REST (router, controladores, modelos, JWT)
├── public/               Frontend (catálogo, carrito, login, admin)
├── sql/                  Esquema y datos de prueba
├── docs/                 Reparto de responsabilidades, seguridad y Postman
├── Dockerfile
├── docker-compose.yml
└── README.md
```

---

## Pruebas con Postman

1. Importa `docs/postman_collection.json`.
2. Importa `docs/postman_environment_xampp.json` o `docs/postman_environment_docker.json`.
3. Ejecuta primero **Login admin** y **Login cliente** (guardan los tokens).
4. Corre el resto de la colección.

También puedes usar `docs/pruebas_api.http` con la extensión REST Client de VS Code.

---

## Notas

- El secreto JWT se define en `api/config/config.php` (no se versiona).
- En producción el token debería viajar en cookie `HttpOnly` y todo el sitio sobre HTTPS.
- Las imágenes del catálogo tienen licencia libre; ver `public/img/productos/CREDITOS.md`.
- El diseño de la seguridad está documentado en `docs/seguridad.md`.
