<?php
session_start();

/**
 * ORQUESTADOR
 */
require_once '../configuracion/config.php';
require_once '../controladores/ControladorDashboard.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$idUsuario = $_SESSION['id_usuario'];

$controladorDashboard = new ControladorDashboard($pdo);
$estadisticasEstudiante = $controladorDashboard->obtenerEstadisticasEstudiante($idUsuario);

if (!$estadisticasEstudiante) {
    session_destroy();
    header('Location: login.php?error=expirado');
    exit;
}

$rutaAprendizaje = $controladorDashboard->obtenerRutaAprendizaje($idUsuario);
$leccionActual = $controladorDashboard->obtenerLeccionActual($idUsuario);
$actividadReciente = $controladorDashboard->obtenerActividadReciente($idUsuario);

try {
    $stmt = $pdo->query("SELECT * FROM noticias ORDER BY creado_en DESC LIMIT 5");
    $todasLasNoticias = $stmt->fetchAll();
}
catch (PDOException $e) {
    $todasLasNoticias = [];
}

try {
    $stmt = $pdo->query("SELECT * FROM mitos");
    $todosLosMitos = $stmt->fetchAll();
}
catch (PDOException $e) {
    $todosLosMitos = [];
}

if (empty($todosLosMitos)) {
    $todosLosMitos = [
        ['mito' => 'La contabilidad es solo matemáticas', 'realidad' => 'Es lógica y organización', 'explicacion' => 'Aunque usa números, se trata más de entender el flujo del dinero y la toma de decisiones.'],
        ['mito' => 'Solo se usa en grandes empresas', 'realidad' => 'Toda entidad la necesita', 'explicacion' => 'Incluso tus finanzas personales o una tienda pequeña se benefician enormemente.']
    ];
}

$stmt = $pdo->query("SELECT q.*, u.nombre_usuario FROM preguntas_expertos q JOIN usuarios u ON q.usuario_id = u.id WHERE q.estado = 'respondida' ORDER BY q.creado_en DESC");
$respuestasExpertos = $stmt->fetchAll();

$stmt = $pdo->query("SELECT u.nombre_usuario, s.puntos, s.nivel FROM usuarios u JOIN estadisticas_usuario s ON u.id = s.usuario_id ORDER BY s.puntos DESC LIMIT 10");
$ranking = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT nivel_conocimiento, rol FROM usuarios WHERE id = ?");
$stmt->execute([$idUsuario]);
$datosUsuario = $stmt->fetch();
$necesitaDiagnostico = ($datosUsuario['nivel_conocimiento'] == 'principiante' && !isset($_SESSION['diagnostico_completado']));

$totalLecciones = count($rutaAprendizaje);
$leccionesCompletadas = count(array_filter($rutaAprendizaje, function ($l) {
    return $l['estado'] === 'completado';
}));
$porcentajeProgreso = ($totalLecciones > 0) ? round(($leccionesCompletadas / $totalLecciones) * 100) : 0;
$rolUsuario = $datosUsuario['rol'] ?? 'estudiante';

$leccionCompletadaExito = false;
if (isset($_GET['completado'])) {
    $idL = (int)$_GET['completado'];
    if ($controladorDashboard->completarLeccion($idUsuario, $idL)) {
        $leccionCompletadaExito = true;
        $estadisticasEstudiante = $controladorDashboard->obtenerEstadisticasEstudiante($idUsuario);
    }
}

$listaTodosLosUsuarios = ($rolUsuario === 'admin') ? $controladorDashboard->obtenerListaUsuarios() : [];
$exitoExperto = isset($_GET['exito_experto']);
$exitoDiagnostico = isset($_GET['diagnostico']);
$alertaBloqueado = isset($_GET['bloqueado']);

