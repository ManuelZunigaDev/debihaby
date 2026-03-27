<?php
class RepositorioCurso {
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    public function obtenerLeccionesDeCurso($cursoId) {
        $stmt = $this->pdo->prepare("SELECT * FROM lecciones WHERE curso_id = ? ORDER BY indice_orden ASC");
        $stmt->execute([$cursoId]);
        return $stmt->fetchAll();
    }

    public function obtenerProgreso($idUsuario) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, IFNULL(p.estado, 'bloqueado') as estado, IFNULL(p.puntaje, 0) as puntaje 
            FROM lecciones l 
            LEFT JOIN progreso_usuario p ON l.id = p.leccion_id AND p.usuario_id = ? 
            ORDER BY l.indice_orden ASC
        ");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    public function actualizarEstado($idUsuario, $idLeccion, $estado) {
        $stmt = $this->pdo->prepare("INSERT INTO progreso_usuario (usuario_id, leccion_id, estado) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE estado = ?");
        return $stmt->execute([$idUsuario, $idLeccion, $estado, $estado]);
    }
}
