<?php
session_start();
require_once 'includes/config.php';
require_once 'controllers/DashboardController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id']; 
$controller = new DashboardController($pdo);
$studentStats = $controller->getStudentStats($userId);
$currentPage = 'settings';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - DebiHaby</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="dashboard-body">
    <script>
        // Apply saved theme immediately
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-welcome">
                <h1>Configuración de Perfil</h1>
                <p>Personaliza tu experiencia.</p>
            </div>
        </header>

        <section class="dashboard-container">
            <div class="card" style="max-width: 600px;">
                <form action="#" method="POST"> <!-- Placeholder action -->
                    <div class="form-group mb-2">
                        <label>Nombre Público</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($studentStats['full_name']); ?>" class="input-modern">
                    </div>
                    <div class="form-group mb-2">
                        <label>Avatar de Mascota</label>
                        <div class="avatar-selector">
                            <img src="assets/debi_pet.png" class="selected" title="Actual">
                            <div class="avatar-option">🦊</div>
                            <div class="avatar-option">🐻</div>
                            <div class="avatar-option">🐼</div>
                            <div class="avatar-option">🦁</div>
                        </div>
                        <p style="font-size: 0.8rem; color: #888; margin-top: 5px;">Selecciona tu compañero de aprendizaje.</p>
                    </div>
                    <div class="form-group mb-2">
                        <label>Correo Electrónico (No editable)</label>
                        <input type="email" value="usuario@ejemplo.com" class="input-modern" disabled style="background: #eee;">
                    </div>
                    <button class="btn btn-primary" type="button" onclick="Swal.fire({title: 'Próximamente', text: 'Funcionalidad de guardar próximamente', icon: 'info', confirmButtonColor: '#4CAF50'})">Guardar Cambios</button>
                    <a href="logout.php" class="btn btn-secondary" style="margin-left: 10px; background: #eee; color: #333;">Cerrar Sesión</a>
                </form>
            </div>
        </section>
    </main>
    <script src="js/dashboard.js"></script>
</body>
</html>
