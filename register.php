<?php
session_start();
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
    $academic_level = $_POST['academic_level'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password)) {
        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'El usuario o email ya están registrados.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, age, academic_level) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashedPassword, $full_name, $age, $academic_level])) {
                $userId = $pdo->lastInsertId();
                // Initialize stats
                $stmt = $pdo->prepare("INSERT INTO user_stats (user_id) VALUES (?)");
                $stmt->execute([$userId]);
                
                // Initialize first lesson
                $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, lesson_id, status) VALUES (?, 1, 'available')");
                $stmt->execute([$userId]);

                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Error al registrar al usuario.';
            }
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
    <title>Registro - DebiHaby</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/login-register.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="assets/logo.png" alt="DebiHaby Logo">
                <h2>Crea tu cuenta</h2>
                <p>Únete a la aventura contable</p>
            </div>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-msg" style="background: #e8f5e9; color: #2e7d32; padding: 0.8rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="full_name">Nombre Completo</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Ej. Juan Pérez" required>
                </div>
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" placeholder="Tu nombre artístico" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <div class="form-group" style="flex: 1;">
                        <label for="age">Edad</label>
                        <input type="number" id="age" name="age" placeholder="20" min="10">
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label for="academic_level">Nivel Académico</label>
                        <select id="academic_level" name="academic_level" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                            <option value="Bachillerato">Bachillerato</option>
                            <option value="Licenciatura">Licenciatura</option>
                            <option value="Profesional">Profesional</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="login-btn">Empezar a Aprender</button>
            </form>

            <a href="login.php" class="back-link">¿Ya tienes cuenta? Inicia Sesión</a>
        </div>
    </div>
</body>
</html>
