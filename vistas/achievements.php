<?php
session_start();
require_once '../configuracion/config.php';
require_once '../controladores/ControladorDashboard.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$idUsuario = $_SESSION['id_usuario']; 
$controlador = new ControladorDashboard($pdo);
$estadisticasEstudiante = $controlador->obtenerEstadisticasEstudiante($idUsuario);
$paginaActual = 'achievements';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logros - DebiHaby</title>
    <link rel="stylesheet" href="../public/css/styles.css">
    <link rel="stylesheet" href="../public/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="dashboard-body">

    <?php include '../configuracion/sidebar.php'; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-welcome">
                <h1>Mis Trofeos y Medallas</h1>
                <p>¡Celebra tu progreso y colecciona todos!</p>
            </div>
            <div class="header-stats">
                 <div class="stat-pill" title="Nivel">
                    <span class="stat-icon"><i class="fas fa-star" style="color: #FFC107;"></i></span>
                    <span class="stat-value">Nivel <?php echo $estadisticasEstudiante['nivel']; ?></span>
                </div>
            </div>
        </header>

        <section class="dashboard-container">
            <div class="logros-grid">
                <div class="card logro-card earned">
                    <div class="logro-icon"><i class="fas fa-seedling"></i></div>
                    <h3>Semilla Contable</h3>
                    <p>Registraste tu primera cuenta.</p>
                </div>
                <div class="card logro-card earned">
                    <div class="logro-icon"><i class="fas fa-fire"></i></div>
                    <h3>En racha</h3>
                    <p>Entraste 3 días seguidos.</p>
                </div>
                <div class="card logro-card locked">
                    <div class="logro-icon"><i class="fas fa-building-columns"></i></div>
                    <h3>Maestro de Activos</h3>
                    <p>Completa todo el módulo de activos.</p>
                </div>
                <div class="card logro-card locked">
                    <div class="logro-icon"><i class="fas fa-crown"></i></div>
                    <h3>Contador Senior</h3>
                    <p>Llega al nivel 20.</p>
                </div>
            </div>
        </section>
    </main>
    <script src="../public/js/dashboard.js"></script>
</body>
</html>
