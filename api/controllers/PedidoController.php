<?php
/**
 * TECNO-MARKET | Controlador de pedidos
 */

class PedidoController
{
    private const REGLAS_CLIENTE = [
        'nombre'    => ['requerido', 'texto:3,100'],
        'correo'    => ['requerido', 'texto:5,100', 'email'],
        'telefono'  => ['requerido', 'texto:7,15'],
        'direccion' => ['texto:0,150'],
    ];

    public function crear(): void
    {
        $datos = Request::body();

        $items = $datos['items'] ?? null;

        if (!is_array($items) || $items === []) {
            Response::error(422, 'Datos invalidos', [
                'items' => ['El pedido debe incluir al menos un producto'],
            ]);
        }

        foreach ($items as $indice => $item) {
            if (!is_array($item)) {
                Response::error(422, 'Datos invalidos', [
                    "items.{$indice}" => ['Cada item debe ser un objeto con id_producto y cantidad'],
                ]);
            }

            $erroresItem = Validador::validar($item, [
                'id_producto' => ['requerido', 'entero', 'min:1'],
                'cantidad'    => ['requerido', 'entero', 'min:1'],
            ]);

            if ($erroresItem !== []) {
                Response::error(422, 'Datos invalidos', [
                    "items.{$indice}" => array_merge(...array_values($erroresItem)),
                ]);
            }
        }

        $idCliente = null;

        if (Auth::rol() === 'cliente') {
            // Un cliente solo puede registrar pedidos a su propio nombre
            $idCliente = Auth::idCliente();

            if ($idCliente === null) {
                Response::error(403, 'El usuario no tiene un cliente asociado');
            }
        } elseif (isset($datos['id_cliente'])) {
            $cliente = Cliente::buscar((int) $datos['id_cliente']);

            if ($cliente === null) {
                Response::error(404, 'Cliente no encontrado');
            }

            $idCliente = $cliente['id_cliente'];
        } elseif (isset($datos['cliente']) && is_array($datos['cliente'])) {
            $erroresCliente = Validador::validar($datos['cliente'], self::REGLAS_CLIENTE);

            if ($erroresCliente !== []) {
                Response::error(422, 'Datos invalidos', $erroresCliente);
            }

            $cliente = Cliente::obtenerOCrear($datos['cliente']);
            $idCliente = $cliente['id_cliente'];
        } else {
            Response::error(422, 'Datos invalidos', [
                'cliente' => ['Debe enviar id_cliente o los datos del cliente'],
            ]);
        }

        $pedido = Pedido::crear($idCliente, $items);

        Response::ok($pedido, 'Pedido registrado correctamente', 201);
    }

    public function listar(): void
    {
        $pagina = max(1, (int) Request::query('pagina', 1));
        $limite = (int) Request::query('limite', 10);
        $limite = $limite < 1 ? 10 : min($limite, 50);
        $estado = trim((string) Request::query('estado', ''));

        if ($estado !== '' && !in_array($estado, Pedido::ESTADOS, true)) {
            Response::error(422, 'Datos invalidos', [
                'estado' => ['El estado debe ser uno de: ' . implode(', ', Pedido::ESTADOS)],
            ]);
        }

        Response::ok(Pedido::listar($pagina, $limite, $estado), 'Listado de pedidos');
    }

    public function ver(string $id): void
    {
        $pedido = Pedido::buscar((int) $id);

        if ($pedido === null) {
            Response::error(404, 'Pedido no encontrado');
        }

        Auth::autorizarPropietario($pedido['cliente']['id_cliente']);

        Response::ok($pedido, 'Pedido encontrado');
    }

    public function porCliente(string $id): void
    {
        $cliente = Cliente::buscar((int) $id);

        if ($cliente === null) {
            Response::error(404, 'Cliente no encontrado');
        }

        Auth::autorizarPropietario((int) $id);

        $pedidos = Pedido::porCliente((int) $id);

        Response::ok([
            'cliente' => $cliente,
            'items'   => $pedidos,
            'total'   => count($pedidos),
        ], 'Pedidos del cliente');
    }

    public function cambiarEstado(string $id): void
    {
        $pedido = Pedido::buscar((int) $id);

        if ($pedido === null) {
            Response::error(404, 'Pedido no encontrado');
        }

        $datos = Request::body();

        $errores = Validador::validar($datos, [
            'estado' => ['requerido', 'en:' . implode(',', Pedido::ESTADOS)],
        ]);

        if ($errores !== []) {
            Response::error(422, 'Datos invalidos', $errores);
        }

        $actualizado = Pedido::cambiarEstado((int) $id, $datos['estado']);

        Response::ok($actualizado, 'Estado del pedido actualizado');
    }
}
