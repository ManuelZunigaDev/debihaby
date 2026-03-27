<?php
require_once __DIR__ . '/../configuracion/config.php';
require_once __DIR__ . '/ControladorUsuario.php';
require_once __DIR__ . '/ControladorCurso.php';

class ControladorDashboard {
    private $pdo;
    private $controladorUsuario;
    private $controladorCurso;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->controladorUsuario = new ControladorUsuario($pdo);
        $this->controladorCurso = new ControladorCurso($pdo);
    }

    public function obtenerEstadisticasEstudiante($idUsuario) {
        $perfil = $this->controladorUsuario->obtenerPerfilUsuario($idUsuario);
        if (!$perfil) return null;
        
        return [
            'nombre_completo' => $perfil['nombre_completo'] ?? '',
            'nombre_usuario'  => $perfil['nombre_usuario'] ?? '',
            'avatar'          => $perfil['avatar'] ?? '../public/assets/debi_pet.png',
            'puntos'          => $perfil['puntos'] ?? 0,
            'nivel'           => $perfil['nivel'] ?? 1,
            'racha'           => $perfil['racha'] ?? 0,
            'ultima_actividad' => $perfil['ultima_actividad'] ?? null
        ];
    }

    public function obtenerLeccionActual($idUsuario) {
        // Primero intentamos recuperar la última lección guardada específicamente (mejor UX)
        $stmt = $this->pdo->prepare("SELECT id_ultima_leccion FROM estadisticas_usuario WHERE usuario_id = ?");
        $stmt->execute([$idUsuario]);
        $idUltima = $stmt->fetchColumn();

        $actual = null;
        if ($idUltima) {
            $stmt = $this->pdo->prepare("
                SELECT l.*, up.estado 
                FROM lecciones l
                LEFT JOIN progreso_usuario up ON l.id = up.leccion_id AND up.usuario_id = ?
                WHERE l.id = ?
            ");
            $stmt->execute([$idUsuario, $idUltima]);
            $actual = $stmt->fetch();
        }

        // Si no hay id_ultima o no es válida, caemos en la lógica secuencial
        if (!$actual || ($actual['estado'] === 'completado' && $idUltima)) {
             $actual = $this->controladorCurso->obtenerLeccionActual($idUsuario);
        }

        if (!$actual) return null;

        return [
            'id' => $actual['id'],
            'titulo' => $actual['titulo'],
            'descripcion' => $actual['descripcion'],
            'categoria' => $actual['categoria'] ?? 'General',
            'recompensa_xp' => $actual['recompensa_xp'] ?? 100,
            'estado' => $actual['estado'] ?? 'disponible'
        ];
    }

    public function obtenerRutaAprendizaje($idUsuario) {
        $ruta = $this->controladorCurso->obtenerRutaAprendizaje($idUsuario);
        $rutaProcesada = [];
        
        foreach ($ruta as $leccion) {
            $rutaProcesada[] = [
                'id' => $leccion['id'],
                'titulo' => $leccion['titulo'],
                'categoria' => $leccion['categoria'] ?? 'General',
                'recompensa_xp' => $leccion['recompensa_xp'] ?? 100,
                'estado' => $leccion['estado'],
                'puntaje' => $leccion['puntaje'] ?? 0,
                'completado_en' => $leccion['completado_en'] ?? null,
                'clase_icono' => $leccion['clase_icono'] ?? null
            ];
        }
        return $rutaProcesada;
    }

    public function obtenerActividadReciente($idUsuario) {
        return $this->controladorUsuario->obtenerActividadReciente($idUsuario);
    }

    public function obtenerListaUsuarios() {
        $usuarios = $this->controladorUsuario->obtenerTodosLosUsuarios();
        $lista = [];
        
        foreach ($usuarios as $u) {
            $lista[] = [
                'id' => $u['id'],
                'nombre_usuario' => $u['nombre_usuario'],
                'rol' => $u['rol'],
                'puntos' => $u['puntos'],
                'nivel' => $u['nivel']
            ];
        }
        return $lista;
    }

    public function completarLeccion($idUsuario, $idLeccion) {
        if ($this->controladorCurso->completarLeccion($idUsuario, $idLeccion)) {
            $this->controladorUsuario->agregarXP($idUsuario, 100);
            $this->controladorUsuario->actualizarRacha($idUsuario);
            return true;
        }
        return false;
    }
}
