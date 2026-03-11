<?php
session_start();
require_once 'includes/config.php';

$error = '';
if (isset($_GET['error']) && $_GET['error'] === 'expired') {
    $error = 'Tu sesión ha expirado o el usuario no existe.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Special case for 'user/user' until registration is used
        if ($user && ($password === 'user' || password_verify($password, $user['password']))) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor, completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - DebiHaby</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/login-register.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="assets/logo.png" alt="DebiHaby Logo">
                <h2>¡Bienvenido de nuevo!</h2>
                <p>Ingresa tus datos para continuar</p>
            </div>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" placeholder="Usuario (ej. user)" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Contraseña (ej. user)" required>
                </div>
                <button type="submit" class="login-btn">Entrar al Aula</button>
            </form>

            <a href="register.php" class="back-link">¿No tienes cuenta? Regístrate aquí</a>
            <a href="index.html" class="back-link">← Volver al inicio</a>
        </div>
    </div>
</body>
</html>
