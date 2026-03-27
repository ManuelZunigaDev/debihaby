<?php
class RepositorioUsuario {
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    public function obtenerPorId($id) {
        $stmt = $this->pdo->prepare("SELECT u.*, s.puntos, s.nivel, s.racha, s.id_ultima_leccion FROM usuarios u JOIN estadisticas_usuario s ON u.id = s.usuario_id WHERE u.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function actualizarPuntos($id, $puntos) {
        $stmt = $this->pdo->prepare("UPDATE estadisticas_usuario SET puntos = puntos + ? WHERE usuario_id = ?");
        return $stmt->execute([$puntos, $id]);
    }

    public function actualizarUltimaLeccion($id, $leccionId) {
        $stmt = $this->pdo->prepare("UPDATE estadisticas_usuario SET id_ultima_leccion = ? WHERE usuario_id = ?");
        return $stmt->execute([$leccionId, $id]);
    }
}
