<?php
$host = 'localhost';
$usuario = 'root';
$password = '';
$bd = 'sistema_pagos_seguridad';
$puerto = 3306;

$conexion = new mysqli($host, $usuario, $password, $bd, $puerto);

if ($conexion->connect_error) {
    die("Error: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

// Funciones seguras
function ejecutarConsulta($conexion, $sql, $params = []) {
    $stmt = $conexion->prepare($sql);
    if ($params) {
        $tipos = str_repeat('s', count($params));
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

function obtenerDatos($conexion, $sql, $params = []) {
    $stmt = ejecutarConsulta($conexion, $sql, $params);
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>