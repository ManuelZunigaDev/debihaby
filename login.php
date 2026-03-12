<?php
session_start();
require_once 'includes/config.php';

$errorMessage = '';
if (isset($_GET['error']) && $_GET['error'] === 'expired') {
    $errorMessage = 'Tu sesión ha expirado. Inicia sesión de nuevo.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errorMessage = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $errorMessage = 'Por favor, completa todos los campos.';
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
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/login-register.css">
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <div class="auth-panel auth-panel--left">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="auth-brand">
                <img src="assets/logo.png" alt="DebiHaby" class="auth-logo">
                <h1 class="auth-brand-title">DebiHaby</h1>
                <p class="auth-brand-subtitle">Tu aula contable interactiva. Aprende, practica y domina la contabilidad a tu propio ritmo.</p>
            </div>
            <div class="auth-features">
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="fas fa-trophy"></i></span>
                    <span>Sistema de logros y niveles</span>
                </div>
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="fas fa-gamepad"></i></span>
                    <span>Lecciones interactivas gamificadas</span>
                </div>
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="fas fa-chart-line"></i></span>
                    <span>Seguimiento de tu progreso en tiempo real</span>
                </div>
            </div>
        </div>

        <div class="auth-panel auth-panel--right">
            <div class="auth-form-wrapper">
                <div class="auth-form-header">
                    <h2>¡Bienvenido de nuevo!</h2>
                    <p>Inicia sesión para continuar tu aprendizaje</p>
                </div>

                <?php if ($errorMessage): ?>
                    <div class="auth-alert auth-alert--error">
                        <i class="fas fa-circle-exclamation"></i>
                        <span><?php echo htmlspecialchars($errorMessage); ?></span>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="auth-form" novalidate>
                    <div class="auth-field">
                        <span class="auth-field-icon"><i class="fas fa-user"></i></span>
                        <input type="text" id="username" name="username" placeholder="Usuario" required autocomplete="username" value="<?php echo htmlspecialchars($username ?? ''); ?>">
                        <label for="username">Usuario</label>
                    </div>

                    <div class="auth-field">
                        <span class="auth-field-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" placeholder="Contraseña" required autocomplete="current-password">
                        <label for="password">Contraseña</label>
                        <button type="button" class="toggle-password" aria-label="Mostrar contraseña" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <button type="submit" class="auth-submit-btn">
                        <span>Entrar al Aula</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <div class="auth-links">
                    <a href="register.php" class="auth-link">¿No tienes cuenta? <strong>Regístrate aquí</strong></a>
                    <a href="index.php" class="auth-link auth-link--secondary"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-password').forEach(btn => {
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
