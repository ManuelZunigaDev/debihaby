<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

require_once '../configuracion/config.php';
require_once '../configuracion/verificar_sesion.php';
require_once '../controladores/ControladorCurso.php';
require_once '../controladores/ControladorUsuario.php';

protegerPagina();

$idUsuario = $_SESSION['id_usuario'] ?? null;

if (!$idUsuario) {
    header("Location: login.php");
    exit;
}

$controladorCurso = new ControladorCurso($pdo);
$controladorUsuario = new ControladorUsuario($pdo);

$estadisticasEstudiante = $controladorUsuario->obtenerPerfilUsuario($idUsuario);
$paginaActual = 'courses';


// Obtener cursos
$cursos = $controladorCurso->obtenerTodosLosCursos($idUsuario) ?? [];


$rutaAprendizaje = $controladorCurso->obtenerRutaAprendizaje($idUsuario) ?? [];


$leccionesPorCurso = [];

foreach ($rutaAprendizaje as $leccion) {

    $cursoId = $leccion['curso_id'];

    if (!isset($leccionesPorCurso[$cursoId])) {
        $leccionesPorCurso[$cursoId] = [];
    }

    $leccionesPorCurso[$cursoId][] = $leccion;
}



$totalLecciones = count($rutaAprendizaje);

$leccionesCompletadas = count(
    array_filter($rutaAprendizaje, function ($l) {
        return $l['estado'] === 'completado';
    })
);

$porcentajeProgreso = 0;

if ($totalLecciones > 0) {
    $porcentajeProgreso = round(($leccionesCompletadas / $totalLecciones) * 100);
}



$siguienteLeccion = null;

foreach ($rutaAprendizaje as $l) {

    if ($l['estado'] === 'disponible') {

        $siguienteLeccion = $l;
        break;
    }

}



