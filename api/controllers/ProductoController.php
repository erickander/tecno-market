<?php
/**
 * TECNO-MARKET | Controlador de productos
 */

class ProductoController
{
    private const REGLAS_CREAR = [
        'nombre'      => ['requerido', 'texto:3,100'],
        'precio'      => ['requerido', 'numero', 'min:0.01'],
        'stock'       => ['requerido', 'entero', 'min:0'],
        'descripcion' => ['texto:0,500'],
        'imagen'      => ['texto:0,255'],
        'activo'      => ['entero', 'en:0,1'],
    ];

    public function listar(): void
    {
        $pagina   = max(1, (int) Request::query('pagina', 1));
        $limite   = (int) Request::query('limite', 8);
        $limite   = $limite < 1 ? 8 : min($limite, 50);
        $busqueda = trim((string) Request::query('q', ''));
        $orden    = (string) Request::query('orden', 'id');

        $resultado = Producto::listar($pagina, $limite, $busqueda, $orden);

        Response::ok($resultado, 'Listado de productos');
    }

    public function ver(string $id): void
    {
        $producto = Producto::buscar((int) $id);

        if ($producto === null) {
            Response::error(404, 'Producto no encontrado');
        }

        Response::ok($producto, 'Producto encontrado');
    }

    public function crear(): void
    {
        $datos = Request::body();

        $errores = Validador::validar($datos, self::REGLAS_CREAR);

        if ($errores !== []) {
            Response::error(422, 'Datos invalidos', $errores);
        }

        $producto = Producto::crear($datos);

        Response::ok($producto, 'Producto creado correctamente', 201);
    }

    public function actualizar(string $id): void
    {
        $producto = Producto::buscar((int) $id);

        if ($producto === null) {
            Response::error(404, 'Producto no encontrado');
        }

        $datos = Request::body();

        if ($datos === []) {
            Response::error(422, 'Datos invalidos', ['cuerpo' => ['No se envio ningun campo para actualizar']]);
        }

        $reglas = [];

        foreach (self::REGLAS_CREAR as $campo => $lista) {
            if (array_key_exists($campo, $datos)) {
                // En actualizacion parcial quitamos 'requerido'
                $reglas[$campo] = array_values(array_diff($lista, ['requerido']));
            }
        }

        $errores = Validador::validar($datos, $reglas);

        if ($errores !== []) {
            Response::error(422, 'Datos invalidos', $errores);
        }

        $actualizado = Producto::actualizar((int) $id, $datos);

        Response::ok($actualizado, 'Producto actualizado correctamente');
    }

    public function eliminar(string $id): void
    {
        $producto = Producto::buscar((int) $id);

        if ($producto === null) {
            Response::error(404, 'Producto no encontrado');
        }

        Producto::eliminar((int) $id);

        Response::noContent();
    }
}
