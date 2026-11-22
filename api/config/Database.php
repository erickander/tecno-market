<?php
/**
 * TECNO-MARKET | Conexion PDO (patron singleton)
 */

class Database
{
    private static ?PDO $conexion = null;

    public static function conexion(): PDO
    {
        if (self::$conexion === null) {
            $config = require __DIR__ . '/config.php';
            $db = $config['db'];

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $db['host'],
                $db['port'],
                $db['nombre']
            );

            try {
                self::$conexion = new PDO($dsn, $db['usuario'], $db['contrasena'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('Error de conexion MySQL: ' . $e->getMessage());
                Response::error(500, 'No se pudo conectar con la base de datos');
            }
        }

        return self::$conexion;
    }

    public static function config(): array
    {
        return require __DIR__ . '/config.php';
    }
}
