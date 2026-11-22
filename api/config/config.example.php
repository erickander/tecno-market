<?php
/**
 * TECNO-MARKET | Configuracion de la aplicacion
 *
 * Este archivo NO se versiona (ver .gitignore).
 * Copiar desde config.example.php en cada equipo.
 *
 * En Docker las variables de entorno sobreescriben estos valores.
 */

return [
    'db' => [
        'host'       => getenv('DB_HOST') ?: '127.0.0.1',
        'port'       => getenv('DB_PORT') ?: '3306',
        'nombre'     => getenv('DB_NAME') ?: 'tecno_market',
        'usuario'    => getenv('DB_USER') ?: 'root',
        'contrasena' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
    ],
    'app' => [
        'nombre'  => 'TECNO-MARKET API',
        'version' => 'v1',
        'debug'   => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    ],
    'jwt' => [
        // P4: mover el secreto real a config.php y no versionarlo
        'secreto'    => getenv('JWT_SECRET') ?: 'CAMBIA_ESTE_SECRETO_EN_PRODUCCION',
        'expiracion' => 3600,
        'emisor'     => 'tecno-market',
    ],
];
