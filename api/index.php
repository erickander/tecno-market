<?php
/**
 * TECNO-MARKET | Punto de entrada de la API
 *
 * URL base: http://localhost/tecno_market/api/v1
 */

declare(strict_types=1);

date_default_timezone_set('America/Guayaquil');

header('X-Content-Type-Options: nosniff');

spl_autoload_register(static function (string $clase): void {
    foreach (['core', 'controllers', 'models', 'config'] as $carpeta) {
        $archivo = __DIR__ . '/' . $carpeta . '/' . $clase . '.php';

        if (is_file($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

set_exception_handler(static function (Throwable $e): void {
    if ($e instanceof ApiException) {
        Response::error($e->codigoHttp, $e->getMessage(), $e->errores);
    }

    error_log('[TECNO-MARKET] ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());

    $mensaje = 'Error interno del servidor';
    $config = require __DIR__ . '/config/config.php';

    if (!empty($config['app']['debug'])) {
        $mensaje = $e->getMessage();
    }

    Response::error(500, $mensaje);
});

$router = new Router();

require __DIR__ . '/routes/api.php';

$router->dispatch();
