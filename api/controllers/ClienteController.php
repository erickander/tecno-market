<?php
/**
 * TECNO-MARKET | Controlador de clientes
 */

class ClienteController
{
    private const REGLAS = [
        'nombre'    => ['requerido', 'texto:3,100'],
        'correo'    => ['requerido', 'texto:5,100', 'email'],
        'telefono'  => ['requerido', 'texto:7,15'],
        'direccion' => ['texto:0,150'],
    ];

    public function crear(): void
    {
        $datos = Request::body();

        $errores = Validador::validar($datos, self::REGLAS);

        if ($errores !== []) {
            Response::error(422, 'Datos invalidos', $errores);
        }

        $cliente = Cliente::crear($datos);

        Response::ok($cliente, 'Cliente registrado correctamente', 201);
    }

    public function ver(string $id): void
    {
        $cliente = Cliente::buscar((int) $id);

        if ($cliente === null) {
            Response::error(404, 'Cliente no encontrado');
        }

        Response::ok($cliente, 'Cliente encontrado');
    }
}
