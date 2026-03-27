<?php
session_start();
require_once '../configuracion/config.php';
require_once '../configuracion/verificar_sesion.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$idUsuario = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lógica de calificación (simulada o dinámica)
    $puntaje = 85; 
    
    if ($puntaje >= 70) {
        $stmt = $pdo->prepare("
            INSERT INTO progreso_usuario (usuario_id, leccion_id, estado, puntaje, completado_en) 
            VALUES (?, 99, 'completado', ?, NOW()) 
            ON DUPLICATE KEY UPDATE estado='completado', puntaje=?, completado_en=NOW()
        ");
        $stmt->execute([$idUsuario, $puntaje, $puntaje]);
        header('Location: certification.php');
        exit;
    } else {
        $error = "No has alcanzado el puntaje mínimo (70%). Inténtalo de nuevo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evaluación Final - DebiHaby</title>
    <link rel="stylesheet" href="../public/css/styles.css">
    <style>
        .exam-container { max-width: 800px; margin: 3rem auto; padding: 2rem; background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .sim-box { border: 2px solid #ddd; padding: 1.5rem; border-radius: 12px; margin: 1.5rem 0; }
    </style>
</head>
<body>
    <div class="exam-container">
        <h1>🏆 Evaluación Final</h1>
        <p>Demuestra lo aprendido gestionando la contabilidad de una empresa virtual.</p>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="sim-box">
                <h3>Caso Práctico: "La Tiendita de Debi"</h3>
                <p>La tienda compró mercancía por $5,000 en efectivo. ¿Cómo registrarías este asiento?</p>
                <select name="q1" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px;">
                    <option value="1">Cargo a Almacén, Abono a Caja</option>
                    <option value="2">Cargo a Caja, Abono a Almacén</option>
                    <option value="3">Cargo a Gastos, Abono a Bancos</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-weight: bold;">Enviar Evaluación</button>
        </form>
    </div>
</body>
</html>