$rolUsuario = $_SESSION['rol_usuario'] ?? 'estudiante';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Cursos - DebiHaby</title>
    <link rel="icon" href="../public/assets/logo2.ico" type="image/x-icon">
    <link rel="stylesheet" href="../public/css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../public/css/dashboard.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .courses-hero {
            background: linear-gradient(135deg, #fff8f0 0%, #ffffff 100%);
            border: 1px solid #f1e8dc;
            border-radius: 20px;
            padding: 1.75rem 2rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .courses-hero h2 { margin: 0 0 0.25rem; font-size: 1.4rem; }
        .courses-hero p  { margin: 0; color: var(--dark-light); font-size: 0.9rem; }

        .overall-progress-bar {
            height: 8px;
            background: #f1f5f9;
            border-radius: 99px;
            overflow: hidden;
            margin-top: 0.5rem;
            max-width: 300px;
        }
        .overall-progress-bar .fill {
            height: 100%;
            background: var(--gradient-primary, linear-gradient(90deg, #FF9800, #FF5722));
            border-radius: 99px;
        }
        .module-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            margin-bottom: 1.25rem;
            overflow: hidden;
            border: 1px solid #f1f5f9;
            transition: box-shadow 0.2s;
        }
        .module-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.09); }
        .module-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid #f8fafc;
            cursor: pointer;
            user-select: none;
            transition: background 0.2s;
        }
        .module-header:hover { background: #fafafa; }
        .module-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            flex-shrink: 0;
        }
        .module-meta { flex: 1; }
        .module-meta h3 { margin: 0 0 0.2rem; font-size: 1.05rem; }
        .module-meta p  { margin: 0; font-size: 0.82rem; color: var(--dark-light); }
        .module-progress-text { font-size: 0.82rem; font-weight: 600; color: var(--primary, #FF9800); }
        .module-mini-bar {
            width: 80px; height: 6px;
            background: #f1f5f9;
            border-radius: 99px;
            overflow: hidden;
            margin-top: 4px;
        }
        .module-mini-bar .fill { height: 100%; border-radius: 99px; }
        .module-toggle-icon { color: #94a3b8; font-size: 0.85rem; transition: transform 0.25s; }
        .module-card.open .module-toggle-icon { transform: rotate(180deg); }

        .lessons-list { display: none; padding: 0.75rem 1.75rem 1.25rem; }
        .module-card.open .lessons-list { display: block; }

        .lesson-row {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
            margin-bottom: 0.25rem;
        }
        .lesson-row:hover { background: #f8fafc; }
        .lesson-row.locked { cursor: default; opacity: 0.45; pointer-events: none; }

        .lesson-status-icon {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; flex-shrink: 0;
        }
        .lesson-row.completed .lesson-status-icon 
        { background: #dcfce7; color: #16a34a; }
        .lesson-row.available  .lesson-status-icon 
        { background: #fff7ed; color: var(--primary, #FF9800); }
        .lesson-row.locked     .lesson-status-icon { background: #f1f5f9; color: #cbd5e1; }

        .lesson-info { flex: 1; }
        .lesson-info strong { display: block; font-size: 0.92rem; }
        .lesson-info span   { font-size: 0.78rem; color: var(--dark-light); }
        .lesson-xp {
            font-size: 0.78rem; font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 99px;
            background: #fff7ed; color: var(--primary, #FF9800);
            flex-shrink: 0;
        }
        .lesson-row.completed .lesson-xp { background: #dcfce7; color: #16a34a; }
        .lesson-row.locked    .lesson-xp { background: #f1f5f9; color: #94a3b8; }

        .no-courses-msg {
            text-align:center; padding: 3rem 2rem;
            color: var(--dark-light);
        }
    </style>
</head>
<body class="dashboard-body">

    <?php include '../configuracion/sidebar.php'; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="hamburger-btn" id="hamburger-btn" aria-label="Abrir menú">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-welcome">
                    <h1>Mis Cursos <i class="fas fa-graduation-cap" style="color: var(--primary); font-size: 1.4rem;"></i></h1>
                    <p>Tu ruta de aprendizaje completa · <?php echo count($cursos); ?> módulos</p>
                </div>
            </div>
            <div class="header-stats">
                <button id="theme-toggle" class="stat-pill" style="cursor:pointer; border:none;">
                    <span class="stat-icon"><i class="fas fa-moon"></i></span>
                    <span class="stat-value">Modo</span>
                </button>
                <div class="stat-pill" title="Puntos DebiHaby">
                    <span class="stat-icon"><i class="fas fa-gem" style="color: #03A9F4;"></i></span>
                    <span class="stat-value"><?php echo number_format($estadisticasEstudiante['puntos']); ?></span>
                </div>
                <div class="stat-pill" title="Lecciones completadas">
                    <span class="stat-icon"><i class="fas fa-check-circle" style="color: #4CAF50;"></i></span>
                    <span class="stat-value"><?php echo $leccionesCompletadas; ?>/<?php echo $totalLecciones; ?></span>
                </div>
            </div>
        </header>

        <section class="dashboard-container">

            <div class="courses-hero">
                <div>
                    <h2><i class="fas fa-map-marked-alt" style="color: var(--primary);"></i> Ruta de Aprendizaje</h2>
                    <p><?php echo $leccionesCompletadas; ?> de <?php echo $totalLecciones; ?> lecciones completadas</p>
                    <div class="overall-progress-bar">
                        <div class="fill" style="width: <?php echo $porcentajeProgreso; ?>%;"></div>
                    </div>
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    <?php if ($siguienteLeccion): ?>
                        <a href="lesson.php?id=<?php echo $siguienteLeccion['id']; ?>" class="btn btn-primary" style="padding: 0.65rem 1.5rem;">
                            <i class="fas fa-play"></i> Continuar
                        </a>
                    <?php
endif; ?>
                    <a href="dashboard.php" class="btn btn-secondary" style="padding: 0.65rem 1.25rem; border-color: var(--primary); color: var(--primary);">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                </div>
            </div>

            <?php if (empty($cursos)): ?>
                <div class="card-premium no-courses-msg">
                    <i class="fas fa-graduation-cap" style="font-size: 3rem; color: var(--primary); display: block; margin-bottom: 1rem;"></i>
                    <p>No hay cursos disponibles aún.</p>
                </div>
            <?php
else: ?>
                <?php foreach ($cursos as $curso):
        $idC = $curso['id'];
        $leccionesCurso = $leccionesPorCurso[$idC] ?? [];

        $totalC = count($leccionesCurso);
        $completadasC = count(array_filter($leccionesCurso, fn($l) => $l['estado'] === 'completado'));
        $porcentajeC = ($totalC > 0) ? round(($completadasC / $totalC) * 100) : 0;

        $tieneDisponibles = count(array_filter($leccionesCurso, fn($l) => $l['estado'] === 'disponible')) > 0;
        $todoListo = ($totalC > 0 && $completadasC === $totalC);
        $estaAbierto = $tieneDisponibles;
?>
                <div class="module-card <?php echo $estaAbierto ? 'open' : ''; ?>" id="module-<?php echo $idC; ?>">
                    <div class="module-header" onclick="toggleModule(<?php echo $idC; ?>)">
                        <div class="module-icon" style="background: <?php echo htmlspecialchars($curso['color'] ?? '#FF9800'); ?>;">
                            <i class="fas <?php echo htmlspecialchars($curso['icono'] ?: 'fa-book'); ?>"></i>
                        </div>
                        <div class="module-meta">
                            <h3><?php echo htmlspecialchars($curso['titulo']); ?></h3>
                            <p><?php echo htmlspecialchars($curso['descripcion']); ?></p>
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.4rem;">
                                <div class="module-mini-bar">
                                    <div class="fill" style="width: <?php echo $porcentajeC; ?>%; background: <?php echo htmlspecialchars($curso['color'] ?? '#FF9800'); ?>;"></div>
                                </div>
                                <span class="module-progress-text"><?php echo $completadasC; ?>/<?php echo $totalC; ?> lecciones</span>
                                <?php if ($todoListo): ?>
                                    <span style="font-size: 0.78rem; background: #dcfce7; color: #16a34a; padding: 0.15rem 0.6rem; border-radius: 99px; font-weight: 600;">✓ Completado</span>
                                <?php
        elseif ($tieneDisponibles): ?>
                                    <span style="font-size: 0.78rem; background: #fff7ed; color: var(--primary); padding: 0.15rem 0.6rem; border-radius: 99px; font-weight: 600;">En progreso</span>
                                <?php
        elseif ($completadasC === 0): ?>
                                    <span style="font-size: 0.78rem; background: #f1f5f9; color: #94a3b8; padding: 0.15rem 0.6rem; border-radius: 99px;">Bloqueado</span>
                                <?php
        endif; ?>
                            </div>
                        </div>
                        <span class="badge" style="background: <?php echo htmlspecialchars($curso['color'] ?? '#FF9800'); ?>22; color: <?php echo htmlspecialchars($curso['color'] ?? '#FF9800'); ?>; margin-right: 0.5rem;">
                            <?php echo htmlspecialchars($curso['categoria'] ?? ''); ?>
                        </span>
                        <i class="fas fa-chevron-down module-toggle-icon"></i>
                    </div>

                    <div class="lessons-list">
                        <?php if (empty($leccionesCurso)): ?>
                            <p style="color: var(--dark-light); text-align: center; padding: 1rem 0;">Sin lecciones disponibles.</p>
                        <?php
        else: ?>
                             <?php foreach ($leccionesCurso as $leccion):
                                $claseEstado = $leccion['estado'];
                                $levelClass = $leccion['nivel'] ?? 'basico';
                                $isLocked = ($claseEstado === 'bloqueado');
                                $claseIcono = $claseEstado === 'completado' ? 'fa-check' : ($claseEstado === 'disponible' ? 'fa-play' : 'fa-lock');
                                $enlace = (!$isLocked) ? "lesson.php?id={$leccion['id']}" : 'javascript:void(0)';
                            ?>
                             <a href="<?php echo $enlace; ?>" class="lesson-row <?php echo $levelClass; ?> <?php echo $claseEstado; ?> <?php echo ($isLocked ? 'locked' : ''); ?>">
                                <div class="lesson-status-icon">
                                    <i class="fas <?php echo $claseIcono; ?>"></i>
                                </div>
                                <div class="lesson-info">
                                    <strong><?php echo htmlspecialchars($leccion['titulo']); ?></strong>
                                    <span class="badge badge-<?php echo $levelClass; ?>"><?php echo $levelClass; ?></span>
                                    <span><?php echo htmlspecialchars($leccion['descripcion'] ?? ''); ?></span>
                                </div>
                                <span class="lesson-xp">
                                    <?php echo $claseEstado === 'completado' ? '+' . $leccion['recompensa_xp'] . ' XP ✓' : $leccion['recompensa_xp'] . ' XP'; ?>
                                </span>
                            </a>
                            <?php
                            endforeach; ?>
                        <?php
        endif; ?>
                    </div>
                </div>
                <?php
    endforeach; ?>
            <?php
endif; ?>

        </section>
    </main>

    <script>
        const htmlRoot = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlRoot.setAttribute('data-theme', savedTheme);

        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            const icon = themeToggle.querySelector('i');
            icon.className = savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            themeToggle.addEventListener('click', () => {
                const current = htmlRoot.getAttribute('data-theme');
                const next = current === 'light' ? 'dark' : 'light';
                htmlRoot.setAttribute('data-theme', next);
                localStorage.setItem('theme', next);
                icon.className = next === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            });
        }

        function toggleModule(courseId) {
            const card = document.getElementById('module-' + courseId);
            if (card) card.classList.toggle('open');
        }

        function switchTab(tabId) {
            console.log('switchTab called in courses.php for:', tabId);
            if (tabId === 'courses') {
                return; 
            }
            window.location.href = 'dashboard.php#' + tabId;
        }

        const hamburgerBtn = document.getElementById('hamburger-btn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        if (hamburgerBtn) {
            hamburgerBtn.addEventListener('click', () => {
                sidebar.classList.toggle('open');
                sidebarOverlay?.classList.toggle('active');
            });
            sidebarOverlay?.addEventListener('click', () => {
                sidebar.classList.remove('open');
                sidebarOverlay?.classList.remove('active');
            });
        }
    </script>
</body>
</html>

/**ECHO  */
