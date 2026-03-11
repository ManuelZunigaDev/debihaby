<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lógica de calificación de la evaluación final
    $score = 85; // Simulado para el ejemplo
    
    if ($score >= 70) {
        $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, lesson_id, status, score, completed_at) VALUES (?, 99, 'completed', ?, NOW()) ON DUPLICATE KEY UPDATE status='completed', score=?");
        $stmt->execute([$userId, $score, $score]);
        header('Location: certification.php');
    } else {
        $error = "No has alcanzado la puntuación mínima (70%). Inténtalo de nuevo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evaluación Final - DebiHaby</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .exam-container { max-width: 800px; margin: 3rem auto; padding: 2rem; background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .sim-box { border: 2px solid #ddd; padding: 1.5rem; border-radius: 12px; margin: 1.5rem 0; }
    </style>
</head>
<body>
    <div class="exam-container">
        <h1>🏆 Evaluación Final</h1>
        <p>Demuestra lo aprendido gestionando la contabilidad de una empresa virtual.</p>
        
        <form method="POST">
            <div class="sim-box">
                <h3>Caso Práctico: "La Tiendita de Debi"</h3>
                <p>La tienda compró mercancía por $5,000 en efectivo. ¿Cómo registrarías este asiento?</p>
                <select name="q1" class="form-control">
                    <option value="1">Cargo a Almacén, Abono a Caja</option>
                    <option value="2">Cargo a Caja, Abono a Almacén</option>
                    <option value="3">Cargo a Gastos, Abono a Bancos</option>
                </select>
            </div>
            <!-- Más preguntas simuladas -->
            <button type="submit" class="btn btn-primary" style="width: 100%;">Enviar Evaluación</button>
        </form>
    </div>
</body>
</html>
