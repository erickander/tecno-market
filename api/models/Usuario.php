<?php
/**
 * TECNO-MARKET | Modelo de usuarios
 */

class Usuario
{
    public static function buscarPorNombre(string $nombreUsuario): ?array
    {
        $pdo = Database::conexion();
        $stmt = $pdo->prepare(
            'SELECT id_usuario, nombre_usuario, contrasena, rol, id_cliente, activo, ultimo_acceso
               FROM usuarios
              WHERE nombre_usuario = :nombre'
        );
        $stmt->execute([':nombre' => $nombreUsuario]);
        $fila = $stmt->fetch();

        return $fila ?: null;
    }

    public static function buscar(int $id): ?array
    {
        $pdo = Database::conexion();
        $stmt = $pdo->prepare(
            'SELECT id_usuario, nombre_usuario, rol, id_cliente, activo, ultimo_acceso, created_at
               FROM usuarios
              WHERE id_usuario = :id'
        );
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();

        return $fila ? self::formatear($fila) : null;
    }

    public static function actualizarUltimoAcceso(int $id): void
    {
        $pdo = Database::conexion();
        $stmt = $pdo->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function formatear(array $fila): array
    {
        return [
            'id_usuario'     => (int) $fila['id_usuario'],
            'nombre_usuario' => $fila['nombre_usuario'],
            'rol'            => $fila['rol'],
            'id_cliente'     => $fila['id_cliente'] !== null ? (int) $fila['id_cliente'] : null,
            'activo'         => (bool) $fila['activo'],
            'ultimo_acceso'  => $fila['ultimo_acceso'],
            'created_at'     => $fila['created_at'] ?? null,
        ];
    }
}
