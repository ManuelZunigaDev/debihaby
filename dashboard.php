<?php
session_start();
require_once 'includes/config.php';
require_once 'controllers/DashboardController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id']; 
$dashboardController = new DashboardController($pdo);
$studentStats = $dashboardController->getStudentStats($userId);

if (!$studentStats) {
    session_destroy();
    header('Location: login.php?error=expired');
    exit;
}

$learningPath = $dashboardController->getLearningPath($userId);
$currentLesson = $dashboardController->getCurrentLesson($userId);
$recentActivity = $dashboardController->getRecentActivity($userId);
$courses = $dashboardController->getCourses($userId);

try {
    $stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 5");
    $allNews = $stmt->fetchAll();
} catch (PDOException $e) {
    $allNews = [];
}

try {
    $stmt = $pdo->query("SELECT * FROM myths");
    $allMyths = $stmt->fetchAll();
} catch (PDOException $e) {
    $allMyths = [];
}

if (empty($allMyths)) {
    $allMyths = [
        ['myth' => 'La contabilidad es solo matemáticas', 'reality' => 'Es lógica y organización', 'explanation' => 'Aunque usa números, se trata más de entender el flujo del dinero y la toma de decisiones.'],
        ['myth' => 'Solo se usa en grandes empresas', 'reality' => 'Toda entidad la necesita', 'explanation' => 'Incluso tus finanzas personales o una tienda pequeña se benefician enormemente.']
    ];
}

$stmt = $pdo->query("SELECT q.*, u.username FROM expert_questions q JOIN users u ON q.user_id = u.id WHERE q.status = 'answered' ORDER BY q.created_at DESC");
$expertAnswers = $stmt->fetchAll();

$stmt = $pdo->query("SELECT u.username, s.points, s.level FROM users u JOIN user_stats s ON u.id = s.user_id ORDER BY s.points DESC LIMIT 10");
$ranking = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT knowledge_level, role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();
$needsDiagnosis = ($userData['knowledge_level'] == 'principiante' && !isset($_SESSION['diagnosis_completed']));

$totalLessons = count($learningPath);
$completedLessons = count(array_filter($learningPath, function($lesson) {
    return $lesson['status'] === 'completed';
}));
$progressPercent = ($totalLessons > 0) ? round(($completedLessons / $totalLessons) * 100) : 0;
$userRole = $userData['role'] ?? 'student';

$lessonCompleted = false;
if (isset($_GET['completed'])) {
    $cId = (int)$_GET['completed'];
    if ($dashboardController->completeLesson($userId, $cId)) {
        $lessonCompleted = true;
        $studentStats = $dashboardController->getStudentStats($userId);
    }
}

$allUsers = ($userRole === 'admin') ? $dashboardController->getUsersList() : [];
$expertSuccess = isset($_GET['expert_success']);
$diagnosisSuccess = isset($_GET['diagnosis']);
$lockedAlert      = isset($_GET['locked']);

