<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth_check.php';

// Asegurar la página
protegerPagina();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $idUsuario = $_SESSION['id_usuario'];
    $pregunta = trim($_POST['question']);
    
    if (!empty($pregunta)) {
        $stmt = $pdo->prepare("INSERT INTO preguntas_expertos (usuario_id, pregunta, estado, creado_en) VALUES (?, ?, 'pendiente', NOW())");
        $stmt->execute([$idUsuario, $pregunta]);
        
        // Redirigir al dashboard con mensaje de éxito
        header('Location: dashboard.php?exito_experto=1#experts');
        exit;
    }
}

header('Location: dashboard.php');
exit;
