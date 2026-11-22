<?php
/**
 * TECNO-MARKET | Middleware de autenticacion y autorizacion
 *
 * Uso en las rutas:
 *   $router->post('/productos', [...], ['administrador']);
 *   $router->get('/clientes/{id}', [...], ['administrador', 'propietario']);
 *
 * 'propietario' permite el paso a cualquier usuario con rol cliente;
 * el controlador confirma despues que el recurso sea suyo con
 * Auth::autorizarPropietario().
 */

class Auth
{
    private static ?array $usuario = null;

    public static function requerir(array $roles = []): void
    {
        // Ruta publica
        if ($roles === []) {
            return;
        }

        $token = self::tokenDeCabecera();

        if ($token === null) {
            header('WWW-Authenticate: Bearer');
            Response::error(401, 'Token de autorizacion requerido');
        }

        $payload = Jwt::validar($token);

        self::$usuario = $payload;

        $rol = (string) ($payload['rol'] ?? '');

        if (in_array($rol, $roles, true)) {
            return;
        }

        if ($rol === 'cliente' && in_array('propietario', $roles, true)) {
            return;
        }

        Response::error(403, 'No tiene permisos para realizar esta operacion');
    }

    public static function usuario(): ?array
    {
        return self::$usuario;
    }

    public static function rol(): ?string
    {
        return self::$usuario['rol'] ?? null;
    }

    public static function idUsuario(): ?int
    {
        return isset(self::$usuario['sub']) ? (int) self::$usuario['sub'] : null;
    }

    public static function idCliente(): ?int
    {
        return isset(self::$usuario['id_cliente']) ? (int) self::$usuario['id_cliente'] : null;
    }

    public static function esAdministrador(): bool
    {
        return self::rol() === 'administrador';
    }

    /**
     * Permite el acceso al administrador o al cliente dueno del recurso.
     */
    public static function autorizarPropietario(int $idCliente, string $mensaje = 'No puede acceder a recursos de otro cliente'): void
    {
        if (self::esAdministrador()) {
            return;
        }

        if (self::idCliente() !== $idCliente) {
            Response::error(403, $mensaje);
        }
    }

    public static function tokenDeCabecera(): ?string
    {
        $cabecera = Request::header('Authorization');

        if ($cabecera === null) {
            return null;
        }

        if (preg_match('/^Bearer\s+(.+)$/i', trim($cabecera), $m)) {
            return trim($m[1]);
        }

        return null;
    }
}
