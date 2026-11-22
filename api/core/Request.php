<?php
/**
 * TECNO-MARKET | Lectura de la peticion HTTP
 */

class Request
{
    private static ?array $cuerpo = null;
    private static ?string $ruta = null;

    public static function metodo(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Devuelve la ruta relativa a /api/v1
     * Ej: /tecno_market/api/v1/productos/3  ->  /productos/3
     *     /api/v1/productos/3               ->  /productos/3
     */
    public static function ruta(): string
    {
        if (self::$ruta !== null) {
            return self::$ruta;
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $marca = '/api/v1';
        $pos = strpos($uri, $marca);

        $relativa = $pos !== false
            ? substr($uri, $pos + strlen($marca))
            : $uri;

        $relativa = '/' . trim($relativa, '/');

        self::$ruta = $relativa;
        return self::$ruta;
    }

    public static function query(string $clave, mixed $defecto = null): mixed
    {
        return $_GET[$clave] ?? $defecto;
    }

    public static function body(): array
    {
        if (self::$cuerpo !== null) {
            return self::$cuerpo;
        }

        $crudo = file_get_contents('php://input');

        if ($crudo === false || trim($crudo) === '') {
            self::$cuerpo = [];
            return self::$cuerpo;
        }

        $datos = json_decode($crudo, true);

        if (!is_array($datos)) {
            Response::error(400, 'El cuerpo de la peticion debe ser un JSON valido');
        }

        self::$cuerpo = $datos;
        return self::$cuerpo;
    }

    public static function header(string $nombre): ?string
    {
        $clave = 'HTTP_' . strtoupper(str_replace('-', '_', $nombre));

        if (isset($_SERVER[$clave])) {
            return $_SERVER[$clave];
        }

        if (strcasecmp($nombre, 'Content-Type') === 0 && isset($_SERVER['CONTENT_TYPE'])) {
            return $_SERVER['CONTENT_TYPE'];
        }

        return null;
    }
}
