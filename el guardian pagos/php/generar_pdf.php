<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../conexion.php';
$id_recibo = $_GET['id'] ?? 0;

$sql = "SELECT r.*, c.nombre as cliente_nombre, c.direccion, c.telefono, c.nit_o_cc,
             p.monto, p.fecha_pago, p.metodo_pago, s.nombre as servicio_nombre
        FROM recibos r
        JOIN pagos p ON r.pago_id = p.id
        JOIN clientes c ON p.cliente_id = c.id
        JOIN servicios s ON p.servicio_id = s.id
        WHERE r.id = ?";
        
$resultado = ejecutarConsulta($conexion, $sql, [$id_recibo]);
$recibo = $resultado->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recibo #<?php echo $recibo['numero_recibo']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; max-width: 800px; margin: auto; }
        .header { text-align: center; border-bottom: 3px solid #667eea; padding-bottom: 20px; }
        .logo { font-size: 32px; color: #1a1a3e; margin-bottom: 10px; }
        .recibo-numero { font-size: 24px; color: #333; margin: 20px 0; }
        table { width: 100%; margin: 20px 0; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        .total { font-size: 32px; color: #28a745; text-align: center; padding: 30px; border: 3px double #667eea; margin: 30px 0; }
        @media print { body { margin: 0; } }
        .print-btn { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">🛡️ SEGURIDAD PRIVADA "El Guardian"</div>
        <div style="color: #666;">Sistema de Gestión de Pagos</div>
    </div>
    
    <h1 class="recibo-numero">RECIBO DE PAGO<br><small>#<?php echo $recibo['numero_recibo']; ?></small></h1>
    
    <table>
        <tr><th>Fecha Emisión</th><td><?php echo date('d/m/Y H:i', strtotime($recibo['fecha_emision'])); ?></td></tr>
        <tr><th>Cliente</th><td><?php echo $recibo['cliente_nombre']; ?></td></tr>
        <tr><th>NIT</th><td><?php echo $recibo['nit_o_cc'] ?: 'N/A'; ?></td></tr>
        <tr><th>Dirección</th><td><?php echo $recibo['direccion']; ?></td></tr>
        <tr><th>Teléfono</th><td><?php echo $recibo['telefono']; ?></td></tr>
        <tr><th>Servicio</th><td><?php echo $recibo['servicio_nombre']; ?></td></tr>
        <tr><th>Fecha Pago</th><td><?php echo date('d/m/Y', strtotime($recibo['fecha_pago'])); ?></td></tr>
        <tr><th>Método Pago</th><td><?php echo ucfirst($recibo['metodo_pago']); ?></td></tr>
    </table>
    
    <div class="total">
        TOTAL PAGADO: Bs<?php echo number_format($recibo['monto'], 0, ',', '.'); ?>
    </div>
    
    <div style="text-align: center; margin-top: 40px; color: #666;">
        <p>Recibo válido para fines contables</p>
        <button class="print-btn" onclick="window.print()">🖨️ Imprimir / Descargar</button>
    </div>
</body>
</html>