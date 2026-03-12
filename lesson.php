<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$lessonId = (int)($_GET['id'] ?? 1);
$stepKey  = $_GET['step'] ?? 'enganche';
$userId   = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT status FROM user_progress WHERE user_id = ? AND lesson_id = ?");
$stmt->execute([$userId, $lessonId]);
$progressData = $stmt->fetch();

if (!$progressData || $progressData['status'] === 'locked') {
    header('Location: dashboard.php?locked=1');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$lessonId]);
$lessonData = $stmt->fetch();

if (!$lessonData) {
    header('Location: dashboard.php');
    exit;
}

$contentFile = 'db/lessons_content.json';
$allLessonsContent = json_decode(file_get_contents($contentFile), true);
$currentLessonContent = $allLessonsContent[$lessonId] ?? null;

if (!$currentLessonContent) {
    header('Location: dashboard.php');
    exit;
}

$stepData = $currentLessonContent['steps'][$stepKey] ?? null;
if (!$stepData) {
    $stepKey  = 'enganche';
    $stepData = $currentLessonContent['steps'][$stepKey];
}

$stepOrder  = ['enganche', 'exploracion', 'explicacion', 'elaboracion', 'evaluacion'];
$stepIndex  = array_search($stepKey, $stepOrder);
$nextStepKey = ($stepIndex < count($stepOrder) - 1) ? $stepOrder[$stepIndex + 1] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lessonData['title']); ?> - DebiHaby</title>
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
<body class="dashboard-body" style="display: block;">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    
    <header class="dashboard-header lesson-container" style="margin-bottom: 0;">
        <div class="header-welcome">
            <a href="dashboard.php" style="text-decoration: none; color: var(--primary); font-weight: 600;"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>
            <h1 style="margin-top: 1rem;"><?php echo htmlspecialchars($lessonData['title']); ?></h1>
        </div>
        <div class="header-stats">
            <div class="stat-pill">
                <span class="stat-icon"><i class="fas fa-layer-group"></i></span>
                <span class="stat-value">Nivel: <?php echo htmlspecialchars($lessonData['category']); ?></span>
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
                <h2><?php echo ucfirst($stepKey); ?>: <?php echo htmlspecialchars($lessonData['title']); ?></h2>
                <div class="step-indicator">
                    Paso <?php echo $stepIndex + 1; ?> de 5
                </div>
            </div>

            <div class="step-content">
                <?php if ($stepData['type'] == 'video'): ?>
                    <div class="instruction-card">
                        <h3><i class="fas fa-play-circle" style="color: var(--primary);"></i> Introducción</h3>
                        <p><?php echo nl2br(htmlspecialchars($stepData['content'])); ?></p>
                        <div class="video-container" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 16px; margin-top: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                            <iframe src="<?php echo htmlspecialchars($stepData['video_url']); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>
                        </div>
                    </div>
                <?php elseif (in_array($stepData['type'], ['scenario', 'theory', 'practice'])): ?>
                    <div class="instruction-card">
                        <?php 
                        $iconClass = 'fa-book-open';
                        if ($stepData['type'] == 'scenario') $iconClass = 'fa-compass';
                        if ($stepData['type'] == 'theory') $iconClass = 'fa-lightbulb';
                        if ($stepData['type'] == 'practice') $iconClass = 'fa-pencil-alt';
                        ?>
                        <h3><i class="fas <?php echo $iconClass; ?>" style="color: var(--primary);"></i> Concepto Clave</h3>
                        <?php 
                        $formattedText = htmlspecialchars($stepData['content']);
                        $formattedText = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formattedText);
                        echo '<p style="line-height: 1.8; font-size: 1.05rem;">' . nl2br($formattedText) . '</p>'; 
                        ?>
                    </div>
                <?php elseif ($stepData['type'] == 'drag_drop'): ?>
                    <div class="instruction-card">
                        <h3><i class="fas fa-gamepad" style="color: var(--primary);"></i> Reto Interactivo</h3>
                        <p><?php echo htmlspecialchars($stepData['content']); ?></p>
                        <div class="drag-drop-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                            <div class="elements-grid" style="grid-template-columns: 1fr;">
                                <h4 style="margin-bottom: 1rem; color: var(--dark-light);">Elementos:</h4>
                                <?php 
                                $shuffledItems = $stepData['items'];
                                shuffle($shuffledItems);
                                foreach ($shuffledItems as $idx => $item): 
                                ?>
                                    <div class="drag-item" draggable="true" id="item-<?php echo $idx; ?>" data-type="<?php echo htmlspecialchars($item['type']); ?>">
                                        <?php echo htmlspecialchars($item['text']); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="drop-area-container">
                                <?php $targetCount = count(array_filter($shuffledItems, fn($i) => $i['type'] === 'activo')); ?>
                                <h4 style="margin-bottom: 1rem; color: var(--success);"><i class="fas fa-box-open"></i> Cofre de Activos: (<span id="correct-count">0</span> / <?php echo $targetCount; ?>)</h4>
                                <div class="drop-zone" id="assets-chest">
                                    <p class="drop-hint" style="color: #aaa; width: 100%; text-align: center; margin-top: 3rem;"><i class="fas fa-arrow-down"></i> Arrastra un activo aquí</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="game-footer">
                <?php if ($nextStepKey): ?>
                    <a href="lesson.php?id=<?php echo $lessonId; ?>&step=<?php echo $nextStepKey; ?>" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem; border-radius: 50px;">
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
        const dragItems = document.querySelectorAll('.drag-item');
        const dropZone = document.getElementById('assets-chest');
        const countSpan = document.getElementById('correct-count');
        const successOverlay = document.getElementById('result-overlay');
        const finishBtn = document.getElementById('finish-btn');
        
        let correctCount = 0;
        const totalNeeded = Array.from(dragItems).filter(i => i.dataset.type === 'activo').length;

        dragItems.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('type', item.dataset.type);
                e.dataTransfer.setData('text', item.innerText);
                item.style.opacity = '0.5';
            });
            item.addEventListener('dragend', () => item.style.opacity = '1');
        });

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('active');
        });

        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('active'));

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('active');
            const type = e.dataTransfer.getData('type');
            const text = e.dataTransfer.getData('text');

            if (type === 'activo') {
                const alreadyAdded = Array.from(dropZone.children).some(c => c.innerText === text);
                if (!alreadyAdded) {
                    const tag = document.createElement('div');
                    tag.className = 'tag-success';
                    tag.innerText = text;
                    dropZone.appendChild(tag);
                    const original = Array.from(dragItems).find(i => i.innerText === text);
                    if (original) original.style.display = 'none';
                    correctCount++;
                    countSpan.innerText = correctCount;
                    if (correctCount === totalNeeded) {
                        setTimeout(() => successOverlay.style.display = 'flex', 500);
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

        if (finishBtn) {
            finishBtn.addEventListener('click', () => {
                const formData = new FormData();
                formData.append('lesson_id', <?php echo $lessonId; ?>);
                fetch('controllers/progress_update.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'dashboard.php?completed=<?php echo $lessonId; ?>';
                    } else {
                        Swal.fire('Error', data.message || 'Error al guardar progreso', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error de conexión', 'error'));
            });
        }
    </script>
</body>
</html>
