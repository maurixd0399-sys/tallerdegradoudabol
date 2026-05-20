<?php
// Si ya está logueado como admin, redirigir al dashboard
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

include 'conexion.php';
// Obtener servicios activos
$servicios = obtenerDatos($conexion, "SELECT * FROM servicios WHERE estado='activo'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios - Seguridad Privada "El Guardian"</title>
    <link rel="stylesheet" href="css/cliente.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-shield-alt"></i>
                <span>El Guardian</span>
            </div>
            <nav class="sidebar-nav">
                <a href="#servicios" class="nav-item active">
                    <i class="fas fa-shield-alt"></i>
                    <span>Servicios</span>
                </a>
                <a href="https://wa.me/59164759285" target="_blank" class="nav-item">
    <i class="fab fa-whatsapp"></i>
    <span>Contacto</span>
</a>
            </nav>
            <div class="sidebar-footer">
                <a href="index.php" class="login-link">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Área Admin</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-title">
                    <h1>Nuestros Servicios</h1>
                    <p>Protección y tranquilidad para tu hogar y negocio</p>
                </div>
            </header>

            <!-- Servicios Grid -->
            <section class="servicios-section">
                <h2><i class="fas fa-briefcase"></i> Servicios Disponibles</h2>
                <div class="servicios-grid">
                    <?php foreach($servicios as $servicio): ?>
                    <div class="servicio-card">
                        <div class="card-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3><?php echo $servicio['nombre']; ?></h3>
                        <p class="card-descripcion"><?php echo $servicio['descripcion']; ?></p>
                        <div class="card-precio">
                            <span class="precio">Bs<?php echo number_format($servicio['precio'], 2); ?></span>
                            <span class="periodo">/<?php echo $servicio['duracion_meses']; ?></span>
                        </div>
                        <button class="btn-seleccionar" onclick="seleccionarServicio(<?php echo $servicio['id']; ?>, '<?php echo addslashes($servicio['nombre']); ?>', <?php echo $servicio['precio']; ?>)">
                            <i class="fas fa-check"></i> Seleccionar
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
        
    </div>

    <!-- Modal Registro -->
  <!-- Modal Registro -->
<div id="modal-registro" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Registrarse</h3>
            <span class="close" onclick="cerrarModal()">&times;</span>
        </div>

        <form id="form-registro">
            <input type="hidden" name="servicio_id" id="servicio_id">

            <div class="servicio-seleccionado">
                <i class="fas fa-shield-alt"></i>
                <span id="servicio-nombre">Servicio seleccionado</span>
                <span id="servicio-precio" class="precio-badge">Bs 0.00</span>
            </div>

            <div class="form-group">
                <label>Nombre Completo *</label>
                <input type="text" name="nombre" placeholder="Juan Pérez" required>
            </div>

            <div class="form-group">
                <label>Teléfono *</label>
                <input type="tel" name="telefono" placeholder="(555) 123-4567" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="correo@ejemplo.com">
            </div>

            <div class="form-group">
                <label>Dirección *</label>
                <input type="text" name="direccion" placeholder="Calle, número, colonia" required>
            </div>

            <div class="form-group">
                <label>Notas adicionales</label>
                <textarea name="notas" placeholder="Detalles sobre el servicio que necesitas..."></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Solicitar Servicio
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Éxito -->
<div id="modal-exito" class="modal">
    <div class="modal-content modal-exito-content">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>

        <h2>¡Registro Exitoso!</h2>
        <p class="success-message">Tu solicitud ha sido recibida</p>
        <p class="success-submessage">Por favor espera, nos contactaremos contigo pronto</p>

        <div class="solicitud-info">
            <div class="solicitud-label">Número de Solicitud</div>
            <div class="solicitud-numero" id="solicitud-numero">#00000</div>
        </div>

        <div class="servicio-contratado">
            <i class="fas fa-shield-alt"></i>
            <span id="servicio-contratado-nombre">Servicio</span>
        </div>

        <p class="gracias-message">¡Gracias por confiar en nosotros!</p>

        <button type="button" class="btn-primary" onclick="cerrarModalExito()">
            <i class="fas fa-home"></i> Volver a Inicio
        </button>
    </div>
</div>

    <script src="js/cliente.js"></script>
</body>
</html>