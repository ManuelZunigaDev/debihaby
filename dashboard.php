<?php
session_start();
require_once 'includes/config.php';
require_once 'controllers/DashboardController.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id']; 

$controller = new DashboardController($pdo);
$studentStats = $controller->getStudentStats($userId);

// If user stats not found (could happen if DB was reset)
if (!$studentStats) {
    session_destroy();
    header('Location: login.php?error=expired');
    exit;
}

$learningPath = $controller->getLearningPath($userId);
$recentActivity = $controller->getRecentActivity($userId);

// Fetch news
$stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 5");
$allNews = $stmt->fetchAll();

// Fetch myths
$stmt = $pdo->query("SELECT * FROM myths");
$allMyths = $stmt->fetchAll();
if (empty($allMyths)) {
    $allMyths = [
        ['myth' => 'La contabilidad es solo matemáticas', 'reality' => 'Es lógica y organización', 'explanation' => 'Aunque usa números, se trata más de entender el flujo del dinero y la toma de decisiones.'],
        ['myth' => 'Solo se usa en grandes empresas', 'reality' => 'Toda entidad la necesita', 'explanation' => 'Incluso tus finanzas personales o una tienda pequeña se benefician enormemente.']
    ];
}

// Fetch answered expert questions
$stmt = $pdo->query("SELECT q.*, u.username FROM expert_questions q JOIN users u ON q.user_id = u.id WHERE q.status = 'answered' ORDER BY q.created_at DESC");
$expertAnswers = $stmt->fetchAll();

// Fetch ranking for stats tab
$stmt = $pdo->query("SELECT u.username, s.points, s.level FROM users u JOIN user_stats s ON u.id = s.user_id ORDER BY s.points DESC LIMIT 10");
$ranking = $stmt->fetchAll();

