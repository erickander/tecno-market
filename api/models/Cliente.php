<?php
/**
 * TECNO-MARKET | Modelo de clientes
 */

class Cliente
{
    public static function crear(array $datos): array
    {
        $pdo = Database::conexion();

        $stmt = $pdo->prepare(
            'INSERT INTO clientes (nombre, correo, telefono, direccion)
             VALUES (:nombre, :correo, :telefono, :direccion)'
        );

        try {
            $stmt->execute([
                ':nombre'    => $datos['nombre'],
                ':correo'    => strtolower($datos['correo']),
                ':telefono'  => $datos['telefono'],
                ':direccion' => $datos['direccion'] ?? null,
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new ApiException(409, 'Ya existe un cliente registrado con ese correo');
            }
            throw $e;
        }

        return self::buscar((int) $pdo->lastInsertId());
    }

    public static function buscar(int $id): ?array
    {
        $pdo = Database::conexion();
        $stmt = $pdo->prepare(
            'SELECT id_cliente, nombre, correo, telefono, direccion, created_at
               FROM clientes
              WHERE id_cliente = :id'
        );
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();

        return $fila ? self::formatear($fila) : null;
    }

    public static function buscarPorCorreo(string $correo): ?array
    {
        $pdo = Database::conexion();
        $stmt = $pdo->prepare(
            'SELECT id_cliente, nombre, correo, telefono, direccion, created_at
               FROM clientes
              WHERE correo = :correo'
        );
        $stmt->execute([':correo' => strtolower($correo)]);
        $fila = $stmt->fetch();

        return $fila ? self::formatear($fila) : null;
    }

    /**
     * Devuelve el cliente existente o lo crea (usado en el checkout).
     */
    public static function obtenerOCrear(array $datos): array
    {
        $existente = self::buscarPorCorreo($datos['correo']);

        if ($existente !== null) {
            return $existente;
        }

        return self::crear($datos);
    }

    private static function formatear(array $fila): array
    {
        return [
            'id_cliente' => (int) $fila['id_cliente'],
            'nombre'     => $fila['nombre'],
            'correo'     => $fila['correo'],
            'telefono'   => $fila['telefono'],
            'direccion'  => $fila['direccion'],
            'created_at' => $fila['created_at'],
        ];
    }
}
