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
$learningPath = $controller->getLearningPath($userId);
$currentPage = 'courses';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Cursos - DebiHaby</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="dashboard-body">

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-welcome">
                <h1>Mis Cursos Contables</h1>
                <p>Explora y continúa aprendiendo.</p>
            </div>
            <!-- Standard stat pills for consistency -->
            <div class="header-stats">
                <div class="stat-pill" title="Puntos DebiHaby">
                    <span class="stat-icon"><i class="fas fa-gem" style="color: #03A9F4;"></i></span>
                    <span class="stat-value"><?php echo number_format($studentStats['points']); ?></span>
                </div>
            </div>
        </header>

        <section class="dashboard-container">
            <div class="courses-grid">
                <?php 
                $categories = ['Activos', 'Pasivos', 'Capital', 'Estados Financieros'];
                foreach ($categories as $cat): 
                    $catLessons = array_filter($learningPath, function($l) use ($cat) { return $l['category'] == $cat; });
                    if (empty($catLessons)) continue;
                ?>
                <div class="card course-card">
                    <div class="card-header">
                        <h3>Módulo: <?php echo $cat; ?></h3>
                        <span class="badge"><?php echo count($catLessons); ?> Lecciones</span>
                    </div>
                    <ul class="lesson-list">
                        <?php foreach($catLessons as $l): ?>
                        <li class="<?php echo $l['status']; ?>">
                            <i class="fas <?php echo $l['status'] == 'completed' ? 'fa-circle-check' : ($l['status'] == 'available' ? 'fa-circle-play' : 'fa-lock'); ?>"></i>
                            <?php echo $l['title']; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <button class="btn btn-primary mt-2" style="width:100%">Continuar Módulo</button>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <script src="js/dashboard.js"></script>
</body>
</html>