// Fetch user data for diagnostic check
$stmt = $pdo->prepare("SELECT knowledge_level, role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();
$needsDiagnosis = ($userData['knowledge_level'] == 'principiante' && !isset($_SESSION['diagnosis_completed']));

// Calculate percentage progress
$totalLessons = count($learningPath);
$completedLessons = count(array_filter($learningPath, function($lesson) {
    return $lesson['status'] === 'completed';
}));
$progressPercent = ($totalLessons > 0) ? round(($completedLessons / $totalLessons) * 100) : 0;
$currentPage = 'dashboard';

// User role check
$userRole = $userData['role'] ?? 'student';

// Handle lesson completion from GET
$lessonCompleted = false;
if (isset($_GET['completed'])) {
    $cId = (int)$_GET['completed'];
    if ($controller->completeLesson($userId, $cId)) {
        $lessonCompleted = true;
        $studentStats = $controller->getStudentStats($userId);
    }
}

// Fetch real admin data if applicable
$allUsers = ($userRole === 'admin') ? $controller->getUsersList() : [];

$expertSuccess = isset($_GET['expert_success']);
$diagnosisSuccess = isset($_GET['diagnosis']);

// Calculate real stats
$avgScore = 0;
$completedForScore = array_filter($learningPath, fn($l) => $l['status'] === 'completed' && $l['score'] > 0);
if (!empty($completedForScore)) {
    $avgScore = round(array_sum(array_column($completedForScore, 'score')) / count($completedForScore));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DebiHaby</title>
    <meta name="description" content="DebiHaby - Tu plataforma gamificada para aprender contabilidad de forma divertida.">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" href="assets/logo2.ico" type="image/x-icon">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js');
        }
    </script>
</head>
<body class="dashboard-body">

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="hamburger-btn" id="hamburger-btn" aria-label="Abrir menú">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-welcome">
                    <h1>Hola, <?php echo htmlspecialchars($studentStats['full_name']); ?> 👋</h1>
                    <p>¡Es un gran día para aprender contabilidad!</p>
                </div>
            </div>
            <div class="header-stats">
                <button id="theme-toggle" class="stat-pill" style="cursor:pointer; border:none;">
                    <span class="stat-icon"><i class="fas fa-moon"></i></span>
                    <span class="stat-value">Modo</span>
                </button>
                <div class="stat-pill" title="Racha de días">
                    <span class="stat-icon"><i class="fas fa-fire" style="color: #FF5722;"></i></span>
                    <span class="stat-value"><?php echo $studentStats['streak']; ?> días</span>
                </div>
                <div class="stat-pill" title="Puntos DebiHaby">
                    <span class="stat-icon"><i class="fas fa-gem" style="color: #03A9F4;"></i></span>
                    <span class="stat-value"><?php echo number_format($studentStats['points']); ?></span>
                </div>
                <div class="user-profile">
                    <img src="<?php echo $studentStats['avatar']; ?>" alt="Avatar" class="avatar-small">
                    <div class="user-info">
                        <span class="level-badge">Nivel <?php echo $studentStats['level']; ?></span>
                    </div>
                </div>
            </div>
        </header>

        <script>
            // === Theme Toggle ===
            const themeToggle = document.getElementById('theme-toggle');
            const htmlRoot = document.documentElement;
            
            const savedTheme = localStorage.getItem('theme') || 'light';
            htmlRoot.setAttribute('data-theme', savedTheme);
            updateIcon(savedTheme);

            themeToggle.addEventListener('click', () => {
                const currentTheme = htmlRoot.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                htmlRoot.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateIcon(newTheme);
            });

            function updateIcon(theme) {
                const icon = themeToggle.querySelector('i');
                icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }

            // === Mobile Sidebar Toggle ===
            const hamburgerBtn = document.getElementById('hamburger-btn');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            hamburgerBtn.addEventListener('click', () => {
                sidebar.classList.toggle('open');
                sidebarOverlay.classList.toggle('active');
            });

            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('active');
            });

            // === Toast System ===
            function showToast(message, type = 'success', duration = 4000) {
                const container = document.getElementById('toast-container');
                const icons = {
                    success: 'fa-circle-check',
                    info: 'fa-circle-info',
                    warning: 'fa-triangle-exclamation',
                    error: 'fa-circle-xmark'
                };
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.innerHTML = `
                    <i class="fas ${icons[type]}"></i>
                    <span>${message}</span>
                    <button class="toast-close" onclick="this.parentElement.classList.add('toast-exit'); setTimeout(() => this.parentElement.remove(), 300);">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                container.appendChild(toast);
                setTimeout(() => {
                    toast.classList.add('toast-exit');
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            }
        </script>

        <section class="dashboard-container">
            <!-- Show toasts for events via JS -->
            <?php if ($lessonCompleted): ?>
                <script>document.addEventListener('DOMContentLoaded', () => showToast('🎉 ¡Felicidades! Lección completada. Has ganado +100 XP.', 'success'));</script>
            <?php endif; ?>

            <?php if ($expertSuccess): ?>
                <script>document.addEventListener('DOMContentLoaded', () => showToast('✅ ¡Pregunta enviada! Un experto te responderá pronto.', 'info'));</script>
            <?php endif; ?>

            <?php if ($diagnosisSuccess): ?>
                <script>document.addEventListener('DOMContentLoaded', () => showToast('🎯 ¡Diagnóstico completado! Tu nivel ha sido actualizado a: <?php echo ucfirst($_SESSION['assigned_level'] ?? 'principiante'); ?>.', 'success'));</script>
            <?php endif; ?>

            <!-- ========== DASHBOARD / RUTA ========== -->
            <div id="tab-dashboard" class="tab-content active">
                <?php if ($needsDiagnosis): ?>
                    <div class="card-premium mission-card mb-2">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <h3 style="margin:0;"><i class="fas fa-bullseye"></i> ¡Comienza tu Diagnóstico!</h3>
                                <p style="margin:0; opacity: 0.9; font-size: 0.9rem;">Evalúa tus conocimientos para personalizar tu ruta.</p>
                            </div>
                            <a href="diagnosis.php" class="btn btn-primary" style="padding: 0.6rem 1.5rem; font-size: 0.9rem;">Iniciar Ahora</a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="dashboard-grid-main">
                    <div class="main-column">
                        <!-- Quick Access -->
                        <div class="card-premium mb-2">
                            <h3><i class="fas fa-rocket"></i> Acceso Rápido</h3>
                            <div class="quick-tools">
                                <a href="javascript:void(0)" onclick="switchTab('news')" class="tool-btn">
                                    <i class="fas fa-newspaper"></i>
                                    <span>Noticias</span>
                                </a>
                                <a href="javascript:void(0)" onclick="switchTab('experts')" class="tool-btn">
                                    <i class="fas fa-user-tie"></i>
                                    <span>Expertos</span>
                                </a>
                                <a href="javascript:void(0)" onclick="switchTab('myths')" class="tool-btn">
                                    <i class="fas fa-brain"></i>
                                    <span>Mitos</span>
                                </a>
                                <a href="javascript:void(0)" onclick="switchTab('resources')" class="tool-btn">
                                    <i class="fas fa-book-atlas"></i>
                                    <span>Recursos</span>
                                </a>
                            </div>
                        </div>

                        <!-- Main Path -->
                        <div class="card path-card card-premium">
                            <div class="card-header">
                                <h2 style="margin:0;"><i class="fas fa-map-marked-alt"></i> Tu Ruta de Aprendizaje</h2>
                                <span class="progress-badge"><?php echo $progressPercent; ?>% completado</span>
                            </div>
                            <div class="learning-path">
                                <?php foreach ($learningPath as $index => $lesson): ?>
                                    <div class="path-node <?php echo $lesson['status']; ?>" title="<?php echo htmlspecialchars($lesson['title']); ?>" onclick="if('<?php echo $lesson['status']; ?>' !== 'locked') window.location.href='lesson.php?id=<?php echo $lesson['id']; ?>'">
                                        <div class="node-circle">
                                            <?php if ($lesson['status'] === 'completed'): ?>
                                                <i class="fas fa-check-circle"></i>
                                            <?php elseif ($lesson['status'] === 'available'): ?>
                                                <i class="fas <?php echo $lesson['icon_class'] ?: 'fa-play'; ?>"></i>
                                            <?php else: ?>
                                                <i class="fas fa-lock"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="node-content">
                                            <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                            <p><?php echo htmlspecialchars($lesson['category']); ?> · <?php echo $lesson['xp_reward']; ?> XP</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-column">
                        <!-- Daily Mission -->
                        <div class="card card-premium mission-card">
                            <h3><i class="fas fa-crosshairs"></i> Misión Diaria</h3>
                            <div class="mission-content">
                                <p>Completa <?php echo max(1, $totalLessons - $completedLessons); ?> lección(es) pendiente(s)</p>
                                <div class="progress-bar-container">
                                    <div class="progress-bar" style="width: <?php echo $progressPercent; ?>%"></div>
                                </div>
                                <span class="xp-gain">+<?php echo max(1, $totalLessons - $completedLessons) * 100; ?> XP posibles</span>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="card activity-card card-premium">
                            <h3><i class="fas fa-clock-rotate-left"></i> Actividad Reciente</h3>
                            <div class="activity-list">
                                <?php if (empty($recentActivity)): ?>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <strong>Sin actividad aún</strong>
                                            <span>¡Comienza tu primera lección!</span>
                                        </div>
                                        <span class="activity-tag">🚀</span>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recentActivity as $activity): ?>
                                        <div class="activity-item">
                                            <div class="activity-info">
                                                <strong><?php echo htmlspecialchars($activity['title']); ?></strong>
                                                <span><?php echo $activity['date']; ?></span>
                                            </div>
                                            <span class="activity-tag"><?php echo $activity['xp']; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Certification CTA -->
                        <div class="card-premium mt-2" style="text-align: center;">
                            <i class="fas fa-certificate" style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.75rem;"></i>
                            <h4>Tu Certificación</h4>
                            <p style="font-size: 0.85rem; color: var(--dark-light);">Completa el 100% para obtener tu diploma oficial.</p>
                            <div class="progress-bar-container" style="background: #eee; margin: 0.75rem 0;">
                                <div class="progress-bar" style="width: <?php echo $progressPercent; ?>%; background: var(--gradient-primary);"></div>
                            </div>
                            <a href="javascript:void(0)" onclick="switchTab('certification')" class="btn btn-secondary" style="width: 100%; border-color: var(--primary); color: var(--primary); font-size: 0.85rem; padding: 0.6rem;">Ver Progreso</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== NEWS ========== -->
            <div id="tab-news" class="tab-content">
                <div class="card-premium">
                    <h2><i class="fas fa-newspaper"></i> Noticias Contables</h2>
                    <div class="news-list mt-2">
                        <?php if (empty($allNews)): ?>
                            <p style="text-align: center; color: var(--dark-light);">No hay noticias por el momento.</p>
                        <?php else: ?>
                            <?php foreach($allNews as $news): ?>
                                <div class="mini-course-item mb-1">
                                    <div>
                                        <span class="badge"><?php echo htmlspecialchars($news['category']); ?></span>
                                        <h4 style="margin-top: 0.5rem;"><?php echo htmlspecialchars($news['title']); ?></h4>
                                        <p style="margin: 0; color: var(--dark-light); font-size: 0.9rem;"><?php echo htmlspecialchars($news['content']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ========== EXPERTS ========== -->
            <div id="tab-experts" class="tab-content">
                <div class="dashboard-grid-main">
                    <div class="main-column">
                        <div class="card-premium mb-2">
                            <h3><i class="fas fa-comments"></i> Pregunta a los Expertos</h3>
                            <form action="experts_handler.php" method="POST" class="mt-1">
                                <textarea name="question" placeholder="¿Cuál es tu duda contable hoy? Escribe tu pregunta aquí..." class="input-modern" style="height:120px; resize: vertical;" required></textarea>
                                <button type="submit" class="btn btn-primary mt-1" style="width: 100%; padding: 0.75rem;">
                                    <i class="fas fa-paper-plane"></i> Enviar Pregunta
                                </button>
                            </form>
                        </div>
                        <div class="card-premium">
                            <h3><i class="fas fa-message"></i> Respuestas Recientes</h3>
                            <?php if (empty($expertAnswers)): ?>
                                <p class="mt-1" style="color: var(--dark-light); text-align: center;">Aún no hay respuestas. ¡Sé el primero en preguntar!</p>
                            <?php else: ?>
                                <?php foreach($expertAnswers as $q): ?>
                                    <div class="activity-item mt-1" style="flex-direction:column; align-items:flex-start;">
                                        <p style="margin:0;"><strong><i class="fas fa-circle-question" style="color: var(--primary);"></i> <?php echo htmlspecialchars($q['question']); ?></strong></p>
                                        <p style="color: var(--success); margin-top: 5px; font-size: 0.9rem;"><i class="fas fa-circle-check"></i> <?php echo htmlspecialchars($q['answer']); ?></p>
                                        <span style="font-size: 0.75rem; color: var(--dark-light);">— <?php echo htmlspecialchars($q['username']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-column">
                        <div class="card-premium">
                            <h4><i class="fas fa-user-graduate"></i> Nuestros Expertos</h4>
                            <div class="mt-1" style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div class="activity-item">
                                    <div class="activity-info">
                                        <strong>Lic. Juan Pérez</strong>
                                        <span>Contabilidad General</span>
                                    </div>
                                    <i class="fas fa-star" style="color: var(--primary);"></i>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-info">
                                        <strong>C.P. María García</strong>
                                        <span>Auditoría Fiscal</span>
                                    </div>
                                    <i class="fas fa-star" style="color: var(--primary);"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== MYTHS ========== -->
            <div id="tab-myths" class="tab-content">
                <div class="card-premium">
                    <h2 style="text-align: center;"><i class="fas fa-brain"></i> Mitos vs Realidades</h2>
                    <p style="text-align: center; color: var(--dark-light);">Descubre la verdad detrás de los mitos contables más comunes.</p>
                    <div class="dashboard-grid-main mt-2" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                        <?php foreach($allMyths as $m): ?>
                            <div class="card-premium" style="border-left: 4px solid var(--primary);">
                                <h4 style="color: var(--danger); font-size: 0.95rem;">
                                    <i class="fas fa-xmark-circle"></i> Mito: <?php echo htmlspecialchars($m['myth']); ?>
                                </h4>
                                <hr class="mt-1 mb-1">
                                <p style="margin: 0;">
                                    <strong style="color: var(--success);"><i class="fas fa-check-circle"></i> Realidad:</strong> 
                                    <?php echo htmlspecialchars($m['reality']); ?>
                                </p>
                                <?php if(!empty($m['explanation'])): ?>
                                    <p style="font-size: 0.85rem; color: var(--dark-light); margin-top: 0.5rem; margin-bottom: 0;">
                                        💡 <?php echo htmlspecialchars($m['explanation']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ========== RESOURCES ========== -->
            <div id="tab-resources" class="tab-content">
                <div class="dashboard-grid-main">
                    <div class="main-column">
                        <div class="card-premium">
                            <h2><i class="fas fa-map-pin"></i> Directorio y Ubicación</h2>
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3738.032213897746!2d-101.7144702!3d20.4536783!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x842c0505c8681941%3A0x7d0694116c4c3461!2sCBTIS%20171!5e0!3m2!1ses-419!2smx!4v1700000000000!5m2!1ses-419!2smx" 
                                width="100%" height="300" style="border:0; border-radius:15px; margin-top:1rem;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                    <div class="card-column">
                        <div class="card-premium">
                            <h4><i class="fas fa-link"></i> Enlaces Útiles</h4>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem;">
                                <a href="https://imcp.org.mx/" target="_blank" class="tool-btn" style="flex-direction: row; gap: 0.75rem; padding: 0.75rem 1rem;">
                                    <i class="fas fa-external-link-alt"></i>
                                    <span>SAT e IMCP</span>
                                </a>
                                <a href="https://www.ifrs.org/" target="_blank" class="tool-btn" style="flex-direction: row; gap: 0.75rem; padding: 0.75rem 1rem;">
                                    <i class="fas fa-external-link-alt"></i>
                                    <span>Normas IFRS</span>
                                </a>
                                <a href="https://www.gob.mx/sat" target="_blank" class="tool-btn" style="flex-direction: row; gap: 0.75rem; padding: 0.75rem 1rem;">
                                    <i class="fas fa-external-link-alt"></i>
                                    <span>Portal del SAT</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== CERTIFICATION ========== -->
            <div id="tab-certification" class="tab-content">
                <div class="card-premium text-center" style="max-width: 600px; margin: 0 auto;">
                    <i class="fas fa-award" style="font-size: 4rem; color: var(--primary); margin-bottom: 1.5rem;"></i>
                    <h2>Tu Certificado DebiHaby</h2>
                    <p class="mt-1" style="color: var(--dark-light);">Para obtener tu certificado, debes completar todos los módulos de aprendizaje con una calificación mínima del 70%.</p>
                    
                    <div style="margin: 2rem 0;">
                        <div style="font-size: 3rem; font-weight: 700; color: var(--primary);"><?php echo $progressPercent; ?>%</div>
                        <div class="progress-bar-container" style="max-width:400px; margin: 1rem auto; background: #eee;">
                            <div class="progress-bar" style="width: <?php echo $progressPercent; ?>%; background: var(--gradient-primary);"></div>
                        </div>
                        <p style="color: var(--dark-light); font-size: 0.9rem;"><?php echo $completedLessons; ?> de <?php echo $totalLessons; ?> lecciones completadas</p>
                    </div>
                    
                    <?php if($progressPercent >= 100): ?>
                        <a href="generate_pdf.php" class="btn btn-primary" style="padding: 0.8rem 2rem;">
                            <i class="fas fa-download"></i> Descargar Certificado PDF
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled style="opacity: 0.6; cursor: not-allowed; border-color: #ccc; color: #999; padding: 0.8rem 2rem;">
                            <i class="fas fa-lock"></i> Certificado No Disponible
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ========== TOOLS (IVA Calculator) ========== -->
            <div id="tab-tools" class="tab-content">
                <div class="card-premium">
                    <h3><i class="fas fa-calculator"></i> Herramientas Contables</h3>
                    <div class="dashboard-grid-main mt-2">
                        <div class="main-column">
                            <div class="card-premium">
                                <h4><i class="fas fa-percent"></i> Calculadora de IVA</h4>
                                <div class="iva-calculator mt-1">
                                    <input type="number" id="base_amount" placeholder="Monto Base ($)" min="0" step="0.01">
                                    <select id="iva_rate">
                                        <option value="0.16">IVA General (16%)</option>
                                        <option value="0.08">IVA Frontera (8%)</option>
                                    </select>
                                    <button class="btn btn-primary" onclick="window.calculateIVA()" style="width: 100%; padding: 0.7rem;">
                                        <i class="fas fa-calculator"></i> Calcular
                                    </button>
                                    <div id="iva_result" class="iva-result"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-column">
                            <div class="card-premium">
                                <h4><i class="fas fa-info-circle"></i> Referencia</h4>
                                <div class="activity-list mt-1">
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <strong>IVA General</strong>
                                            <span>Tasa del 16% en todo México</span>
                                        </div>
                                        <span class="activity-tag">16%</span>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <strong>IVA Frontera</strong>
                                            <span>Tasa preferencial zona norte</span>
                                        </div>
                                        <span class="activity-tag">8%</span>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <strong>Tasa 0%</strong>
                                            <span>Alimentos, medicinas, etc.</span>
                                        </div>
                                        <span class="activity-tag">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    window.calculateIVA = function() {
                        const amount = parseFloat(document.getElementById('base_amount').value);
                        const rate = parseFloat(document.getElementById('iva_rate').value);
                        const resultDiv = document.getElementById('iva_result');
                        
                        if (!amount || amount <= 0) {
                            showToast('Ingresa un monto válido.', 'warning');
                            return;
                        }
                        
                        const iva = amount * rate;
                        const total = amount + iva;
                        const ratePct = Math.round(rate * 100);
                        
                        resultDiv.innerHTML = `
                            <div class="result-row">
                                <span>Subtotal:</span>
                                <span>$${amount.toFixed(2)}</span>
                            </div>
                            <div class="result-row">
                                <span>IVA (${ratePct}%):</span>
                                <span>$${iva.toFixed(2)}</span>
                            </div>
                            <div class="result-row result-total">
                                <span>Total:</span>
                                <span>$${total.toFixed(2)}</span>
                            </div>
                        `;
                        resultDiv.classList.add('visible');
                    }
                </script>
            </div>

            <!-- ========== LIBRARY ========== -->
            <div id="tab-library" class="tab-content">
                <div class="card-premium">
                    <h3><i class="fas fa-book-open"></i> Biblioteca de Material de Apoyo</h3>
                    <p style="color: var(--dark-light); font-size: 0.9rem;">Descarga materiales complementarios para reforzar tu aprendizaje.</p>
                    <div class="activity-list mt-2">
                        <div class="activity-item">
                            <div class="activity-info">
                                <strong>Manual de Contabilidad Básica</strong>
                                <span><i class="fas fa-file-pdf" style="color: #d32f2f;"></i> PDF · 3.2 MB</span>
                            </div>
                            <button class="btn btn-secondary" style="font-size:0.75rem; padding: 0.4rem 1rem; border-color: var(--primary); color: var(--primary);" onclick="showToast('Descarga disponible próximamente.', 'info')">
                                <i class="fas fa-download"></i> Descargar
                            </button>
                        </div>
                        <div class="activity-item">
                            <div class="activity-info">
                                <strong>Ejercicios de Práctica (Excel)</strong>
                                <span><i class="fas fa-file-excel" style="color: #4CAF50;"></i> XLSX · 1.5 MB</span>
                            </div>
                            <button class="btn btn-secondary" style="font-size:0.75rem; padding: 0.4rem 1rem; border-color: var(--primary); color: var(--primary);" onclick="showToast('Descarga disponible próximamente.', 'info')">
                                <i class="fas fa-download"></i> Descargar
                            </button>
                        </div>
                        <div class="activity-item">
                            <div class="activity-info">
                                <strong>Glosario de Términos Contables</strong>
                                <span><i class="fas fa-file-pdf" style="color: #d32f2f;"></i> PDF · 800 KB</span>
                            </div>
                            <button class="btn btn-secondary" style="font-size:0.75rem; padding: 0.4rem 1rem; border-color: var(--primary); color: var(--primary);" onclick="showToast('Descarga disponible próximamente.', 'info')">
                                <i class="fas fa-download"></i> Descargar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== STATS ========== -->
            <div id="tab-stats" class="tab-content">
                <div class="dashboard-grid-main">
                    <div class="main-column">
                        <div class="card-premium">
                            <h3><i class="fas fa-chart-line"></i> Tu Desempeño</h3>
                            <div class="stats-overview mt-2">
                                <div class="stat-box card-premium">
                                    <span class="stat-label">Lecciones</span>
                                    <h2 class="stat-big-value"><?php echo $completedLessons; ?>/<?php echo $totalLessons; ?></h2>
                                </div>
                                <div class="stat-box card-premium">
                                    <span class="stat-label">Puntos</span>
                                    <h2 class="stat-big-value"><?php echo number_format($studentStats['points']); ?></h2>
                                </div>
                                <div class="stat-box card-premium">
                                    <span class="stat-label">Nivel</span>
                                    <h2 class="stat-big-value"><?php echo $studentStats['level']; ?></h2>
                                </div>
                            </div>
                            <div class="card-premium mt-2" style="text-align: center;">
                                <span class="stat-label">Progreso General</span>
                                <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary); margin: 0.5rem 0;"><?php echo $progressPercent; ?>%</div>
                                <div class="progress-bar-container" style="background: #eee; max-width: 300px; margin: 0 auto;">
                                    <div class="progress-bar" style="width: <?php echo $progressPercent; ?>%; background: var(--gradient-primary);"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-column">
                        <div class="card-premium">
                            <h3><i class="fas fa-trophy"></i> Ranking Global</h3>
                            <div class="activity-list mt-1">
                                <?php foreach($ranking as $idx => $r): ?>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <strong>
                                                <?php 
                                                    $medals = ['🥇', '🥈', '🥉'];
                                                    echo ($idx < 3) ? $medals[$idx] : ($idx + 1) . '.';
                                                ?> 
                                                <?php echo htmlspecialchars($r['username']); ?>
                                            </strong>
                                            <span>Nivel <?php echo $r['level']; ?></span>
                                        </div>
                                        <span class="activity-tag"><?php echo number_format($r['points']); ?> pts</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== ADMIN ========== -->
            <?php if($userRole === 'admin'): ?>
            <div id="tab-admin" class="tab-content">
                <div class="card-premium">
                    <h2><i class="fas fa-shield-halved"></i> Panel de Administración</h2>
                    <div class="dashboard-grid-main mt-2">
                        <div class="main-column">
                            <div class="card-premium mb-2">
                                <h3><i class="fas fa-users"></i> Usuarios Registrados</h3>
                                <table class="admin-table mt-1">
                                    <thead>
                                        <tr>
                                            <th>Ref</th>
                                            <th>Nombre</th>
                                            <th>Rol</th>
                                            <th>Puntos</th>
                                            <th>Nivel</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($allUsers as $u): ?>
                                            <tr>
                                                <td>#<?php echo $u['id']; ?></td>
                                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                                <td><span class="badge"><?php echo ucfirst($u['role']); ?></span></td>
                                                <td><?php echo number_format($u['points']); ?></td>
                                                <td><?php echo $u['level']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-column">
                            <div class="card-premium">
                                <h4><i class="fas fa-sliders"></i> Gestión de Contenido</h4>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem;">
                                    <button class="btn btn-primary" style="width:100%; font-size:0.85rem; padding: 0.7rem;" onclick="showToast('Funcionalidad en desarrollo.', 'info')">
                                        <i class="fas fa-plus"></i> Nueva Lección
                                    </button>
                                    <button class="btn btn-primary" style="width:100%; font-size:0.85rem; padding: 0.7rem;" onclick="showToast('Funcionalidad en desarrollo.', 'info')">
                                        <i class="fas fa-plus"></i> Nueva Noticia
                                    </button>
                                    <button class="btn btn-secondary" style="width:100%; font-size:0.85rem; padding: 0.7rem; border-color: var(--primary); color: var(--primary);" onclick="showToast('Funcionalidad en desarrollo.', 'info')">
                                        <i class="fas fa-download"></i> Log de Errores
                                    </button>
                                </div>
                            </div>
                            <div class="card-premium mt-2">
                                <h4><i class="fas fa-chart-pie"></i> Resumen</h4>
                                <div class="activity-list mt-1">
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <strong>Total Usuarios</strong>
                                        </div>
                                        <span class="activity-tag"><?php echo count($allUsers); ?></span>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <strong>Lecciones</strong>
                                        </div>
                                        <span class="activity-tag"><?php echo $totalLessons; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </section>

    </main>

    <script>
        // === Tab Navigation ===
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });

            const activeTab = document.getElementById('tab-' + tabId);
            if (activeTab) {
                activeTab.classList.add('active');
            }

            const sidebarItem = document.querySelector(`.nav-item[onclick*="${tabId}"]`);
            if (sidebarItem) {
                sidebarItem.classList.add('active');
            }

            // Close mobile sidebar on tab switch
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar && overlay) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            }

            // Update URL hash
            history.replaceState(null, null, '#' + tabId);
        }

        // Initialize with default or from URL hash
        window.addEventListener('load', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash) switchTab(hash);
        });
    </script>
</body>
</html>
