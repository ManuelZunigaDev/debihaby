<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth_check.php';
require_once 'controllers/ControladorCurso.php';
require_once 'controllers/ControladorUsuario.php';

// Proteger la página
protegerPagina();

$idLeccion = (int)($_GET['id'] ?? 1);
$clavePaso  = $_GET['paso'] ?? 'explicacion';
$idUsuario   = (int)$_SESSION['id_usuario'];

$controladorCurso = new ControladorCurso($pdo);
$controladorUsuario   = new ControladorUsuario($pdo);

// Verificar si la lección es accesible
$rutaAprendizaje = $controladorCurso->obtenerRutaAprendizaje($idUsuario);
$progresoLeccionActual = array_filter($rutaAprendizaje, fn($l) => $l['id'] == $idLeccion);
$datosProgreso = reset($progresoLeccionActual);

if (!$datosProgreso || $datosProgreso['estado'] === 'bloqueado') {
    header('Location: dashboard.php?bloqueado=1');
    exit;
}

$stmt = $pdo->prepare("
    SELECT l.*, c.categoria 
    FROM lecciones l 
    JOIN cursos c ON l.curso_id = c.id 
    WHERE l.id = ?
");
$stmt->execute([$idLeccion]);
$datosLeccion = $stmt->fetch();

if (!$datosLeccion) {
    header('Location: dashboard.php');
    exit;
}

$archivoContenido = 'db/lessons_content.json';
$todoElContenido = json_decode(file_get_contents($archivoContenido), true);
$contenidoLeccionActual = $todoElContenido[$idLeccion] ?? null;

if (!$contenidoLeccionActual) {
    $contenidoLeccionActual = [
        "title" => $datosLeccion['titulo'] ?? "Lección",
        "category" => $datosLeccion['categoria'] ?? "General",
        "pasos" => [
            "explicacion" => [
                "tipo" => "teoria",
                "contenido" => $datosLeccion['descripcion'] ?? "Aprende los conceptos fundamentales de esta lección."
            ],
            "evaluacion" => [
                "tipo" => "arrastrar",
                "contenido" => "Demuestra lo que has aprendido. Identifica los conceptos correctos.",
                "elementos" => [
                    ["texto" => "Concepto de " . ($datosLeccion['titulo'] ?? ''), "tipo" => "activo"],
                    ["texto" => "Distractor o Diferente", "tipo" => "pasivo"]
                ]
            ]
        ]
    ];
}

$pasos = $contenidoLeccionActual['pasos'] ?? $contenidoLeccionActual['steps'] ?? [];
foreach ($pasos as $k => &$p) {
    $p['tipo'] = $p['tipo'] ?? $p['type'] ?? 'teoria';
    if ($p['tipo'] === 'theory') $p['tipo'] = 'teoria';
    if ($p['tipo'] === 'drag_drop') $p['tipo'] = 'arrastrar';
    $p['contenido'] = $p['contenido'] ?? $p['content'] ?? '';
    
    if (isset($p['items'])) {
        $p['elementos'] = [];
        foreach ($p['items'] as $item) {
            $p['elementos'][] = [
                'texto' => $item['texto'] ?? $item['text'] ?? '',
                'tipo' => $item['tipo'] ?? $item['type'] ?? ''
            ];
        }
    }
}
$contenidoLeccionActual['pasos'] = $pasos;

// Validar paso y asegurar que exista
if (!isset($contenidoLeccionActual['pasos'][$clavePaso])) {
    $pasosDisponibles = array_keys($contenidoLeccionActual['pasos']);
    $clavePaso = !empty($pasosDisponibles) ? $pasosDisponibles[0] : null;
}

if (!$clavePaso) {
    header('Location: dashboard.php');
    exit;
}

$datosPaso = $contenidoLeccionActual['pasos'][$clavePaso];

$ordenPasos  = ['explicacion', 'evaluacion']; // Simplificado para que coincida con el JSON actual
$indicePaso  = array_search($clavePaso, $ordenPasos);
$siguienteClavePaso = ($indicePaso !== false && $indicePaso < count($ordenPasos) - 1) ? $ordenPasos[$indicePaso + 1] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($datosLeccion['titulo']); ?> - DebiHaby</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .lesson-container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        .game-area { background: white; border-radius: 24px; padding: 2.5rem; box-shadow: var(--shadow-lg); min-height: 500px; display: flex; flex-direction: column; position: relative; }
        .game-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid #f0f0f0; padding-bottom: 1rem; }
        .instruction-card { background: var(--gradient-card); padding: 1.5rem; border-radius: 16px; margin-bottom: 2rem; border-left: 5px solid var(--primary); }
        .elements-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem; margin-top: 1rem; }
        .drag-item { background: #f8f9fa; border: 2px dashed #ccc; padding: 1.5rem; border-radius: 12px; text-align: center; cursor: grab; transition: var(--transition); font-weight: 600; }
        .drag-item:hover { border-color: var(--primary); background: white; transform: scale(1.05); }
        .drop-zone { background: #fff; border: 3px solid #eee; border-radius: 20px; padding: 2rem; min-height: 200px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; align-content: flex-start; transition: var(--transition); }
        .drop-zone.active { border-color: var(--primary); background: var(--gradient-card); }
        .tag-success { background: #e8f5e9; color: #2e7d32; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; border: 1px solid #c8e6c9; }
        .game-footer { margin-top: auto; display: flex; justify-content: space-between; align-items: center; padding-top: 2rem; }
        #result-overlay { display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.9); z-index: 10; flex-direction: column; align-items: center; justify-content: center; border-radius: 24px; text-align: center; }
    </style>
</head>
<body class="dashboard-body">
    <script>
        const temaGuardado = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', temaGuardado);
    </script>

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="dashboard-header" style="margin-bottom: 0;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="hamburger-btn" id="hamburger-btn" aria-label="Abrir menú">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-welcome">
                    <a href="courses.php" style="text-decoration: none; color: var(--primary); font-weight: 600;"><i class="fas fa-arrow-left"></i> Volver a Mis Cursos</a>
                    <h1 style="margin-top: 0.5rem; font-size: 1.5rem;"><?php echo htmlspecialchars($datosLeccion['titulo']); ?></h1>
                </div>
            </div>
            <div class="header-stats">
                <div class="stat-pill">
                    <span class="stat-icon"><i class="fas fa-layer-group"></i></span>
                    <span class="stat-value">Categoría: <?php echo htmlspecialchars($datosLeccion['categoria'] ?? 'General'); ?></span>
                </div>
            </div>
        </header>

    <div class="lesson-container">
        <div class="game-area">
            <div id="result-overlay">
                <div class="success-icon" style="font-size: 5rem; color: #4CAF50; margin-bottom: 1rem;">
                    <i class="fas fa-circle-check"></i>
                </div>
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">¡Excelente!</h2>
                <p style="font-size: 1.2rem; margin-bottom: 2rem;">Has completado este reto correctamente.</p>
                <div class="stat-pill" style="margin-bottom: 2rem;">
                    <span class="stat-icon"><i class="fas fa-gem" style="color: #03A9F4;"></i></span>
                    <span class="stat-value">+100 Puntos ganados</span>
                </div>
                <a href="dashboard.php" class="btn btn-primary">Continuar mi Camino</a>
            </div>

            <div class="game-header">
                <h2><?php echo ucfirst($clavePaso); ?>: <?php echo htmlspecialchars($datosLeccion['titulo']); ?></h2>
                <div class="step-indicator">
                    Paso <?php echo ($indicePaso !== false) ? $indicePaso + 1 : '?'; ?> de <?php echo count($ordenPasos); ?>
                </div>
            </div>

            <div class="step-content">
                <?php if ($datosPaso['tipo'] == 'video'): ?>
                    <div class="instruction-card">
                        <h3><i class="fas fa-play-circle" style="color: var(--primary);"></i> Introducción</h3>
                        <p><?php echo nl2br(htmlspecialchars($datosPaso['contenido'])); ?></p>
                        <div class="video-container" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 16px; margin-top: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                            <iframe src="<?php echo htmlspecialchars($datosPaso['video_url']); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>
                        </div>
                    </div>
                <?php elseif (in_array($datosPaso['tipo'], ['teoria', 'escenario', 'practica'])): ?>
                    <div class="instruction-card">
                        <?php 
                        $claseIcono = 'fa-book-open';
                        if ($datosPaso['tipo'] == 'escenario') $claseIcono = 'fa-compass';
                        if ($datosPaso['tipo'] == 'teoria') $claseIcono = 'fa-lightbulb';
                        if ($datosPaso['tipo'] == 'practica') $claseIcono = 'fa-pencil-alt';
                        ?>
                        <h3><i class="fas <?php echo $claseIcono; ?>" style="color: var(--primary);"></i> Concepto Clave</h3>
                        <?php 
                        $textoFormateado = htmlspecialchars($datosPaso['contenido']);
                        $textoFormateado = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $textoFormateado);
                        echo '<p style="line-height: 1.8; font-size: 1.05rem;">' . nl2br($textoFormateado) . '</p>'; 
                        ?>
                    </div>
                <?php elseif ($datosPaso['tipo'] == 'arrastrar'): ?>
                    <div class="instruction-card">
                        <h3><i class="fas fa-gamepad" style="color: var(--primary);"></i> Reto Interactivo</h3>
                        <p><?php echo htmlspecialchars($datosPaso['contenido']); ?></p>
                        <div class="drag-drop-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                            <div class="elements-grid" style="grid-template-columns: 1fr;">
                                <h4 style="margin-bottom: 1rem; color: var(--dark-light);">Elementos:</h4>
                                <?php 
                                $elementosMezclados = $datosPaso['elementos'];
                                shuffle($elementosMezclados);
                                foreach ($elementosMezclados as $idx => $elemento): 
                                ?>
                                    <div class="drag-item" draggable="true" id="item-<?php echo $idx; ?>" data-tipo="<?php echo htmlspecialchars($elemento['tipo']); ?>">
                                        <?php echo htmlspecialchars($elemento['texto']); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="drop-area-container">
                                <?php $cantidadObjetivo = count(array_filter($elementosMezclados, fn($i) => $i['tipo'] === 'activo')); ?>
                                <h4 style="margin-bottom: 1rem; color: var(--success);"><i class="fas fa-box-open"></i> Cofre de Activos: (<span id="correct-count">0</span> / <?php echo $cantidadObjetivo; ?>)</h4>
                                <div class="drop-zone" id="assets-chest">
                                    <p class="drop-hint" style="color: #aaa; width: 100%; text-align: center; margin-top: 3rem;"><i class="fas fa-arrow-down"></i> Arrastra un activo aquí</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="game-footer">
                <?php if ($siguienteClavePaso): ?>
                    <a href="lesson.php?id=<?php echo $idLeccion; ?>&paso=<?php echo $siguienteClavePaso; ?>" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem; border-radius: 50px;">
                        Siguiente Paso <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                    </a>
                <?php else: ?>
                    <button id="finish-btn" class="btn btn-success" style="padding: 1rem 2rem; font-size: 1.1rem; border-radius: 50px; background: linear-gradient(135deg, #059669, #10b981); border:none;">
                        Finalizar Lección <i class="fas fa-check" style="margin-left: 0.5rem;"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const itemsArrastrables = document.querySelectorAll('.drag-item');
        const zonaSoltar = document.getElementById('assets-chest');
        const spanContador = document.getElementById('correct-count');
        const overlayExito = document.getElementById('result-overlay');
        const botonFinalizar = document.getElementById('finish-btn');
        
        let contadorCorrectos = 0;
        const totalNecesario = Array.from(itemsArrastrables).filter(i => i.dataset.tipo === 'activo').length;

        itemsArrastrables.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('tipo', item.dataset.tipo);
                e.dataTransfer.setData('texto', item.innerText);
                item.style.opacity = '0.5';
            });
            item.addEventListener('dragend', () => item.style.opacity = '1');
        });

        zonaSoltar.addEventListener('dragover', (e) => {
            e.preventDefault();
            zonaSoltar.classList.add('active');
        });

        zonaSoltar.addEventListener('dragleave', () => zonaSoltar.classList.remove('active'));

        zonaSoltar.addEventListener('drop', (e) => {
            e.preventDefault();
            zonaSoltar.classList.remove('active');
            const tipo = e.dataTransfer.getData('tipo');
            const texto = e.dataTransfer.getData('texto');

            if (tipo === 'activo') {
                const yaAgregado = Array.from(zonaSoltar.children).some(c => c.innerText === texto);
                if (!yaAgregado) {
                    const tag = document.createElement('div');
                    tag.className = 'tag-success';
                    tag.innerText = texto;
                    zonaSoltar.appendChild(tag);
                    const original = Array.from(itemsArrastrables).find(i => i.innerText === texto);
                    if (original) original.style.display = 'none';
                    contadorCorrectos++;
                    spanContador.innerText = contadorCorrectos;
                    if (contadorCorrectos === totalNecesario) {
                        setTimeout(() => overlayExito.style.display = 'flex', 500);
                    }
                }
            } else {
                Swal.fire({
                    title: '¡Oops!',
                    text: 'Eso parece una deuda u obligación (Pasivo). Intenta con un bien de la empresa.',
                    icon: 'warning',
                    confirmButtonColor: '#f59e0b'
                });
            }
        });

        if (botonFinalizar) {
            botonFinalizar.addEventListener('click', () => {
                const datosForm = new FormData();
                datosForm.append('id_leccion', <?php echo $idLeccion; ?>);
                fetch('controllers/actualizar_progreso.php', {
                    method: 'POST',
                    body: datosForm
                })
                .then(res => res.json())
                .then(dato => {
                    if (dato.exito) {
                        window.location.href = 'courses.php';
                    } else {
                        Swal.fire('Error', dato.mensaje || 'Error al guardar progreso', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error de conexión', 'error'));
            });
        }
    </script>
    </main>
</body>
</html>
