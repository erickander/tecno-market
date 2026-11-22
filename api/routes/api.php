<?php
/**
 * TECNO-MARKET | Definicion de rutas
 *
 * El tercer argumento indica los roles que exigira el middleware de P4.
 * Mientras Auth::requerir() sea un esqueleto, todas las rutas responden.
 */

$router->get('/', function (): void {
    Response::ok([
        'api'       => 'TECNO-MARKET',
        'version'   => 'v1',
        'estado'    => 'operativa',
        'documentacion' => 'docs/reparto_responsabilidades.md',
        'endpoints' => [
            'GET    /ping',
            'GET    /productos',
            'GET    /productos/{id}',
            'POST   /productos',
            'PUT    /productos/{id}',
            'DELETE /productos/{id}',
            'POST   /clientes',
            'GET    /clientes/{id}',
            'POST   /pedidos',
            'GET    /pedidos/{id}',
            'GET    /clientes/{id}/pedidos',
            'PATCH  /pedidos/{id}/estado',
        ],
    ], 'API TECNO-MARKET');
});

$router->get('/ping', function (): void {
    Response::ok([
        'estado' => 'ok',
        'hora'   => date('c'),
    ], 'pong');
});

// ------------------------------------------------------------
// Autenticacion
// ------------------------------------------------------------
$router->post('/auth/login', [AuthController::class, 'login']);
$router->get('/auth/me', [AuthController::class, 'me'], ['administrador', 'cliente']);

// ------------------------------------------------------------
// Productos (publico: catalogo / protegido: administracion)
// ------------------------------------------------------------
$router->get('/productos', [ProductoController::class, 'listar']);
$router->get('/productos/{id}', [ProductoController::class, 'ver']);
$router->post('/productos', [ProductoController::class, 'crear'], ['administrador']);
$router->put('/productos/{id}', [ProductoController::class, 'actualizar'], ['administrador']);
$router->delete('/productos/{id}', [ProductoController::class, 'eliminar'], ['administrador']);

// ------------------------------------------------------------
// Clientes
// ------------------------------------------------------------
$router->post('/clientes', [ClienteController::class, 'crear']);
$router->get('/clientes/{id}', [ClienteController::class, 'ver'], ['administrador', 'propietario']);

// ------------------------------------------------------------
// Pedidos
// ------------------------------------------------------------
$router->post('/pedidos', [PedidoController::class, 'crear'], ['cliente']);
$router->get('/pedidos', [PedidoController::class, 'listar'], ['administrador']);
$router->get('/pedidos/{id}', [PedidoController::class, 'ver'], ['administrador', 'propietario']);
$router->get('/clientes/{id}/pedidos', [PedidoController::class, 'porCliente'], ['administrador', 'propietario']);
$router->patch('/pedidos/{id}/estado', [PedidoController::class, 'cambiarEstado'], ['administrador']);
