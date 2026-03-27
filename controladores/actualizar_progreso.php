<?php
session_start();
require_once '../configuracion/config.php';
require_once 'ControladorCurso.php';
require_once 'ControladorUsuario.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario']) || !isset($_POST['id_leccion'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'No autorizado o faltan datos']);
    exit;
}

try {
    $idUsuario = (int)$_SESSION['id_usuario'];
    $idLeccion = (int)$_POST['id_leccion'];

    $controladorCurso = new ControladorCurso($pdo);
    $controladorUsuario = new ControladorUsuario($pdo);

    $exito = $controladorCurso->completarLeccion($idUsuario, $idLeccion);

    if ($exito) {
        $stmt = $pdo->prepare("SELECT recompensa_xp FROM lecciones WHERE id = ?");
        $stmt->execute([$idLeccion]);
        $xp = $stmt->fetchColumn() ?: 100;
        
        $controladorUsuario->agregarXP($idUsuario, $xp);
        $controladorUsuario->actualizarRacha($idUsuario);
    }

    echo json_encode(['exito' => true, 'mensaje' => 'Progreso guardado correctamente']);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => 'Excepción del servidor: ' . $e->getMessage()]);
}
