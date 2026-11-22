<?php
/**
 * TECNO-MARKET | Modelo de productos
 */

class Producto
{
    private const ORDENES = [
        'id'          => 'id_producto ASC',
        'nombre'      => 'nombre ASC',
        'precio_asc'  => 'precio ASC',
        'precio_desc' => 'precio DESC',
        'stock'       => 'stock DESC',
    ];

    public static function listar(
        int $pagina,
        int $limite,
        string $busqueda,
        string $orden,
        bool $incluirInactivos = false
    ): array {
        $pdo = Database::conexion();

        $where  = $incluirInactivos ? '1 = 1' : 'activo = 1';
        $params = [];

        if ($busqueda !== '') {
            // MySQL nativo no permite reutilizar el mismo marcador en una consulta
            $where .= ' AND (nombre LIKE :busqueda_nombre OR descripcion LIKE :busqueda_descripcion)';
            $params[':busqueda_nombre']       = '%' . $busqueda . '%';
            $params[':busqueda_descripcion']  = '%' . $busqueda . '%';
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE {$where}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $orderBy = self::ORDENES[$orden] ?? self::ORDENES['id'];

        $sql = "SELECT id_producto, nombre, descripcion, precio, stock, imagen, activo,
                       created_at, updated_at
                  FROM productos
                 WHERE {$where}
                 ORDER BY {$orderBy}
                 LIMIT :limite OFFSET :offset";

        $stmt = $pdo->prepare($sql);

        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($pagina - 1) * $limite, PDO::PARAM_INT);
        $stmt->execute();

        $items = array_map([self::class, 'formatear'], $stmt->fetchAll());

        return [
            'items' => $items,
            'meta'  => [
                'pagina'  => $pagina,
                'limite'  => $limite,
                'total'   => $total,
                'paginas' => $limite > 0 ? (int) ceil($total / $limite) : 0,
                'busqueda' => $busqueda,
            ],
        ];
    }

    public static function buscar(int $id): ?array
    {
        $pdo = Database::conexion();
        $stmt = $pdo->prepare(
            'SELECT id_producto, nombre, descripcion, precio, stock, imagen, activo,
                    created_at, updated_at
               FROM productos
              WHERE id_producto = :id'
        );
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();

        return $fila ? self::formatear($fila) : null;
    }

    public static function crear(array $datos): array
    {
        $pdo = Database::conexion();

        $stmt = $pdo->prepare(
            'INSERT INTO productos (nombre, descripcion, precio, stock, imagen, activo)
             VALUES (:nombre, :descripcion, :precio, :stock, :imagen, :activo)'
        );

        $stmt->execute([
            ':nombre'      => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] ?? null,
            ':precio'      => $datos['precio'],
            ':stock'       => $datos['stock'],
            ':imagen'      => $datos['imagen'] ?? 'placeholder.svg',
            ':activo'      => $datos['activo'] ?? 1,
        ]);

        return self::buscar((int) $pdo->lastInsertId());
    }

    public static function actualizar(int $id, array $datos): array
    {
        $pdo = Database::conexion();

        $permitidos = ['nombre', 'descripcion', 'precio', 'stock', 'imagen', 'activo'];
        $asignaciones = [];
        $params = [':id' => $id];

        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $datos)) {
                $asignaciones[] = "{$campo} = :{$campo}";
                $params[":{$campo}"] = $datos[$campo];
            }
        }

        if ($asignaciones === []) {
            throw new ApiException(422, 'No se envio ningun campo para actualizar');
        }

        $sql = 'UPDATE productos SET ' . implode(', ', $asignaciones) . ' WHERE id_producto = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return self::buscar($id);
    }

    public static function tienePedidos(int $id): bool
    {
        $pdo = Database::conexion();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM detalle_pedido WHERE id_producto = :id');
        $stmt->execute([':id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function eliminar(int $id): void
    {
        if (self::tienePedidos($id)) {
            throw new ApiException(
                409,
                'El producto tiene pedidos asociados. Puede desactivarlo con PUT y activo = 0.'
            );
        }

        $pdo = Database::conexion();
        $stmt = $pdo->prepare('DELETE FROM productos WHERE id_producto = :id');
        $stmt->execute([':id' => $id]);
    }

    private static function formatear(array $fila): array
    {
        return [
            'id_producto' => (int) $fila['id_producto'],
            'nombre'      => $fila['nombre'],
            'descripcion' => $fila['descripcion'],
            'precio'      => (float) $fila['precio'],
            'stock'       => (int) $fila['stock'],
            'imagen'      => $fila['imagen'],
            'activo'      => (bool) $fila['activo'],
            'created_at'  => $fila['created_at'],
            'updated_at'  => $fila['updated_at'],
        ];
    }
}
