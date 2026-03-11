<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$questions = json_decode(file_get_contents('db/diagnosis_questions.json'), true);
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $totalPoints = 0;
    foreach ($questions as $q) {
        $totalPoints += $q['points'];
        if (isset($_POST['q' . $q['id']]) && $_POST['q' . $q['id']] == $q['answer']) {
            $score += $q['points'];
        }
    }

    $percentage = ($score / $totalPoints) * 100;
    $level = 'principiante';
    if ($percentage >= 80) $level = 'intermedio';
    elseif ($percentage >= 50) $level = 'intermedio_basico';

    // Update user level
    $stmt = $pdo->prepare("UPDATE users SET knowledge_level = ? WHERE id = ?");
    $stmt->execute([$level, $userId]);

    // Update stats (bonus points for diagnostic)
    $stmt = $pdo->prepare("UPDATE user_stats SET points = points + ? WHERE user_id = ?");
    $stmt->execute([$score, $userId]);

    $_SESSION['diagnosis_completed'] = true;
    $_SESSION['assigned_level'] = $level;
    header('Location: dashboard.php?diagnosis=success');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Inicial - DebiHaby</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .diagnosis-container { max-width: 800px; margin: 4rem auto; padding: 2rem; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .question-card { margin-bottom: 2rem; padding: 1.5rem; border-left: 5px solid var(--primary); background: #f9f9f9; border-radius: 0 10px 10px 0; }
        .options { display: grid; gap: 1rem; margin-top: 1rem; }
        .option { padding: 1rem; background: white; border: 2px solid #eee; border-radius: 8px; cursor: pointer; transition: 0.3s; }
        .option:hover { border-color: var(--primary); background: #f0f7ff; }
        input[type="radio"] { margin-right: 10px; }
        .btn-submit { display: block; width: 100%; padding: 1rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <div class="diagnosis-container">
        <h1>🔍 Diagnóstico de Conocimientos</h1>
        <p>Responde estas preguntas para que Debi pueda asignarte la mejor ruta de aprendizaje.</p>
        <hr><br>
        
        <form method="POST">
            <?php foreach ($questions as $q): ?>
                <div class="question-card">
                    <h3><?php echo $q['question']; ?></h3>
                    <div class="options">
                        <?php foreach ($q['options'] as $index => $option): ?>
                            <label class="option">
                                <input type="radio" name="q<?php echo $q['id']; ?>" value="<?php echo $index; ?>" required>
                                <?php echo $option; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn-submit">Finalizar Diagnóstico</button>
        </form>
    </div>
</body>
</html>
