<?php
session_start();
require_once '../configuracion/config.php';

// Si ya está logueado lo mandamos al dashboard
if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}

$mensajeError = '';
$nombreUsuario = '';
$email = '';
$nombreCompleto = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombreUsuario = trim($_POST['nombre_usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    // Validar campos
    if (!$nombreUsuario || !$email || !$nombreCompleto || !$contrasena) {
        $mensajeError = "Completa todos los campos.";
    }

    elseif (strlen($contrasena) < 8) {
        $mensajeError = "La contraseña debe tener mínimo 8 caracteres.";
    }

    else {

        // Verificar si el usuario o correo ya existen
        $stmt = $pdo->prepare("
        SELECT id FROM usuarios 
        WHERE nombre_usuario = ? OR correo = ?
        ");

        $stmt->execute([$nombreUsuario, $email]);

        if ($stmt->fetch()) {

            $mensajeError = "El usuario o correo ya están registrados.";

        } else {

            // Crear hash de contraseña
            $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

            // Insertar usuario
            $stmt = $pdo->prepare("
            INSERT INTO usuarios
            (nombre_usuario, correo, contrasena, nombre_completo)
            VALUES (?, ?, ?, ?)
            ");

            if ($stmt->execute([$nombreUsuario, $email, $contrasenaHash, $nombreCompleto])) {

                $idUsuario = $pdo->lastInsertId();

                // Crear estadísticas iniciales
                $stmt = $pdo->prepare("
                INSERT INTO estadisticas_usuario
                (usuario_id, puntos, nivel, experiencia, racha)
                VALUES (?,0,1,0,0)
                ");
                $stmt->execute([$idUsuario]);

                // Obtener primera lección
                $stmt = $pdo->query("
                SELECT id 
                FROM lecciones 
                ORDER BY indice_orden ASC, id ASC 
                LIMIT 1
                ");

                $idPrimeraLeccion = $stmt->fetchColumn();

                if ($idPrimeraLeccion) {

                    $stmt = $pdo->prepare("
                    INSERT INTO progreso_usuario
                    (usuario_id, leccion_id, estado)
                    VALUES (?, ?, 'disponible')
                    ");

                    $stmt->execute([$idUsuario, $idPrimeraLeccion]);
                }

                // Iniciar sesión
                $_SESSION['id_usuario'] = $idUsuario;
                $_SESSION['nombre_usuario'] = $nombreUsuario;
                $_SESSION['rol_usuario'] = 'estudiante';

                header('Location: dashboard.php');
                exit;

            } else {

                $mensajeError = "Error al registrar el usuario.";

            }

        }

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
                    <h1 class="premium-title">Únete a DebiHaby</h1>
                    <p class="premium-subtitle">Comienza tu viaje hacia la maestría contable hoy mismo.</p>
                    
                    <div class="feature-pills">
                        <div class="feature-pill">
                            <i class="fas fa-rocket"></i>
                            <span>Desde Cero</span>
                        </div>
                        <div class="feature-pill">
                            <i class="fas fa-medal"></i>
                            <span>Logros Únicos</span>
                        </div>
                        <div class="feature-pill">
                            <i class="fas fa-brain"></i>
                            <span>Método 5Es</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-form-panel">
                <div class="form-inner">
                    <div class="form-header">
                        <h2>Crear Cuenta</h2>
                        <p>Regístrate para empezar a aprender</p>
                    </div>

                    <?php if ($mensajeError): ?>
                        <div class="auth-alert-premium animate-shake">
                            <i class="fas fa-circle-exclamation"></i>
                            <span><?php echo htmlspecialchars($mensajeError); ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="POST" class="premium-form">
                        <div class="input-group-premium">
                            <label for="nombre_completo">Nombre Completo</label>
                            <div class="input-wrapper">
                                <i class="fas fa-id-card"></i>
                                <input type="text" id="nombre_completo" name="nombre_completo" placeholder="Tu nombre y apellido" required autocomplete="name" value="<?php echo htmlspecialchars($nombreCompleto); ?>">
                            </div>
                        </div>

                        <div class="input-group-premium">
                            <label for="nombre_usuario">Usuario</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" id="nombre_usuario" name="nombre_usuario" placeholder="Elige un alias" required autocomplete="username" value="<?php echo htmlspecialchars($nombreUsuario); ?>">
                            </div>
                        </div>

                        <div class="input-group-premium">
                            <label for="email">Correo Electrónico</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required autocomplete="email" value="<?php echo htmlspecialchars($email); ?>">
                            </div>
                        </div>

                        <div class="input-group-premium">
                            <label for="contrasena">Contraseña</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="contrasena" name="contrasena" placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
                                <button type="button" class="toggle-password-v2" aria-label="Toggle password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-premium-submit">
                            <span>Empezar a Aprender</span>
                        </button>
                    </form>

                    <div class="form-footer">
                        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
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