$avgScore = 0;
$completedWithScore = array_filter($learningPath, fn($l) => $l['status'] === 'completed' && $l['score'] > 0);
if (!empty($completedWithScore)) {
    $avgScore = round(array_sum(array_column($completedWithScore, 'score')) / count($completedWithScore));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DebiHaby</title>
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
    <div class="toast-container" id="toast-container"></div>

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="hamburger-btn" id="hamburger-btn"><i class="fas fa-bars"></i></button>
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
                <div class="stat-pill">
                    <span class="stat-icon"><i class="fas fa-fire" style="color: #FF5722;"></i></span>
                    <span class="stat-value"><?php echo $studentStats['streak']; ?> días</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-icon"><i class="fas fa-gem" style="color: #03A9F4;"></i></span>
                    <span class="stat-value"><?php echo number_format($studentStats['points']); ?></span>
                </div>
                <div class="user-profile">
                    <img src="<?php echo $studentStats['avatar'] ?: 'assets/debi_pet.png'; ?>" alt="Avatar" class="avatar-small">
                    <div class="user-info">
                        <span class="level-badge">Nivel <?php echo $studentStats['level']; ?></span>
                    </div>
                </div>
            </div>
        </header>

        <section class="dashboard-container">
            <?php if ($lessonCompleted): ?>
                <script>document.addEventListener('DOMContentLoaded', () => showToast('🎉 ¡Felicidades! Lección completada. Has ganado +100 XP.', 'success'));</script>
            <?php endif; ?>
            <?php if ($expertSuccess): ?>
                <script>document.addEventListener('DOMContentLoaded', () => showToast('✅ ¡Pregunta enviada! Un experto te responderá pronto.', 'info'));</script>
            <?php endif; ?>
            <?php if ($diagnosisSuccess): ?>
                <script>document.addEventListener('DOMContentLoaded', () => showToast('🎯 ¡Diagnóstico completado! Tu nivel ha sido actualizado.', 'success'));</script>
            <?php endif; ?>
            <?php if ($lockedAlert): ?>
                <script>document.addEventListener('DOMContentLoaded', () => showToast('🔒 Esta lección aún está bloqueada.', 'warning'));</script>
            <?php endif; ?>

            <div id="tab-dashboard" class="tab-content active">
                <?php if ($needsDiagnosis): ?>
                    <div class="card-premium mission-card mb-2">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <h3 style="margin:0;"><i class="fas fa-bullseye"></i> ¡Comienza tu Diagnóstico!</h3>
                                <p style="margin:0; opacity: 0.9; font-size: 0.9rem;">Evalúa tus conocimientos para personalizar tu ruta.</p>
                            </div>
                            <a href="diagnosis.php" class="btn btn-primary">Iniciar Ahora</a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="dashboard-grid-main">
                    <div class="main-column" style="grid-column: 1 / -1;">
                        <div class="current-lesson-hero card-premium mb-2" style="min-height: 300px; display: flex; flex-direction: column; justify-content: center;">
                            <div class="hero-label">CONTINUAR APRENDIENDO</div>
                            <?php if ($currentLesson): ?>
                                <h1 style="font-size: 2.5rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($currentLesson['title']); ?></h1>
                                <p style="font-size: 1.1rem; max-width: 600px;"><?php echo htmlspecialchars($currentLesson['description']); ?></p>
                                <div class="lesson-meta" style="margin: 1.5rem 0;">
                                    <span><i class="fas fa-tags"></i> <?php echo htmlspecialchars($currentLesson['category']); ?></span>
                                    <span><i class="fas fa-bolt"></i> +<?php echo $currentLesson['xp_reward']; ?> XP</span>
                                </div>
                                <a href="lesson.php?id=<?php echo $currentLesson['id']; ?>" class="btn btn-primary btn-lg" style="align-self: flex-start; padding: 1rem 2rem; font-size: 1.1rem;">
                                    <i class="fas fa-play"></i> Iniciar Lección
                                </a>
                            <?php else: ?>
                                <h2>¡Has completado todas las lecciones!</h2>
                                <p>Excelente trabajo. Mantente atento a nuevas actualizaciones o repasa tus cursos anteriores.</p>
                                <a href="javascript:void(0)" onclick="switchTab('stats')" class="btn btn-secondary mt-1">Ver Mis Logros</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Redesigned Mis Cursos Tab -->
            <div id="tab-courses" class="tab-content">
                <div class="courses-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
                    <?php foreach ($courses as $course): 
                        $courseProgress = ($course['total_lessons'] > 0) ? round(($course['completed_lessons'] / $course['total_lessons']) * 100) : 0;
                    ?>
                        <div class="card-premium course-card" style="display: flex; flex-direction: column; padding: 1.5rem; border-top: 5px solid <?php echo $course['color']; ?>;">
                            <div style="display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 1rem;">
                                <div class="course-icon" style="flex-shrink: 0; width: 60px; height: 60px; background: <?php echo $course['color']; ?>15; color: <?php echo $course['color']; ?>; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
                                    <i class="fas <?php echo $course['icon']; ?>"></i>
                                </div>
                                <div style="flex-grow: 1;">
                                    <span class="badge" style="background: <?php echo $course['color']; ?>20; color: <?php echo $course['color']; ?>;"><?php echo htmlspecialchars($course['category']); ?></span>
                                    <h3 style="margin: 0.5rem 0 0 0; font-size: 1.25rem;"><?php echo htmlspecialchars($course['title']); ?></h3>
                                </div>
                            </div>
                            <p style="color: var(--dark-light); font-size: 0.95rem; margin-bottom: 1.5rem; min-height: 45px;"><?php echo htmlspecialchars($course['description']); ?></p>
                            
                            <div class="course-stats" style="margin-top: auto;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.5rem; font-weight: 600;">
                                    <span><?php echo $courseProgress; ?>% Completado</span>
                                    <span><?php echo $course['completed_lessons']; ?>/<?php echo $course['total_lessons']; ?></span>
                                </div>
                                <div class="progress-bar-container" style="height: 10px; background: #eee;">
                                    <div class="progress-bar" style="width: <?php echo $courseProgress; ?>%; background: <?php echo $course['color']; ?>; border-radius: 10px;"></div>
                                </div>
                            </div>

                            <button onclick="toggleLessons(<?php echo $course['id']; ?>)" class="btn btn-secondary mt-1" style="width: 100%; border: 1px solid #ddd;">
                                <i class="fas fa-list-ul"></i> Temario
                            </button>
                            
                            <!-- Hidden Lessons List -->
                            <div id="lessons-list-<?php echo $course['id']; ?>" class="nested-lessons" style="display: none; margin-top: 1rem; border-top: 1px solid #eee; padding-top: 1rem;">
                                <?php 
                                $lessons = array_filter($learningPath, fn($l) => $l['course_id'] == $course['id']);
                                foreach ($lessons as $lesson): ?>
                                    <div class="mini-lesson-item <?php echo $lesson['status']; ?>" 
                                         style="display: flex; align-items: center; gap: 0.8rem; padding: 0.8rem; border-radius: 10px; margin-bottom: 0.5rem; cursor: pointer; transition: 0.2s;"
                                         onclick="if('<?php echo $lesson['status']; ?>' !== 'locked') window.location.href='lesson.php?id=<?php echo $lesson['id']; ?>'">
                                        <div class="status-dot <?php echo $lesson['status']; ?>" style="width: 12px; height: 12px; border-radius: 50%;"></div>
                                        <span style="font-size: 0.9rem; flex-grow: 1;"><?php echo htmlspecialchars($lesson['title']); ?></span>
                                        <?php if ($lesson['status'] === 'completed'): ?><i class="fas fa-check-circle" style="color: var(--success);"></i><?php endif; ?>
                                        <?php if ($lesson['status'] === 'locked'): ?><i class="fas fa-lock" style="color: #ccc;"></i><?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="tab-news" class="tab-content">
                <div class="card-premium">
                    <h2><i class="fas fa-newspaper"></i> Noticias Contables</h2>
                    <div class="news-list mt-2">
                        <?php foreach($allNews as $news): ?>
                            <div class="mini-course-item mb-1">
                                <div>
                                    <span class="badge"><?php echo htmlspecialchars($news['category']); ?></span>
                                    <h4 style="margin-top:0.5rem;"><?php echo htmlspecialchars($news['title']); ?></h4>
                                    <p style="color:var(--dark-light); font-size:0.9rem;"><?php echo htmlspecialchars($news['content']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div id="tab-experts" class="tab-content">
                <div class="dashboard-grid-main">
                    <div class="main-column">
                        <div class="card-premium mb-2">
                            <h3><i class="fas fa-comments"></i> Pregunta a los Expertos</h3>
                            <form action="experts_handler.php" method="POST" class="mt-1">
                                <textarea name="question" placeholder="Escribe tu duda contable aquí..." class="input-modern" style="height:120px;" required></textarea>
                                <button type="submit" class="btn btn-primary mt-1" style="width: 100%;"><i class="fas fa-paper-plane"></i> Enviar</button>
                            </form>
                        </div>
                        <div class="card-premium">
                            <h3><i class="fas fa-message"></i> Respuestas Recientes</h3>
                            <?php foreach($expertAnswers as $q): ?>
                                <div class="activity-item mt-1" style="flex-direction:column; align-items:flex-start;">
                                    <p><strong><i class="fas fa-circle-question" style="color: var(--primary);"></i> <?php echo htmlspecialchars($q['question']); ?></strong></p>
                                    <p style="color: var(--success); margin-top: 5px; font-size: 0.9rem;"><i class="fas fa-circle-check"></i> <?php echo htmlspecialchars($q['answer']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tab-myths" class="tab-content">
                <div class="card-premium">
                    <h2 style="text-align: center;"><i class="fas fa-brain"></i> Mitos vs Realidades</h2>
                    <div class="dashboard-grid-main mt-2" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                        <?php foreach($allMyths as $m): ?>
                            <div class="card-premium" style="border-left: 4px solid var(--primary);">
                                <h4 style="color: var(--danger); font-size: 0.95rem;"><i class="fas fa-xmark-circle"></i> Mito: <?php echo htmlspecialchars($m['myth']); ?></h4>
                                <p style="margin: 5px 0;"><strong style="color: var(--success);"><i class="fas fa-check-circle"></i> Realidad:</strong> <?php echo htmlspecialchars($m['reality']); ?></p>
                                <?php if(!empty($m['explanation'])): ?><p style="font-size: 0.85rem; color: var(--dark-light);">💡 <?php echo htmlspecialchars($m['explanation']); ?></p><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div id="tab-certification" class="tab-content">
                <div class="card-premium text-center" style="max-width:600px; margin: 0 auto;">
                    <i class="fas fa-award" style="font-size:4rem; color:var(--primary); margin-bottom:1.5rem;"></i>
                    <h2>Tu Certificado</h2>
                    <p>Completa todos los módulos al 100% para obtenerlo.</p>
                    <div style="margin: 2rem 0;">
                        <div style="font-size: 3rem; font-weight:700; color:var(--primary);"><?php echo $progressPercent; ?>%</div>
                        <div class="progress-bar-container" style="max-width:400px; margin: 1rem auto; background:#eee;"><div class="progress-bar" style="width: <?php echo $progressPercent; ?>%; background:var(--gradient-primary);"></div></div>
                    </div>
                    <?php if($progressPercent >= 100): ?>
                        <a href="generate_pdf.php" class="btn btn-primary"><i class="fas fa-download"></i> Descargar PDF</a>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled style="opacity:0.6;"><i class="fas fa-lock"></i> No Disponible</button>
                    <?php endif; ?>
                </div>
            </div>

            <div id="tab-tools" class="tab-content">
                <div class="card-premium" style="max-width:600px; margin:0 auto;">
                    <h3><i class="fas fa-calculator"></i> Calculadora de IVA</h3>
                    <div class="iva-calculator mt-1">
                        <input type="number" id="base_amount" placeholder="Monto Base ($)" min="0" step="0.01" class="input-modern">
                        <select id="iva_rate" class="input-modern mt-1" style="width:100%;">
                            <option value="0.16">IVA General (16%)</option>
                            <option value="0.08">IVA Frontera (8%)</option>
                        </select>
                        <button class="btn btn-primary mt-1" onclick="calculateIVA()" style="width: 100%;">Calcular</button>
                        <div id="iva_result" class="iva-result"></div>
                    </div>
                </div>
            </div>

            <div id="tab-stats" class="tab-content">
                <div class="dashboard-grid-main">
                    <div class="main-column">
                        <div class="card-premium">
                            <h3><i class="fas fa-chart-line"></i> Tu Desempeño</h3>
                            <div class="stats-overview mt-2">
                                <div class="stat-box card-premium"><span>Lecciones</span><h2><?php echo $completedLessons; ?>/<?php echo $totalLessons; ?></h2></div>
                                <div class="stat-box card-premium"><span>Puntos</span><h2><?php echo number_format($studentStats['points']); ?></h2></div>
                                <div class="stat-box card-premium"><span>Nivel</span><h2><?php echo $studentStats['level']; ?></h2></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-column">
                        <div class="card-premium">
                            <h3><i class="fas fa-trophy"></i> Ranking Global</h3>
                            <div class="activity-list mt-1">
                                <?php foreach($ranking as $idx => $r): ?>
                                    <div class="activity-item">
                                        <div class="activity-info"><strong><?php echo ($idx+1); ?>. <?php echo htmlspecialchars($r['username']); ?></strong><span>Nivel <?php echo $r['level']; ?></span></div>
                                        <span class="activity-tag"><?php echo number_format($r['points']); ?> pts</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($userRole === 'admin'): ?>
            <div id="tab-admin" class="tab-content">
                <div class="card-premium">
                    <h2><i class="fas fa-shield-halved"></i> Panel Admin</h2>
                    <table class="admin-table mt-1">
                        <thead><tr><th>ID</th><th>Usuario</th><th>Rol</th><th>Puntos</th></tr></thead>
                        <tbody>
                            <?php foreach($allUsers as $u): ?>
                                <tr><td>#<?php echo $u['id']; ?></td><td><?php echo htmlspecialchars($u['username']); ?></td><td><?php echo $u['role']; ?></td><td><?php echo number_format($u['points']); ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </section>
    </main>

    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const htmlRoot = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlRoot.setAttribute('data-theme', savedTheme);
        themeToggle.querySelector('i').className = savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';

        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlRoot.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            htmlRoot.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            themeToggle.querySelector('i').className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        });

        const hamburgerBtn = document.getElementById('hamburger-btn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        hamburgerBtn.addEventListener('click', () => { sidebar.classList.toggle('open'); sidebarOverlay.classList.toggle('active'); });
        sidebarOverlay.addEventListener('click', () => { sidebar.classList.remove('open'); sidebarOverlay.classList.remove('active'); });

        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('toast-container');
            const icons = { success: 'fa-circle-check', info: 'fa-circle-info', warning: 'fa-triangle-exclamation', error: 'fa-circle-xmark' };
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `<i class="fas ${icons[type]}"></i><span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => { toast.classList.add('toast-exit'); setTimeout(() => toast.remove(), 300); }, duration);
        }

        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
            const activeTab = document.getElementById('tab-' + tabId);
            if (activeTab) activeTab.classList.add('active');
            const navItem = document.querySelector(`.nav-item[onclick*="${tabId}"]`);
            if (navItem) navItem.classList.add('active');
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
            history.replaceState(null, null, '#' + tabId);
        }

        function calculateIVA() {
            const amount = parseFloat(document.getElementById('base_amount').value);
            const rate = parseFloat(document.getElementById('iva_rate').value);
            const resultDiv = document.getElementById('iva_result');
            if (!amount || amount <= 0) { showToast('Ingresa un monto válido.', 'warning'); return; }
            const iva = amount * rate;
            const total = amount + iva;
            resultDiv.innerHTML = `<div class="result-row"><span>Subtotal:</span><span>$${amount.toFixed(2)}</span></div><div class="result-row"><span>IVA:</span><span>$${iva.toFixed(2)}</span></div><div class="result-row result-total"><span>Total:</span><span>$${total.toFixed(2)}</span></div>`;
            resultDiv.classList.add('visible');
        }

        function toggleLessons(courseId) {
            const list = document.getElementById('lessons-list-' + courseId);
            const isVisible = list.style.display === 'block';
            list.style.display = isVisible ? 'none' : 'block';
        }

        window.addEventListener('load', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash) switchTab(hash);
        });
    </script>
</body>
</html>
