<?php

$host = "localhost";
$usuario = "root";
$password = "";
$basedatos = "tecno_market";

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$basedatos;charset=utf8mb4",
        $usuario,
        $password
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

?>
