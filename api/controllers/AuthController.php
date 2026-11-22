<?php
/**
 * TECNO-MARKET | Autenticacion
 */

class AuthController
{
    public function login(): void
    {
        $datos = Request::body();

        $errores = Validador::validar($datos, [
            'nombre_usuario' => ['requerido', 'texto:3,50'],
            'contrasena'     => ['requerido', 'texto:1,100'],
        ]);

        if ($errores !== []) {
            Response::error(422, 'Datos invalidos', $errores);
        }

        $usuario = Usuario::buscarPorNombre(trim($datos['nombre_usuario']));

        if ($usuario === null || !password_verify((string) $datos['contrasena'], $usuario['contrasena'])) {
            Response::error(401, 'Credenciales incorrectas');
        }

        if (!(bool) $usuario['activo']) {
            Response::error(401, 'Usuario inactivo');
        }

        Usuario::actualizarUltimoAcceso((int) $usuario['id_usuario']);
        $usuario['ultimo_acceso'] = date('Y-m-d H:i:s');

        $jwt = Jwt::generar([
            'sub'        => (int) $usuario['id_usuario'],
            'rol'        => $usuario['rol'],
            'id_cliente' => $usuario['id_cliente'] !== null ? (int) $usuario['id_cliente'] : null,
        ]);

        Response::ok([
            'token'     => $jwt['token'],
            'expira_en' => $jwt['expira_en'],
            'usuario'   => Usuario::formatear($usuario),
        ], 'Sesion iniciada correctamente');
    }

    public function me(): void
    {
        $payload = Auth::usuario();

        if ($payload === null) {
            Response::error(401, 'Token de autorizacion requerido');
        }

        $usuario = Usuario::buscar((int) $payload['sub']);

        if ($usuario === null) {
            Response::error(404, 'Usuario no encontrado');
        }

        Response::ok($usuario, 'Sesion activa');
    }
}
