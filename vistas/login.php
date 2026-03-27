<?php
session_start();
require_once '../configuracion/config.php';

// Redirigir si ya ha iniciado sesión
if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}

$mensajeError = '';
if (isset($_GET['error']) && $_GET['error'] === 'expirado') {
    $mensajeError = 'Tu sesión ha expirado. Inicia sesión de nuevo.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreUsuario = trim($_POST['nombre_usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (!empty($nombreUsuario) && !empty($contrasena)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
        $stmt->execute([$nombreUsuario]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
            $_SESSION['rol_usuario'] = $usuario['rol'];
            header('Location: dashboard.php');
            exit;
        } else {
            $mensajeError = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $mensajeError = 'Por favor, completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - DebiHaby</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../public/css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../public/css/login-register.css?v=<?php echo time(); ?>">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card-glass">
            <div class="auth-visual-panel">
                <div class="visual-content">
                    <div class="logo-wrapper">
                        <img src="../public/assets/logo.png" alt="DebiHaby Logo" class="floating-logo">
                    </div>
                    <h1 class="premium-title">DebiHaby</h1>
                    <p class="premium-subtitle">Domina la contabilidad con una experiencia gamificada única.</p>
                    
                    <div class="feature-pills">
                        <div class="feature-pill">
                            <i class="fas fa-rocket"></i>
                            <span>Aprendizaje Rápido</span>
                        </div>
                        <div class="feature-pill">
                            <i class="fas fa-gamepad"></i>
                            <span>Gamificación</span>
                        </div>
                        <div class="feature-pill">
                            <i class="fas fa-shield-halved"></i>
                            <span>Certificación</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-form-panel">
                <div class="form-inner">
                    <div class="form-header">
                        <h2>Bienvenido</h2>
                        <p>Ingresa tus credenciales para continuar</p>
                    </div>

                    <?php if ($mensajeError): ?>
                        <div class="auth-alert-premium animate-shake">
                            <i class="fas fa-circle-exclamation"></i>
                            <span><?php echo htmlspecialchars($mensajeError); ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST" class="premium-form">
                        <div class="input-group-premium">
                            <label for="nombre_usuario">Usuario</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" id="nombre_usuario" name="nombre_usuario" placeholder="Tu nombre de usuario" required autocomplete="username" value="<?php echo htmlspecialchars($nombreUsuario ?? ''); ?>">
                            </div>
                        </div>

                        <div class="input-group-premium">
                            <label for="contrasena">Contraseña</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required autocomplete="current-password">
                                <button type="button" class="toggle-password-v2" aria-label="Toggle password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                        </div>

                        <button type="submit" class="btn-premium-submit">
                            <span>Iniciar Sesión</span>
                        </button>
                    </form>

                    <div class="form-footer">
                        <p>¿No tienes una cuenta? <a href="register.php">Regístrate gratis</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-password-v2').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.previousElementSibling;
                const icon = btn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'fas fa-eye-slash';
                } else {
                    input.type = 'password';
                    icon.className = 'fas fa-eye';
                }
            });
        });
    </script>
</body>
</html>
