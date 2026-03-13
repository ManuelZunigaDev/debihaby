<?php
require_once __DIR__ . '/../includes/config.php';

class ControladorUsuario {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerPerfilUsuario($idUsuario) {
        $stmt = $this->pdo->prepare("
            SELECT u.nombre_completo, u.avatar, u.rol, u.nivel_conocimiento, s.* 
            FROM usuarios u 
            JOIN estadisticas_usuario s ON u.id = s.usuario_id 
            WHERE u.id = ?
        ");
        $stmt->execute([$idUsuario]);
        return $stmt->fetch();
    }

    public function agregarXP($idUsuario, $cantidad) {
        $stmt = $this->pdo->prepare("UPDATE estadisticas_usuario SET puntos = puntos + ? WHERE usuario_id = ?");
        $stmt->execute([$cantidad, $idUsuario]);

        $stmt = $this->pdo->prepare("SELECT puntos FROM estadisticas_usuario WHERE usuario_id = ?");
        $stmt->execute([$idUsuario]);
        $xp = $stmt->fetchColumn();
        $nuevoNivel = floor($xp / 1000) + 1;

        $stmt = $this->pdo->prepare("UPDATE estadisticas_usuario SET nivel = ? WHERE usuario_id = ?");
        $stmt->execute([$nuevoNivel, $idUsuario]);
    }

    public function actualizarRacha($idUsuario) {
        $stmt = $this->pdo->prepare("SELECT ultima_actividad, racha FROM estadisticas_usuario WHERE usuario_id = ?");
        $stmt->execute([$idUsuario]);
        $datos = $stmt->fetch();

        $hoy = date('Y-m-d');
        if ($datos['ultima_actividad'] === $hoy) return;

        $ayer = date('Y-m-d', strtotime('-1 day'));
        $racha = ($datos['ultima_actividad'] === $ayer) ? $datos['racha'] + 1 : 1;

        $stmt = $this->pdo->prepare("UPDATE estadisticas_usuario SET racha = ?, ultima_actividad = ? WHERE usuario_id = ?");
        $stmt->execute([$racha, $hoy, $idUsuario]);
    }

    public function obtenerRankingGlobal($limite = 10) {
        $stmt = $this->pdo->prepare("
            SELECT u.nombre_usuario, s.puntos, s.nivel 
            FROM usuarios u 
            JOIN estadisticas_usuario s ON u.id = s.usuario_id 
            ORDER BY s.puntos DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerActividadReciente($idUsuario) {
        $stmt = $this->pdo->prepare("
            SELECT l.titulo, l.recompensa_xp, p.estado, p.completado_en
            FROM progreso_usuario p
            JOIN lecciones l ON p.leccion_id = l.id
            WHERE p.usuario_id = ?
            ORDER BY p.completado_en DESC
            LIMIT 5
        ");
        $stmt->execute([$idUsuario]);
        $filas = $stmt->fetchAll();

        $actividades = [];
        foreach ($filas as $fila) {
            $fecha = 'Pendiente';
            if ($fila['completado_en']) {
                $dt = new DateTime($fila['completado_en']);
                $ahora = new DateTime();
                $diff = $ahora->diff($dt);
                if ($diff->days === 0) {
                    $fecha = 'Hoy';
                } elseif ($diff->days === 1) {
                    $fecha = 'Ayer';
                } elseif ($diff->days < 7) {
                    $fecha = 'Hace ' . $diff->days . ' días';
                } else {
                    $fecha = $dt->format('d/m/Y');
                }
            }

            $textoXP = $fila['estado'] === 'completado' ? '+' . $fila['recompensa_xp'] . ' XP' : 'En progreso';
            
            $actividades[] = [
                'tipo' => $fila['estado'] === 'completado' ? 'leccion_completada' : 'en_progreso',
                'titulo' => $fila['titulo'],
                'fecha' => $fecha,
                'xp' => $textoXP
            ];
        }

        if (empty($actividades)) {
            $actividades[] = [
                'tipo' => 'bienvenida',
                'titulo' => '¡Bienvenido a DebiHaby!',
                'fecha' => 'Hoy',
                'xp' => '🎉'
            ];
        }
        return $actividades;
    }

    public function obtenerTodosLosUsuarios() {
        $stmt = $this->pdo->query("
            SELECT u.id, u.nombre_usuario, u.rol, u.correo, s.puntos, s.nivel 
            FROM usuarios u 
            JOIN estadisticas_usuario s ON u.id = s.usuario_id 
            ORDER BY s.puntos DESC
        ");
        return $stmt->fetchAll();
    }
}
