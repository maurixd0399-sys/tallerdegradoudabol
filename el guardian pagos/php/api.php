<?php
session_start();
header('Content-Type: application/json');
include '../conexion.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'clientes':
        echo json_encode(obtenerDatos($conexion, "SELECT * FROM clientes WHERE estado = 'activo' ORDER BY id DESC"));
        break;
        
    case 'servicios':
        echo json_encode(obtenerDatos($conexion, "SELECT * FROM servicios WHERE estado='activo'"));
        break;
        
    case 'pagos':
        echo json_encode(obtenerDatos($conexion, "
            SELECT p.*, c.nombre as cliente_nombre, s.nombre as servicio_nombre 
            FROM pagos p 
            JOIN clientes c ON p.cliente_id = c.id 
            JOIN servicios s ON p.servicio_id = s.id 
            WHERE p.estado IN ('pendiente','atrasado')
            ORDER BY p.fecha_pago ASC
        "));
        break;
        
    case 'recibos':
        echo json_encode(obtenerDatos($conexion, "
            SELECT r.*, c.nombre as cliente_nombre, p.monto as total_pagado
            FROM recibos r
            JOIN pagos p ON r.pago_id = p.id
            JOIN clientes c ON p.cliente_id = c.id
            ORDER BY r.fecha_emision DESC
        "));
        break;
    
    case 'cliente_save':
case 'cliente_con_pago':
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $servicio_id = intval($_POST['servicio_id'] ?? 0);

    if ($nombre === '' || $telefono === '' || $direccion === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Faltan datos obligatorios del cliente'
        ]);
        break;
    }

    $conexion->begin_transaction();

    try {
        // 1. Crear cliente
        $sql_cliente = "INSERT INTO clientes (nombre, email, telefono, direccion) VALUES (?, ?, ?, ?)";
        $stmt_cliente = ejecutarConsulta($conexion, $sql_cliente, [$nombre, $email, $telefono, $direccion]);

        if (!$stmt_cliente) {
            throw new Exception('No se pudo registrar el cliente');
        }

        $cliente_id = $conexion->insert_id;

        // 2. Si viene servicio_id, crear pago pendiente
        $pago_creado = false;
        $monto = 0;
        $fecha_pago = null;

        if ($servicio_id > 0) {
            $servicio = obtenerDatos($conexion, "SELECT precio FROM servicios WHERE id = ? AND estado = 'activo'", [$servicio_id]);

            if (!$servicio || count($servicio) === 0) {
                throw new Exception('Servicio no encontrado o inactivo');
            }

            $monto = $servicio[0]['precio'];
            $fecha_pago = $_POST['fecha_pago'] ?? date('Y-m-d', strtotime('+15 days'));
            $metodo_pago = $_POST['metodo_pago'] ?? 'por definir';

            $sql_pago = "
                INSERT INTO pagos 
                (cliente_id, servicio_id, monto, fecha_pago, metodo_pago, estado)
                VALUES (?, ?, ?, ?, ?, 'pendiente')
            ";

            $stmt_pago = ejecutarConsulta($conexion, $sql_pago, [
                $cliente_id,
                $servicio_id,
                $monto,
                $fecha_pago,
                $metodo_pago
            ]);

            if (!$stmt_pago) {
                throw new Exception('No se pudo crear el pago');
            }

            $pago_creado = true;
        }

        $conexion->commit();

        echo json_encode([
            'success' => true,
            'cliente_id' => $cliente_id,
            'pago_creado' => $pago_creado,
            'monto' => $monto,
            'fecha_pago' => $fecha_pago,
            'message' => $pago_creado
                ? 'Cliente registrado y pago pendiente creado'
                : 'Cliente registrado correctamente'
        ]);
    } catch (Exception $e) {
        $conexion->rollback();

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    break;
    case 'cliente_get':
        $id = $_GET['id'] ?? 0;
        $cliente = obtenerDatos($conexion, "SELECT * FROM clientes WHERE id = ?", [$id]);
        echo json_encode($cliente[0] ?? []);
        break;
        
    case 'cliente_update':
        $id = $_POST['id'] ?? 0;
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        
        $sql = "UPDATE clientes SET nombre=?, email=?, telefono=?, direccion=? WHERE id=?";
        $stmt = ejecutarConsulta($conexion, $sql, [$nombre, $email, $telefono, $direccion, $id]);
        echo json_encode(['success' => $stmt ? true : false]);
        break;
        
    case 'cliente_delete':
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de cliente inválido'
        ]);
        break;
    }

    $conexion->begin_transaction();

    try {
        ejecutarConsulta($conexion, "
            DELETE r
            FROM recibos r
            INNER JOIN pagos p ON r.pago_id = p.id
            WHERE p.cliente_id = ?
        ", [$id]);

        ejecutarConsulta($conexion, "
            DELETE FROM pagos
            WHERE cliente_id = ?
        ", [$id]);

        $stmt = ejecutarConsulta($conexion, "
            DELETE FROM clientes
            WHERE id = ?
        ", [$id]);

        $conexion->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Cliente eliminado correctamente'
        ]);
    } catch (Exception $e) {
        $conexion->rollback();

        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar: ' . $e->getMessage()
        ]);
    }

    break;
        
    case 'pago_confirmar':
        $input = json_decode(file_get_contents('php://input'), true);
        $pago_id = $input['pago_id'] ?? 0;
        
        $sql = "UPDATE pagos SET estado = 'pagado' WHERE id = ?";
        ejecutarConsulta($conexion, $sql, [$pago_id]);
        
        $numero_recibo = 'REC-' . date('Ymd') . '-' . str_pad($pago_id, 4, '0', STR_PAD_LEFT);
        $sql_recibo = "INSERT INTO recibos (pago_id, numero_recibo, total_pagado) 
                       SELECT id, ?, monto FROM pagos WHERE id = ?";
        ejecutarConsulta($conexion, $sql_recibo, [$numero_recibo, $pago_id]);
        
        echo json_encode(['success' => true, 'recibo_numero' => $numero_recibo]);
        break;
        
    case 'pago_nuevo':
        $cliente_id = $_POST['cliente_id'] ?? 0;
        $servicio_id = $_POST['servicio_id'] ?? 0;
        $fecha_pago = $_POST['fecha_pago'] ?? '';
        $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
        
        $servicio = obtenerDatos($conexion, "SELECT precio FROM servicios WHERE id = ?", [$servicio_id]);
        $monto = $servicio[0]['precio'] ?? 0;
        
        $sql = "INSERT INTO pagos (cliente_id, servicio_id, monto, fecha_pago, metodo_pago, estado) 
                VALUES (?, ?, ?, ?, ?, 'pendiente')";
        $stmt = ejecutarConsulta($conexion, $sql, [$cliente_id, $servicio_id, $monto, $fecha_pago, $metodo_pago]);
        
        echo json_encode(['success' => $stmt ? true : false, 'monto' => $monto]);
        break;
        // AGREGAR al switch (antes del break final):



    
}

$conexion->close();
?>
