<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

include 'conexion.php';

$error = '';
if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Login SIMPLE (sin hash por ahora)
    $sql = "SELECT id, nombre, rol FROM usuarios WHERE email = ? AND password = ?";
    $stmt = ejecutarConsulta($conexion, $sql, [$email, $password]);
    $usuario = $stmt->get_result()->fetch_assoc();
     if (isset($_POST['btn_invitado'])) {
        // Crear sesión de invitado
        $_SESSION['usuario_id'] = 0;
        $_SESSION['usuario_nombre'] = 'Invitado';
        $_SESSION['usuario_rol'] = 'invitado';
        header("Location: dashboard_invitado.php");
        exit();
    }
    
    if ($usuario) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        header("Location: dashboard.php"); // ← Cambiará después
        exit();
    } else {
        $error = "¡Email o contraseña incorrectos!";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ingreso - Seguridad Privada "El GUARDIAN"</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Fondo animado -->
        <div class="background-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>

        <!-- Card principal con efecto 3D -->
        <div class="login-card">
            <div class="card-inner">
                <div class="logo">
                    <i class="fas fa-shield-alt"></i>
                    <h1>EL GUARDIAN</h1>
                    <p>Sistema de Pagos</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email corporativo" required autocomplete="off">
                        <span class="input-line"></span>
                    </div>
                    
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Contraseña" required autocomplete="off">
                        <span class="input-line"></span>
                    </div>

                <button type="submit" class="btn-login">
    <span>INICIAR SESIÓN</span>
    <i class="fas fa-arrow-right"></i>
</button>
</form>

<div class="separador">
    <span>O</span>
</div>

<a href="cliente_invitado.php" class="btn-secondary">
    <i class="fas fa-search"></i>
    Ver nuestros Servicios
</a>

<p class="invited-text">
    ¿Necesitas protección? Revisa nuestros servicios sin registrarte
</p>
            
    

    <script src="js/script.js"></script>
</body>
</html>