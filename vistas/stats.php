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

// Obtener mejores jugadores para el Ranking
$stmt = $pdo->query("
    SELECT u.nombre_usuario, s.puntos, s.nivel 
    FROM usuarios u 
    JOIN estadisticas_usuario s ON u.id = s.usuario_id 
    ORDER BY s.puntos DESC 
    LIMIT 5
");
$mejoresJugadores = $stmt->fetchAll();

$paginaActual = 'stats';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - DebiHaby</title>
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
                <h1>Análisis de Desempeño</h1>
                <p>Revisa tus métricas y mejora cada día.</p>
            </div>
        </header>

        <section class="dashboard-container">
            <div class="stats-overview">
                <div class="card stat-box">
                    <span class="stat-label">Precisión Promedio</span>
                    <span class="stat-big-value">92%</span>
                </div>
                <div class="card stat-box">
                    <span class="stat-label">Tiempo de Estudio</span>
                    <span class="stat-big-value">14h 30m</span>
                </div>
                <div class="card stat-box">
                    <span class="stat-label">Ejercicios Completados</span>
                    <span class="stat-big-value">45</span>
                </div>
            </div>

            <div class="dashboard-grid mt-2">
                <div class="card" style="grid-column: span 1;">
                    <h3>🏆 Tabla de Posiciones</h3>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #eee;">
                                <th style="padding: 10px;">Usuario</th>
                                <th style="padding: 10px;">Puntos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mejoresJugadores as $jugador): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 10px;"><?php echo htmlspecialchars($jugador['nombre_usuario']); ?></td>
                                    <td style="padding: 10px; font-weight: bold; color: var(--primary);"><?php echo number_format($jugador['puntos']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
    <script src="../public/js/dashboard.js"></script>
</body>
</html>
