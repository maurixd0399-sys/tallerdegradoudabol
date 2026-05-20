<?php
include 'conexion.php';
echo "<h2>🔍 DEBUG PAGOS</h2>";

// 1. ¿Tabla pagos tiene datos?
$pagos = $conexion->query("SELECT * FROM pagos WHERE estado IN ('pendiente','atrasado')");
echo "<p><strong>Pagos encontrados:</strong> " . $pagos->num_rows . "</p>";

// 2. Si no hay, crear de prueba
if ($pagos->num_rows == 0) {
    echo "<p>❌ Creando pagos de prueba...</p>";
    $sql = "INSERT INTO pagos (cliente_id, servicio_id, monto, fecha_pago, estado) VALUES 
            (1, 1, 2500000, '2024-11-15', 'pendiente'),
            (2, 2, 1800000, '2024-11-20', 'atrasado')";
    if ($conexion->query($sql)) {
        echo "✅ 2 pagos creados";
    }
}

// 3. Probar API
echo "<h3>API Pagos:</h3>";
$api_result = obtenerDatos($conexion, "
    SELECT p.*, c.nombre as cliente_nombre, s.nombre as servicio_nombre 
    FROM pagos p JOIN clientes c ON p.cliente_id = c.id 
    JOIN servicios s ON p.servicio_id = s.id 
    WHERE p.estado IN ('pendiente','atrasado')
");
echo "<pre>" . json_encode($api_result, JSON_PRETTY_PRINT) . "</pre>";
?>