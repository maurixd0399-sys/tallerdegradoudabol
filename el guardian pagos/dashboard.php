<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

include 'conexion.php';

// Obtener stats
$stats = [
    'clientes' => count(obtenerDatos($conexion, "SELECT id FROM clientes")),
    'servicios' => count(obtenerDatos($conexion, "SELECT id FROM servicios WHERE estado='activo'")),
    'pendientes' => count(obtenerDatos($conexion, "SELECT id FROM pagos WHERE estado IN ('pendiente','atrasado')")),
    'recibos' => count(obtenerDatos($conexion, "SELECT id FROM recibos"))
];

$nombre_usuario = $_SESSION['usuario_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>principal - Seguridad Privada "El Guardian"</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar 3D -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-shield-alt"></i>
                <h2>SEGURIDAD PRIVADA "EL GUARDIAN"</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="#clientes" class="nav-item active" data-section="clientes">
                    <i class="fas fa-users"></i>
                    <span>Clientes</span>
                </a>
                <a href="#servicios" class="nav-item" data-section="servicios">
                    <i class="fas fa-cogs"></i>
                    <span>Servicios</span>
                </a>
                <a href="#pagos" class="nav-item" data-section="pagos">
                    <i class="fas fa-credit-card"></i>
                    <span>Pagos</span>
                </a>
                <a href="#recibos" class="nav-item" data-section="recibos">
                    <i class="fas fa-file-invoice"></i>
                    <span>Recibos</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="php/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span><?php echo $nombre_usuario; ?></span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <h1 id="page-title">Incio</h1>
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <div>
                            <span class="stat-number"><?php echo $stats['clientes']; ?></span>
                            <span class="stat-label">Clientes</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-cogs"></i>
                        <div>
                            <span class="stat-number"><?php echo $stats['servicios']; ?></span>
                            <span class="stat-label">Servicios</span>
                        </div>
                    </div>
                    <div class="stat-card warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <span class="stat-number"><?php echo $stats['pendientes']; ?></span>
                            <span class="stat-label">Pendientes</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-file-invoice"></i>
                        <div>
                            <span class="stat-number"><?php echo $stats['recibos']; ?></span>
                            <span class="stat-label">Recibos</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Contenido dinámico por secciones -->
            <section id="clientes" class="content-section active">
                <h2>Gestión de Clientes</h2>
                <div class="table-container">
                    <div class="table-header">
                        <button class="btn-add" onclick="mostrarModal('cliente')">
                            <i class="fas fa-plus"></i> Nuevo Cliente
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Dirección</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="clientes-table">
                                <!-- Cargado por JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="servicios" class="content-section">
                <h2>Servicios Disponibles</h2>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Duración</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="servicios-table">
                                <!-- Cargado por JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="pagos" class="content-section">
                <h2>Pagos Pendientes</h2>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Servicio</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="pagos-table">
                                <!-- Cargado por JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="recibos" class="content-section">
                <h2>Recibos Generados</h2>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nº Recibo</th>
                                    <th>Pago</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="recibos-table">
                                <!-- Cargado por JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

   <!-- Modal Nuevo Cliente + Pago AUTOMÁTICO -->
<div id="modal-cliente" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Nuevo Cliente + Servicio</h3>
            <span class="close" onclick="cerrarModal('cliente')">×</span>
        </div>
        <form id="form-cliente">
            <h4 style="margin-bottom:1rem;color:#667eea;">📋 Datos Cliente</h4>
            <input type="text" name="nombre" placeholder="Nombre completo *" required>
            <input type="email" name="email" placeholder="Email">
            <input type="tel" name="telefono" placeholder="Teléfono *" required>
            <input type="text" name="direccion" placeholder="Dirección *" required>
            
            <h4 style="margin:2rem 0 1rem 0;color:#667eea;">🛡️ Servicio Contratado</h4>
            <select name="servicio_id" id="servicio-select" required>
                <option value="">Seleccionar servicio...</option>
            </select>
            <div id="precio-servicio" style="color:#64c8ff;font-weight:600;margin:0.5rem 0;"></div>
            
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal('cliente')">Cancelar</button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Crear Cliente + Pago
                </button>
            </div>
        </form>
    </div>
</div>
<div class="modal" id="modal-cliente-edit">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar cliente</h3>
            <span class="close" onclick="cerrarModal('cliente-edit')">&times;</span>
        </div>

        <form id="form-cliente-edit">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="email" name="email" placeholder="Email">
            <input type="text" name="telefono" placeholder="Teléfono" required>
            <input type="text" name="direccion" placeholder="Dirección" required>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal('cliente-edit')">Cancelar</button>
                <button type="submit" class="btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
</div>

    <script src="js/dashboard.js"></script>
</body>
</html>