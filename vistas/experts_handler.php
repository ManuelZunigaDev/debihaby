<?php
session_start();
require_once '../configuracion/config.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pregunta = trim($_POST['question'] ?? '');
    $idUsuario = (int)$_SESSION['id_usuario'];

    if (!empty($pregunta)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO preguntas_expertos (usuario_id, pregunta, estado, creado_en) VALUES (?, ?, 'pendiente', NOW())");
            $stmt->execute([$idUsuario, $pregunta]);
            header('Location: dashboard.php?exito_experto=1');
            exit;
        }
        catch (PDOException $e) {
        // EXCEPCIONES PEDOORAS
        }
    }
}

header('Location: dashboard.php');
exit;
