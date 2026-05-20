<?php
/* ============================================
   PHP - Registro Cliente Invitado
   Seguridad Privada "El Guardian"
   ============================================ */

header('Content-Type: application/json');

// Validar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit();
}

// Incluir conexión
include '../conexion.php';

// Validar campos requeridos
$campos_requeridos = ['nombre', 'telefono', 'direccion', 'servicio_id'];
foreach ($campos_requeridos as $campo) {
    if (empty($_POST[$campo])) {
        echo json_encode([
            'success' => false,
            'message' => 'Faltan datos requeridos: ' . $campo
        ]);
        exit();
    }
}

// Sanitizar datos
$nombre = trim($conexion->real_escape_string($_POST['nombre']));
$telefono = trim($conexion->real_escape_string($_POST['telefono']));
$email = isset($_POST['email']) ? trim($conexion->real_escape_string($_POST['email'])) : '';
$direccion = trim($conexion->real_escape_string($_POST['direccion']));
$servicio_id = (int)$_POST['servicio_id'];
$notas = isset($_POST['notas']) ? trim($conexion->real_escape_string($_POST['notas'])) : '';

// Verificar que el servicio existe y está activo
$servicios = obtenerDatos($conexion, "SELECT * FROM servicios WHERE id = ? AND estado = 'activo'", [$servicio_id]);

if (empty($servicios)) {
    echo json_encode([
        'success' => false,
        'message' => 'El servicio seleccionado no está disponible'
    ]);
    exit();
}

$servicio = $servicios[0];
$precio_servicio = $servicio['precio'];

// Verificar si el cliente ya existe (por teléfono)
$clientes = obtenerDatos($conexion, "SELECT * FROM clientes WHERE telefono = ?", [$telefono]);

if (!empty($clientes)) {
    // Usar cliente existente
    $cliente = $clientes[0];
    $cliente_id = $cliente['id'];
    
    // Actualizar datos
    $sql_update = "UPDATE clientes SET nombre = ?, email = ?, direccion = ? WHERE id = ?";
    ejecutarConsulta($conexion, $sql_update, [$nombre, $email, $direccion, $cliente_id]);
} else {
    // Insertar nuevo cliente
    $sql_cliente = "INSERT INTO clientes (nombre, email, telefono, direccion, fecha_registro) VALUES (?, ?, ?, ?, NOW())";
    ejecutarConsulta($conexion, $sql_cliente, [$nombre, $email, $telefono, $direccion]);
    
    $cliente_id = $conexion->insert_id;
}

// Crear registro de pago/solicitud
$sql_pago = "INSERT INTO pagos (cliente_id, servicio_id, monto, estado, fecha_solicitud, notas) VALUES (?, ?, ?, 'pendiente', NOW(), ?)";
ejecutarConsulta($conexion, $sql_pago, [$cliente_id, $servicio_id, $precio_servicio, $notas]);

$pago_id = $conexion->insert_id;

// Generar número de solicitud
$solicitud_id = date('Y') . str_pad($pago_id, 5, '0', STR_PAD_LEFT);

// Responder éxito
echo json_encode([
    'success' => true,
    'message' => 'Registro exitoso',
    'solicitud_id' => $solicitud_id,
    'cliente_id' => $cliente_id,
    'pago_id' => $pago_id,
    'servicio' => $servicio['nombre'],
    'monto' => $precio_servicio
]);

// Cerrar conexión
$conexion->close();