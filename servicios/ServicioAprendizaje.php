<?php
require_once __DIR__ . '/../repositorios/RepositorioUsuario.php';
require_once __DIR__ . '/../repositorios/RepositorioCurso.php';

class ServicioAprendizaje {
    private $userRepo;
    private $cursoRepo;

    public function __construct($pdo) {
        $this->userRepo = new RepositorioUsuario($pdo);
        $this->cursoRepo = new RepositorioCurso($pdo);
    }

    public function obtenerProgresoActual($idUsuario) {
        $usuario = $this->userRepo->obtenerPorId($idUsuario);
        $leccionId = $usuario['id_ultima_leccion'] ?: 1;
        
        // Retornar la lección donde se quedó
        $progreso = $this->cursoRepo->obtenerProgreso($idUsuario);
        $actual = array_filter($progreso, fn($l) => $l['id'] == $leccionId);
        
        return reset($actual) ?: reset($progreso);
    }

    public function registrarAvance($idUsuario, $idLeccion) {
        // Guardar última lección visitada para UX
        $this->userRepo->actualizarUltimaLeccion($idUsuario, $idLeccion);
        
        // BloomTaxonomy: Al completar Aplicar/Analizar, podemos sumar puntos extra
        $this->userRepo->actualizarPuntos($idUsuario, 100);
        $this->cursoRepo->actualizarEstado($idUsuario, $idLeccion, 'completado');
        
        // Desbloquear siguiente si es necesario (lógica del curso)
        // ... (ver ControladorCurso.php para lógica extendida)
    }

    public function formatearContenidoBloom() {
        // Esta función puede integrarse en el cargador de JSON 
        // para asegurar que cada lección tenga pasos de Bloom
        return ["Recordar", "Comprender", "Aplicar", "Analizar", "Evaluar", "Crear"];
    }
}
