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
                    <div class="main-column">
                        <div class="current-lesson-hero card-premium mb-2">
                            <div class="hero-label">CONTINUAR APRENDIENDO</div>
                            <?php if ($currentLesson): ?>
                                <h2><?php echo htmlspecialchars($currentLesson['title']); ?></h2>
                                <p><?php echo htmlspecialchars($currentLesson['description']); ?></p>
                                <div class="lesson-meta">
                                    <span><i class="fas fa-tags"></i> <?php echo htmlspecialchars($currentLesson['category']); ?></span>
                                    <span><i class="fas fa-bolt"></i> +<?php echo $currentLesson['xp_reward']; ?> XP</span>
                                </div>
                                <a href="lesson.php?id=<?php echo $currentLesson['id']; ?>" class="btn btn-primary mt-1">
                                    <i class="fas fa-play"></i> Iniciar Lección
                                </a>
                            <?php else: ?>
                                <h2>¡Has completado todas las lecciones!</h2>
                                <p>Mantente atento a nuevas actualizaciones.</p>
                            <?php endif; ?>
                        </div>

                        <div class="card-premium mb-2">
                            <h3><i class="fas fa-rocket"></i> Acceso Rápido</h3>
                            <div class="quick-tools">
                                <a href="javascript:void(0)" onclick="switchTab('news')" class="tool-btn"><i class="fas fa-newspaper"></i><span>Noticias</span></a>
                                <a href="javascript:void(0)" onclick="switchTab('experts')" class="tool-btn"><i class="fas fa-user-tie"></i><span>Expertos</span></a>
                                <a href="javascript:void(0)" onclick="switchTab('myths')" class="tool-btn"><i class="fas fa-brain"></i><span>Mitos</span></a>
                                <a href="javascript:void(0)" onclick="switchTab('tools')" class="tool-btn"><i class="fas fa-calculator"></i><span>Calculadora</span></a>
                            </div>
                        </div>
                    </div>

                    <div class="card-column">
                        <div class="card card-premium mission-card">
                            <h3><i class="fas fa-crosshairs"></i> Tu Progreso</h3>
                            <div class="mission-content">
                                <p><?php echo $completedLessons; ?> de <?php echo $totalLessons; ?> lecciones</p>
                                <div class="progress-bar-container"><div class="progress-bar" style="width: <?php echo $progressPercent; ?>%"></div></div>
                            </div>
                        </div>
                        
                        <div class="card activity-card card-premium">
                            <h3><i class="fas fa-clock-rotate-left"></i> Actividad Reciente</h3>
                            <div class="activity-list">
                                <?php if (empty($recentActivity)): ?>
                                    <div class="activity-item"><div class="activity-info"><strong>¡Bienvenido!</strong><span>Comienza hoy</span></div><span class="activity-tag">🚀</span></div>
                                <?php else: ?>
                                    <?php foreach (array_slice($recentActivity, 0, 3) as $activity): ?>
                                        <div class="activity-item"><div class="activity-info"><strong><?php echo htmlspecialchars($activity['title']); ?></strong><span><?php echo $activity['date']; ?></span></div><span class="activity-tag"><?php echo $activity['xp']; ?></span></div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Mis Cursos Tab -->
            <div id="tab-courses" class="tab-content">
                <div class="card path-card card-premium">
                    <div class="card-header">
                        <h2 style="margin:0;"><i class="fas fa-graduation-cap"></i> Mis Cursos</h2>
                        <span class="progress-badge"><?php echo $progressPercent; ?>% completado</span>
                    </div>
                    <div class="learning-path">
                        <?php foreach ($learningPath as $lesson): ?>
                            <div class="path-node <?php echo $lesson['status']; ?>" onclick="if('<?php echo $lesson['status']; ?>' !== 'locked') window.location.href='lesson.php?id=<?php echo $lesson['id']; ?>'">
                                <div class="node-circle">
                                    <?php if ($lesson['status'] === 'completed'): ?><i class="fas fa-check-circle"></i>
                                    <?php elseif ($lesson['status'] === 'available'): ?><i class="fas <?php echo $lesson['icon_class'] ?: 'fa-play'; ?>"></i>
                                    <?php else: ?><i class="fas fa-lock"></i><?php endif; ?>
                                </div>
                                <div class="node-content">
                                    <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($lesson['category']); ?> · <?php echo $lesson['xp_reward']; ?> XP</p>
                                    <?php if ($lesson['status'] === 'completed'): ?><span class="score-tag" style="font-size: 0.8rem; color: var(--primary); font-weight: 600;">Calificación: <?php echo $lesson['score']; ?>%</span><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
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

        window.addEventListener('load', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash) switchTab(hash);
        });
    </script>
</body>
</html>