$promedioPuntaje = 0;
$completadasConPuntos = array_filter($rutaAprendizaje, fn($l) => $l['estado'] === 'completado' && $l['puntaje'] > 0);
if (!empty($completadasConPuntos)) {
    $promedioPuntaje = round(array_sum(array_column($completadasConPuntos, 'puntaje')) / count($completadasConPuntos));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DebiHaby</title>
    <link rel="stylesheet" href="../public/css/styles.css">
    <link rel="stylesheet" href="../public/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="../public/assets/logo2.ico" type="image/x-icon">
</head>
<body class="dashboard-body">
    <div class="toast-container" id="toast-container"></div>

    <?php include '../configuracion/sidebar.php'; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="hamburger-btn" id="hamburger-btn"><i class="fas fa-bars"></i></button>
                <div class="header-welcome">
                    <h1>Hola, <?php echo htmlspecialchars($estadisticasEstudiante['nombre_completo']); ?> 👋</h1>
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
                    <span class="stat-value"><?php echo $estadisticasEstudiante['racha']; ?> días</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-icon"><i class="fas fa-gem" style="color: #03A9F4;"></i></span>
                    <span class="stat-value"><?php echo number_format($estadisticasEstudiante['puntos']); ?></span>
                </div>
                <div class="user-profile">
                    <img src="<?php echo $estadisticasEstudiante['avatar']; ?>" onerror="this.src='../public/assets/debi_pet.png'" alt="Avatar" class="avatar-small">
                    <div class="user-info">
                        <span class="level-badge">Nivel <?php echo $estadisticasEstudiante['nivel']; ?></span>
                    </div>
                </div>
            </div>
        </header>

        <section class="dashboard-container">
            <?php if ($leccionCompletadaExito): ?>
                <script>document.addEventListener('DOMContentLoaded', () => showToast('¡Felicidades! Lección completada. Has ganado +100 XP.', 'success'));</script>
            <?php
endif; ?>
            <?php if ($exitoExperto): ?>
                <script>document.addEventListener('DOMContentLoaded', () => showToast('¡Pregunta enviada! Un experto te responderá pronto.', 'info'));</script>
            <?php
endif; ?>
            <?php if ($exitoDiagnostico): ?>
                <script>document.addEventListener('DOMContentLoaded', () => showToast('¡Diagnóstico completado! Tu nivel ha sido actualizado.', 'success'));</script>
            <?php
endif; ?>
            <?php if ($alertaBloqueado): ?>
                <script>document.addEventListener('DOMContentLoaded', () => showToast('Esta lección aún está bloqueada.', 'warning'));</script>
            <?php
endif; ?>

            <div id="tab-dashboard" class="tab-content active">
                <?php if ($necesitaDiagnostico): ?>
                    <div class="card-premium mission-card mb-2">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <h3 style="margin:0;"><i class="fas fa-bullseye"></i> ¡Comienza tu Diagnóstico!</h3>
                                <p style="margin:0; opacity: 0.9; font-size: 0.9rem;">Evalúa tus conocimientos para personalizar tu ruta.</p>
                            </div>
                            <a href="diagnosis.php" class="btn btn-primary">Iniciar Ahora</a>
                        </div>
                    </div>
                <?php
endif; ?>

                <div class="dashboard-grid-main">
                    <div class="main-column" style="grid-column: 1 / -1;">
                        <div class="current-lesson-hero card-premium mb-2" style="min-height: 300px; display: flex; flex-direction: column; justify-content: center;">
                            <div class="hero-label">CONTINUAR APRENDIENDO</div>
                            <?php if ($leccionActual): ?>
                                <h1 style="font-size: 2.5rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($leccionActual['titulo']); ?></h1>
                                <p style="font-size: 1.1rem; max-width: 600px;"><?php echo htmlspecialchars($leccionActual['descripcion']); ?></p>
                                <div class="lesson-meta" style="margin: 1.5rem 0;">
                                    <span><i class="fas fa-tags"></i> <?php echo htmlspecialchars($leccionActual['categoria']); ?></span>
                                    <span><i class="fas fa-bolt"></i> +<?php echo $leccionActual['recompensa_xp']; ?> XP</span>
                                </div>
                                <a href="lesson.php?id=<?php echo $leccionActual['id']; ?>" class="btn btn-primary btn-lg" style="align-self: flex-start; padding: 1rem 2rem; font-size: 1.1rem;">
                                    <i class="fas fa-play"></i> Iniciar Lección
                                </a>
                            <?php
else: ?>
                                <h2>¡Has completado todas las lecciones!</h2>
                                <p>Excelente trabajo. Mantente atento a nuevas actualizaciones o repasa tus cursos anteriores.</p>
                                <a href="javascript:void(0)" onclick="switchTab('stats')" class="btn btn-secondary mt-1">Ver Mis Logros</a>
                            <?php
endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Módulo Mis Cursos -->
            <div id="tab-courses" class="tab-content">
                <div class="path-container" style="display: flex; flex-direction: column; gap: 2.5rem;">
                    <?php
$categoriasInfo = [
    'Activos' => ['icono' => 'fa-coins', 'color' => '#3b82f6'],
    'Pasivos' => ['icono' => 'fa-file-invoice-dollar', 'color' => '#10b981'],
    'Capital' => ['icono' => 'fa-vault', 'color' => '#ef4444'],
    'Estados Financieros' => ['icono' => 'fa-chart-pie', 'color' => '#8b5cf6'],
    'General' => ['icono' => 'fa-book', 'color' => '#64748b']
];

foreach ($categoriasInfo as $nomCat => $datosCat):
    $leccionesCat = array_filter($rutaAprendizaje, function ($l) use ($nomCat) {
        return $l['categoria'] === $nomCat;
    });
    if (empty($leccionesCat))
        continue;
?>
                        <div class="course-module">
                            <div class="module-header" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 2px solid #f0f0f0; padding-bottom: 0.5rem;">
                                <div class="module-icon" style="width: 50px; height: 50px; background: <?php echo $datosCat['color']; ?>20; color: <?php echo $datosCat['color']; ?>; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                    <i class="fas <?php echo $datosCat['icono']; ?>"></i>
                                </div>
                                <div>
                                    <h2 style="margin: 0; font-size: 1.4rem; color: #1a1a2e;">Módulo: <?php echo $nomCat; ?></h2>
                                    <p style="margin: 0; color: #636e72; font-size: 0.9rem;"><?php echo count($leccionesCat); ?> Lecciones disponibles</p>
                                </div>
                            </div>
                                  <div class="learning-path" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                                    <?php foreach ($leccionesCat as $leccion): 
                                        $isLocked = ($leccion['estado'] === 'bloqueado');
                                        $levelClass = $leccion['nivel'] ?? 'basico';
                                    ?>
                                        <div class="path-node <?php echo $levelClass; ?> <?php echo $leccion['estado']; ?>" 
                                             style="margin: 0; width: 100%; cursor: pointer;"
                                             onclick="if('<?php echo $leccion['estado']; ?>' !== 'bloqueado') window.location.href='lesson.php?id=<?php echo $leccion['id']; ?>'">
                                            <div class="node-circle" style="flex-shrink: 0;">
                                                <?php if ($leccion['estado'] === 'completado'): ?><i class="fas fa-check-circle"></i>
                                                <?php
                                                elseif ($leccion['estado'] === 'disponible'): ?><i class="fas <?php echo $leccion['clase_icono'] ?: 'fa-play'; ?>"></i>
                                                <?php
                                                else: ?><i class="fas fa-lock"></i><?php
                                                endif; ?>
                                            </div>
                                            <div class="node-content">
                                                <h3 style="font-size: 1.1rem;"><?php echo htmlspecialchars($leccion['titulo']); ?></h3>
                                                <p style="font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; margin-top: 0.2rem;">
                                                    <span class="badge badge-<?php echo $levelClass; ?>">
                                                        <?php echo $levelClass; ?>
                                                    </span>
                                                    • <?php echo $leccion['recompensa_xp']; ?> XP
                                                </p>
                                                <?php if ($leccion['estado'] === 'completado'): ?>
                                                    <span class="score-tag">Nota: <?php echo $leccion['puntaje']; ?>%</span>
                                                <?php
                                                endif; ?>
                                            </div>
                                        </div>
                                    <?php
                                    endforeach; ?>
                                </div>
                    </div>
                        <?php
endforeach; ?>
                </div>
            </div>

            <!-- Noticias -->
            <div id="tab-news" class="tab-content">
                <div class="card-premium">
                    <h2><i class="fas fa-newspaper"></i> Noticias Contables</h2>
                    <div class="news-list mt-2">
                        <?php foreach ($todasLasNoticias as $noticia): ?>
                            <div class="mini-course-item mb-1">
                                <div>
                                    <span class="badge"><?php echo htmlspecialchars($noticia['categoria']); ?></span>
                                    <h4 style="margin-top:0.5rem;"><?php echo htmlspecialchars($noticia['titulo']); ?></h4>
                                    <p style="color:var(--dark-light); font-size:0.9rem;"><?php echo htmlspecialchars($noticia['contenido']); ?></p>
                                </div>
                            </div>
                        <?php
endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Expertos -->
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
                            <?php foreach ($respuestasExpertos as $resp): ?>
                                <div class="activity-item mt-1" style="flex-direction:column; align-items:flex-start;">
                                    <p><strong><i class="fas fa-circle-question" style="color: var(--primary);"></i> <?php echo htmlspecialchars($resp['pregunta']); ?></strong></p>
                                    <p style="color: var(--success); margin-top: 5px; font-size: 0.9rem;"><i class="fas fa-circle-check"></i> <?php echo htmlspecialchars($resp['respuesta']); ?></p>
                                </div>
                            <?php
endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mitos -->
            <div id="tab-myths" class="tab-content">
                <div class="card-premium">
                    <h2 style="text-align: center;"><i class="fas fa-brain"></i> Mitos vs Realidades</h2>
                    <div class="dashboard-grid-main mt-2" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                        <?php foreach ($todosLosMitos as $m): ?>
                            <div class="card-premium" style="border-left: 4px solid var(--primary);">
                                <h4 style="color: var(--danger); font-size: 0.95rem;"><i class="fas fa-xmark-circle"></i> Mito: <?php echo htmlspecialchars($m['mito']); ?></h4>
                                <p style="margin: 5px 0;"><strong style="color: var(--success);"><i class="fas fa-check-circle"></i> Realidad:</strong> <?php echo htmlspecialchars($m['realidad']); ?></p>
                                <?php if (!empty($m['explicacion'])): ?><p style="font-size: 0.85rem; color: var(--dark-light);">💡 <?php echo htmlspecialchars($m['explicacion']); ?></p><?php
    endif; ?>
                            </div>
                        <?php
endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Certificación -->
            <div id="tab-certification" class="tab-content">
                <div class="card-premium text-center" style="max-width:600px; margin: 0 auto;">
                    <i class="fas fa-award" style="font-size:4rem; color:var(--primary); margin-bottom:1.5rem;"></i>
                    <h2>Tu Certificado</h2>
                    <p>Completa todos los módulos al 100% para obtenerlo.</p>
                    <div style="margin: 2rem 0;">
                        <div style="font-size: 3rem; font-weight:700; color:var(--primary);"><?php echo $porcentajeProgreso; ?>%</div>
                        <div class="progress-bar-container" style="max-width:400px; margin: 1rem auto; background:#eee;"><div class="progress-bar" style="width: <?php echo $porcentajeProgreso; ?>%; background:var(--gradient-primary);"></div></div>
                    </div>
                    <?php if ($porcentajeProgreso >= 100): ?>
                        <a href="generate_pdf.php" class="btn btn-primary"><i class="fas fa-download"></i> Descargar PDF</a>
                    <?php
else: ?>
                        <button class="btn btn-secondary" disabled style="opacity:0.6;"><i class="fas fa-lock"></i> No Disponible</button>
                    <?php
endif; ?>
                </div>
            </div>

            <!-- Calculadoras -->
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

            <!-- Estadísticas -->
            <div id="tab-stats" class="tab-content">
                <div class="dashboard-grid-main">
                    <div class="main-column">
                        <div class="card-premium">
                            <h3><i class="fas fa-chart-line"></i> Tu Desempeño</h3>
                            <div class="stats-overview mt-2">
                                <div class="stat-box card-premium"><span>Lecciones</span><h2><?php echo $leccionesCompletadas; ?>/<?php echo $totalLecciones; ?></h2></div>
                                <div class="stat-box card-premium"><span>Puntos</span><h2><?php echo number_format($estadisticasEstudiante['puntos']); ?></h2></div>
                                <div class="stat-box card-premium"><span>Nivel</span><h2><?php echo $estadisticasEstudiante['nivel']; ?></h2></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-column">
                        <div class="card-premium">
                            <h3><i class="fas fa-trophy"></i> Ranking Global</h3>
                            <div class="activity-list mt-1">
                                <?php foreach ($ranking as $idx => $r): ?>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <strong><?php echo($idx + 1); ?>. <?php echo htmlspecialchars($r['nombre_usuario']); ?></strong>
                                            <span>Nivel <?php echo $r['nivel']; ?></span>
                                        </div>
                                        <span class="activity-tag"><?php echo number_format($r['puntos']); ?> XP</span>
                                    </div>
                                <?php
                                endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($rolUsuario === 'admin'): ?>
            <div id="tab-admin" class="tab-content">
                <div class="card-premium">
                    <h2><i class="fas fa-shield-halved"></i> Panel Admin</h2>
                    <table class="admin-table mt-1">
                        <thead><tr><th>ID</th><th>Usuario</th><th>Rol</th><th>Puntos</th></tr></thead>
                        <tbody>
                            <?php foreach ($listaTodosLosUsuarios as $u): ?>
                                <tr><td>#<?php echo $u['id']; ?></td><td><?php echo htmlspecialchars($u['nombre_usuario']); ?></td><td><?php echo $u['rol']; ?></td><td><?php echo number_format($u['puntos']); ?></td></tr>
                            <?php
    endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
endif; ?>

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
