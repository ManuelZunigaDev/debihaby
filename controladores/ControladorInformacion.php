<?php
require_once __DIR__ . '/../configuracion/config.php';

class ControladorInformacion {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerNoticias($limite = 5) {
        $stmt = $this->pdo->prepare("SELECT * FROM noticias ORDER BY creado_en DESC LIMIT ?");
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerMitos() {
        $stmt = $this->pdo->query("SELECT * FROM mitos");
        $todosLosMitos = $stmt->fetchAll();
        
        if (empty($todosLosMitos)) {
            $todosLosMitos = [
                ['mito' => 'La contabilidad es solo matemáticas', 'realidad' => 'Es lógica y organización', 'explicacion' => 'Aunque usa números, se trata más de entender el flujo del dinero y la toma de decisiones.'],
                ['mito' => 'Solo se usa en grandes empresas', 'realidad' => 'Toda entidad la necesita', 'explicacion' => 'Incluso tus finanzas personales o una tienda pequeña se benefician enormemente.']
            ];
        }
        return $todosLosMitos;
    }

    public function obtenerRespuestasExpertos() {
        $stmt = $this->pdo->query("
            SELECT q.*, u.nombre_usuario 
            FROM preguntas_expertos q 
            JOIN usuarios u ON q.usuario_id = u.id 
            WHERE q.estado = 'respondida' 
            ORDER BY q.creado_en DESC
        ");
        return $stmt->fetchAll();
    }
}
