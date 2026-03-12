<?php
session_start();
require_once 'includes/config.php';

$errorMessage = '';
$isSuccess    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password) && !empty($fullName)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errorMessage = 'El usuario o email ya están registrados.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'student')");
            if ($stmt->execute([$username, $email, $hashedPassword, $fullName])) {
                $userId = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO user_stats (user_id, points, level) VALUES (?, 0, 1)");
                $stmt->execute([$userId]);

                // Dynamic lesson ID assignment with auto-fix
                try {
                    $stmt = $pdo->query("SELECT id FROM lessons ORDER BY order_index ASC, id ASC LIMIT 1");
                    $firstLessonId = $stmt->fetchColumn();
                    
                    if (!$firstLessonId) {
                        // Table exists but empty? Run migration
                        $sql = file_get_contents('db/database.sql');
                        if ($sql) $pdo->exec($sql);
                        $firstLessonId = $pdo->query("SELECT id FROM lessons ORDER BY order_index ASC, id ASC LIMIT 1")->fetchColumn();
                    }

                    if ($firstLessonId) {
                        $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, lesson_id, status) VALUES (?, ?, 'available')");
                        $stmt->execute([$userId, $firstLessonId]);
                    }
                } catch (PDOException $e) {
                    // If error is missing table, run migration
                    if (strpos($e->getMessage(), "lessons' doesn't exist") !== false || strpos($e->getMessage(), "courses' doesn't exist") !== false) {
                        $sql = file_get_contents('db/database.sql');
                        if ($sql) {
                            $pdo->exec($sql);
                            $firstLessonId = $pdo->query("SELECT id FROM lessons ORDER BY order_index ASC, id ASC LIMIT 1")->fetchColumn();
                            if ($firstLessonId) {
                                $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, lesson_id, status) VALUES (?, ?, 'available')");
                                $stmt->execute([$userId, $firstLessonId]);
                            }
                        }
                    } else {
                        throw $e;
                    }
                }

                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                header('Location: dashboard.php');
                exit;
            } else {
                $errorMessage = 'Error al registrar al usuario.';
            }
        }
    } else {
        $errorMessage = 'Por favor, completa todos los campos obligatorios.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - DebiHaby</title>
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
                <p class="auth-brand-subtitle">Únete a miles de estudiantes que ya dominan la contabilidad de forma divertida e interactiva.</p>
            </div>
            <div class="auth-features">
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="fas fa-rocket"></i></span>
                    <span>Comienza desde cero, sin experiencia previa</span>
                </div>
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="fas fa-medal"></i></span>
                    <span>Gana medallas y desbloquea logros</span>
                </div>
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="fas fa-brain"></i></span>
                    <span>Aprende con el método de las 5Es</span>
                </div>
            </div>
        </div>

        <div class="auth-panel auth-panel--right">
            <div class="auth-form-wrapper">
                <div class="auth-form-header">
                    <h2>Crea tu cuenta</h2>
                    <p>Es gratis. Empieza a aprender hoy</p>
                </div>

                <?php if ($errorMessage): ?>
                    <div class="auth-alert auth-alert--error">
                        <i class="fas fa-circle-exclamation"></i>
                        <span><?php echo htmlspecialchars($errorMessage); ?></span>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" class="auth-form" novalidate>
                    <div class="auth-field">
                        <span class="auth-field-icon"><i class="fas fa-id-card"></i></span>
                        <input type="text" id="full_name" name="full_name" placeholder="Nombre completo" required autocomplete="name" value="<?php echo htmlspecialchars($fullName ?? ''); ?>">
                        <label for="full_name">Nombre completo</label>
                    </div>

                    <div class="auth-field">
                        <span class="auth-field-icon"><i class="fas fa-user"></i></span>
                        <input type="text" id="username" name="username" placeholder="Usuario" required autocomplete="username" value="<?php echo htmlspecialchars($username ?? ''); ?>">
                        <label for="username">Usuario</label>
                    </div>

                    <div class="auth-field">
                        <span class="auth-field-icon"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" placeholder="Correo electrónico" required autocomplete="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        <label for="email">Correo electrónico</label>
                    </div>

                    <div class="auth-field">
                        <span class="auth-field-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" placeholder="Contraseña" required autocomplete="new-password">
                        <label for="password">Contraseña</label>
                        <button type="button" class="toggle-password" aria-label="Mostrar contraseña" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="password-strength" id="password-strength">
                        <div class="strength-bar">
                            <span id="strength-fill"></span>
                        </div>
                        <p id="strength-label">Escribe una contraseña</p>
                    </div>

                    <button type="submit" class="auth-submit-btn">
                        <span>Empezar a Aprender</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <div class="auth-links">
                    <a href="login.php" class="auth-link">¿Ya tienes cuenta? <strong>Inicia sesión</strong></a>
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

        const passwordInput = document.getElementById('password');
        const strengthFill  = document.getElementById('strength-fill');
        const strengthLabel = document.getElementById('strength-label');

        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const levels = [
                { label: 'Muy débil', color: '#ef4444', width: '15%' },
                { label: 'Débil',     color: '#f97316', width: '35%' },
                { label: 'Regular',   color: '#eab308', width: '60%' },
                { label: 'Fuerte',    color: '#22c55e', width: '85%' },
                { label: 'Muy fuerte', color: '#16a34a', width: '100%' },
            ];

            if (val.length === 0) {
                strengthFill.style.width = '0';
                strengthLabel.textContent = 'Escribe una contraseña';
                return;
            }

            const level = levels[Math.min(score, 4)];
            strengthFill.style.width  = level.width;
            strengthFill.style.background = level.color;
            strengthLabel.textContent = level.label;
            strengthLabel.style.color = level.color;
        });
    </script>
</body>
</html>
