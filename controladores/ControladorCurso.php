<?php
require_once __DIR__ . '/../configuracion/config.php';

class ControladorCurso
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerTodosLosCursos($idUsuario)
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, 
            (SELECT COUNT(*) FROM lecciones WHERE curso_id = c.id) as total_lecciones,
            (SELECT COUNT(*) FROM progreso_usuario up 
             JOIN lecciones l ON up.leccion_id = l.id 
             WHERE l.curso_id = c.id AND up.usuario_id = ? AND up.estado = 'completado') as lecciones_completadas
            FROM cursos c
            ORDER BY c.indice_orden ASC
        ");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    public function obtenerLeccionActual($idUsuario)
    {
        $stmt = $this->pdo->prepare("
            SELECT l.*, up.estado 
            FROM lecciones l
            JOIN progreso_usuario up ON l.id = up.leccion_id 
            WHERE up.usuario_id = ? AND up.estado = 'disponible'
            ORDER BY l.indice_orden ASC
            LIMIT 1
        ");
        $stmt->execute([$idUsuario]);
        $actual = $stmt->fetch();

        if (!$actual) {
            $stmt = $this->pdo->prepare("
                SELECT l.*, 'disponible' as estado 
                FROM lecciones l
                LEFT JOIN progreso_usuario up ON l.id = up.leccion_id AND up.usuario_id = ?
                WHERE up.estado IS NULL
                ORDER BY l.indice_orden ASC
                LIMIT 1
            ");
            $stmt->execute([$idUsuario]);
            $actual = $stmt->fetch();
        }

        if (!$actual) {
            $stmt = $this->pdo->prepare("
                SELECT l.*, up.estado
                FROM lecciones l
                JOIN progreso_usuario up ON l.id = up.leccion_id
                WHERE up.usuario_id = ? AND up.estado = 'completado'
                ORDER BY l.indice_orden DESC
                LIMIT 1
            ");
            $stmt->execute([$idUsuario]);
            $actual = $stmt->fetch();
        }

        return $actual;
    }

    public function obtenerCursos()
    {
        $stmt = $this->pdo->query("SELECT * FROM cursos ORDER BY indice_orden ASC");
        return $stmt->fetchAll();
    }

    public function obtenerRutaAprendizaje($idUsuario)
    {
        $stmt = $this->pdo->prepare("
            SELECT l.*, IFNULL(p.estado, 'bloqueado') as estado, IFNULL(p.puntaje, 0) as puntaje, p.completado_en, l.nivel
            FROM lecciones l
            LEFT JOIN progreso_usuario p ON l.id = p.leccion_id AND p.usuario_id = ?
            ORDER BY l.indice_orden ASC
        ");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    public function completarLeccion($idUsuario, $idLeccion)
    {
        $this->desbloquearSiguienteLeccion($idUsuario, $idLeccion);

        $stmt = $this->pdo->prepare("UPDATE estadisticas_usuario SET id_ultima_leccion = ? WHERE usuario_id = ?");
        $stmt->execute([$idLeccion, $idUsuario]);

        $stmt = $this->pdo->prepare("SELECT usuario_id FROM progreso_usuario WHERE usuario_id = ? AND leccion_id = ? AND estado = 'completado'");
        $stmt->execute([$idUsuario, $idLeccion]);
        if ($stmt->fetch())
            return false;

        $stmt = $this->pdo->prepare("
            INSERT INTO progreso_usuario (usuario_id, leccion_id, estado, completado_en) 
            VALUES (?, ?, 'completado', NOW())
            ON DUPLICATE KEY UPDATE estado = 'completado', completado_en = NOW()
        ");
        $stmt->execute([$idUsuario, $idLeccion]);

        return true;
    }

    private function desbloquearSiguienteLeccion($idUsuario, $idLeccionCompletada)
    {
        $stmt = $this->pdo->prepare("SELECT indice_orden, curso_id FROM lecciones WHERE id = ?");
        $stmt->execute([$idLeccionCompletada]);
        $fila = $stmt->fetch();
        if (!$fila)
            return;

        $ordenActual = $fila['indice_orden'];
        $idCurso = $fila['curso_id'];

        $stmt = $this->pdo->prepare("SELECT id FROM lecciones WHERE curso_id = ? AND indice_orden > ? ORDER BY indice_orden ASC LIMIT 1");
        $stmt->execute([$idCurso, $ordenActual]);
        $idSiguienteLeccion = $stmt->fetchColumn();

        if (!$idSiguienteLeccion) {
            $stmt = $this->pdo->prepare("SELECT indice_orden FROM cursos WHERE id = ?");
            $stmt->execute([$idCurso]);
            $ordenCursoActual = $stmt->fetchColumn();

            $stmt = $this->pdo->prepare("SELECT id FROM cursos WHERE indice_orden > ? ORDER BY indice_orden ASC LIMIT 1");
            $stmt->execute([$ordenCursoActual]);
            $idSiguienteCurso = $stmt->fetchColumn();

            if ($idSiguienteCurso) {
                $stmt = $this->pdo->prepare("SELECT id FROM lecciones WHERE curso_id = ? ORDER BY indice_orden ASC LIMIT 1");
                $stmt->execute([$idSiguienteCurso]);
                $idSiguienteLeccion = $stmt->fetchColumn();
            }
        }

        if ($idSiguienteLeccion) {
            $stmt = $this->pdo->prepare("INSERT INTO progreso_usuario (usuario_id, leccion_id, estado) VALUES (?, ?, 'disponible') ON DUPLICATE KEY UPDATE estado = IF(estado='bloqueado', 'disponible', estado)");
            $stmt->execute([$idUsuario, $idSiguienteLeccion]);
        }
    }
}
