<?php
/**
 * TECNO-MARKET | Modelo de pedidos
 *
 * La creacion del pedido usa una transaccion con bloqueo de fila
 * (SELECT ... FOR UPDATE) para evitar vender mas stock del disponible.
 */

class Pedido
{
    public const ESTADOS = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];

    public static function crear(int $idCliente, array $items): array
    {
        $pdo = Database::conexion();

        // Unificar lineas repetidas del mismo producto
        $porProducto = [];

        foreach ($items as $item) {
            $idProducto = (int) ($item['id_producto'] ?? 0);
            $cantidad   = (int) ($item['cantidad'] ?? 0);

            if ($idProducto <= 0 || $cantidad <= 0) {
                throw new ApiException(422, 'Cada item requiere id_producto y cantidad mayores que cero');
            }

            $porProducto[$idProducto] = ($porProducto[$idProducto] ?? 0) + $cantidad;
        }

        if ($porProducto === []) {
            throw new ApiException(422, 'El pedido debe incluir al menos un producto');
        }

        $bloquear = $pdo->prepare(
            'SELECT id_producto, nombre, precio, stock
               FROM productos
              WHERE id_producto = :id
                FOR UPDATE'
        );

        $insertarPedido  = $pdo->prepare(
            'INSERT INTO pedidos (id_cliente, estado, total) VALUES (:cliente, "pendiente", 0)'
        );
        $insertarDetalle = $pdo->prepare(
            'INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario)
             VALUES (:pedido, :producto, :cantidad, :precio)'
        );
        $descontarStock  = $pdo->prepare(
            'UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id'
        );

        $pdo->beginTransaction();

        try {
            $detalles = [];

            foreach ($porProducto as $idProducto => $cantidad) {
                $bloquear->execute([':id' => $idProducto]);
                $producto = $bloquear->fetch();

                if ($producto === false) {
                    throw new ApiException(404, "El producto {$idProducto} no existe");
                }

                if ((int) $producto['stock'] < $cantidad) {
                    throw new ApiException(
                        409,
                        sprintf(
                            'Stock insuficiente para «%s»: disponible %d, solicitado %d',
                            $producto['nombre'],
                            (int) $producto['stock'],
                            $cantidad
                        )
                    );
                }

                $detalles[] = [
                    'id_producto'     => (int) $producto['id_producto'],
                    'cantidad'        => $cantidad,
                    'precio_unitario' => (float) $producto['precio'],
                ];
            }

            $insertarPedido->execute([':cliente' => $idCliente]);
            $idPedido = (int) $pdo->lastInsertId();

            foreach ($detalles as $detalle) {
                $insertarDetalle->execute([
                    ':pedido'   => $idPedido,
                    ':producto' => $detalle['id_producto'],
                    ':cantidad' => $detalle['cantidad'],
                    ':precio'   => $detalle['precio_unitario'],
                ]);

                $descontarStock->execute([
                    ':cantidad' => $detalle['cantidad'],
                    ':id'       => $detalle['id_producto'],
                ]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        return self::buscar($idPedido);
    }

    public static function buscar(int $id): ?array
    {
        $pdo = Database::conexion();

        $stmt = $pdo->prepare(
            'SELECT p.id_pedido, p.id_cliente, p.fecha, p.estado, p.total, p.updated_at,
                    c.nombre AS cliente_nombre, c.correo AS cliente_correo,
                    c.telefono AS cliente_telefono
               FROM pedidos p
               JOIN clientes c ON c.id_cliente = p.id_cliente
              WHERE p.id_pedido = :id'
        );
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();

        if ($fila === false) {
            return null;
        }

        $stmtDetalle = $pdo->prepare(
            'SELECT d.id_producto, d.cantidad, d.precio_unitario, d.subtotal,
                    pr.nombre, pr.imagen
               FROM detalle_pedido d
               JOIN productos pr ON pr.id_producto = d.id_producto
              WHERE d.id_pedido = :id
              ORDER BY d.id_detalle'
        );
        $stmtDetalle->execute([':id' => $id]);

        return self::formatear($fila, $stmtDetalle->fetchAll());
    }

    public static function listar(int $pagina, int $limite, string $estado): array
    {
        $pdo = Database::conexion();

        $where  = '1 = 1';
        $params = [];

        if ($estado !== '') {
            $where .= ' AND p.estado = :estado';
            $params[':estado'] = $estado;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos p WHERE {$where}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT p.id_pedido
                  FROM pedidos p
                 WHERE {$where}
                 ORDER BY p.id_pedido DESC
                 LIMIT :limite OFFSET :offset";

        $stmt = $pdo->prepare($sql);

        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($pagina - 1) * $limite, PDO::PARAM_INT);
        $stmt->execute();

        $items = [];

        foreach ($stmt->fetchAll() as $fila) {
            $items[] = self::buscar((int) $fila['id_pedido']);
        }

        return [
            'items' => $items,
            'meta'  => [
                'pagina'  => $pagina,
                'limite'  => $limite,
                'total'   => $total,
                'paginas' => $limite > 0 ? (int) ceil($total / $limite) : 0,
            ],
        ];
    }

    public static function porCliente(int $idCliente): array
    {
        $pdo = Database::conexion();

        $stmt = $pdo->prepare(
            'SELECT id_pedido FROM pedidos
              WHERE id_cliente = :cliente
              ORDER BY id_pedido DESC'
        );
        $stmt->execute([':cliente' => $idCliente]);

        $pedidos = [];

        foreach ($stmt->fetchAll() as $fila) {
            $pedidos[] = self::buscar((int) $fila['id_pedido']);
        }

        return $pedidos;
    }

    public static function cambiarEstado(int $id, string $estado): array
    {
        $pdo = Database::conexion();
        $stmt = $pdo->prepare('UPDATE pedidos SET estado = :estado WHERE id_pedido = :id');
        $stmt->execute([':estado' => $estado, ':id' => $id]);

        return self::buscar($id);
    }

    private static function formatear(array $fila, array $items): array
    {
        return [
            'id_pedido' => (int) $fila['id_pedido'],
            'fecha'     => $fila['fecha'],
            'estado'    => $fila['estado'],
            'total'     => (float) $fila['total'],
            'cliente'   => [
                'id_cliente' => (int) $fila['id_cliente'],
                'nombre'     => $fila['cliente_nombre'],
                'correo'     => $fila['cliente_correo'],
                'telefono'   => $fila['cliente_telefono'],
            ],
            'items'     => array_map(static function (array $item): array {
                return [
                    'id_producto'     => (int) $item['id_producto'],
                    'nombre'          => $item['nombre'],
                    'imagen'          => $item['imagen'],
                    'cantidad'        => (int) $item['cantidad'],
                    'precio_unitario' => (float) $item['precio_unitario'],
                    'subtotal'        => (float) $item['subtotal'],
                ];
            }, $items),
            'updated_at' => $fila['updated_at'],
        ];
    }
}
