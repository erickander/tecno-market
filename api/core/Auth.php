<?php
/**
 * TECNO-MARKET | Middleware de seguridad (PENDIENTE - P4)
 *
 * Contrato que debe respetar la Persona 4:
 *  - Auth::requerir(array $roles) valida el token JWT de la cabecera
 *    Authorization: Bearer <token>.
 *  - Sin token, token vencido o firma invalida  -> Response::error(401, ...)
 *  - Rol insuficiente                            -> Response::error(403, ...)
 *  - Si $roles esta vacio, la ruta es publica y no se valida nada.
 *  - Auth::usuario() devuelve el payload del token o null.
 *
 * Mientras tanto las rutas quedan abiertas para poder demostrar la tienda.
 */

class Auth
{
    public static function requerir(array $roles = []): void
    {
        // TODO (P4): implementar validacion de JWT.
        //
        // $token = self::tokenDeCabecera();
        // if ($token === null) {
        //     Response::error(401, 'Token de autorizacion requerido');
        // }
        // $payload = Jwt::validar($token);   // lanza 401 si es invalido o vencido
        // if ($roles !== [] && !in_array($payload['rol'] ?? '', $roles, true)) {
        //     Response::error(403, 'No tiene permisos para realizar esta operacion');
        // }
        // self::$usuarioActual = $payload;

        return;
    }

    public static function usuario(): ?array
    {
        // TODO (P4): devolver el payload del token validado.
        return null;
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
