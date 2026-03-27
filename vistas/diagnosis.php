<?php
session_start();
require_once '../configuracion/config.php';
require_once '../configuracion/verificar_sesion.php';
protegerPagina();

$idUsuario = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nivel = $_POST['nivel'] ?? 'principiante';
    $stmt = $pdo->prepare("UPDATE usuarios SET nivel_conocimiento = ? WHERE id = ?");
    $stmt->execute([$nivel, $idUsuario]);

    $_SESSION['diagnostico_completado'] = true;
    header('Location: dashboard.php?diagnostico=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Inicial - DebiHaby</title>
    <link rel="stylesheet" href="../public/css/styles.css">
    <link rel="stylesheet" href="../public/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="lesson-container" style="max-width: 700px; margin-top: 5rem;">
        <div class="card-premium text-center">
            <i class="fas fa-microscope" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
            <h2>Diagnóstico Contable</h2>
            <p>Queremos saber cuánto sabes para personalizar tu experiencia.</p>
            
            <form action="diagnosis.php" method="POST" class="mt-3">
                <div style="display: grid; gap: 1rem;">
                    <button type="submit" name="nivel" value="principiante" class="btn btn-secondary" style="background: white; border: 1px solid #ddd; padding: 1.5rem; text-align: left; display: flex; align-items: center; gap: 1rem;">
                        <span style="font-size: 2rem;">🌱</span>
                        <div>
                            <strong>Novato</strong>
                            <p style="margin:0; font-size: 0.8rem; opacity: 0.7;">Nunca he visto contabilidad antes.</p>
                        </div>
                    </button>
                    
                    <button type="submit" name="nivel" value="intermedio" class="btn btn-secondary" style="background: white; border: 1px solid #ddd; padding: 1.5rem; text-align: left; display: flex; align-items: center; gap: 1rem;">
                        <span style="font-size: 2rem;"></span>
                        <div>
                            <strong>Conozco lo básico</strong>
                            <p style="margin:0; font-size: 0.8rem; opacity: 0.7;">Entiendo qué es un Activo y un Pasivo.</p>
                        </div>
                    </button>
                    
                    <button type="submit" name="nivel" value="avanzado" class="btn btn-secondary" style="background: white; border: 1px solid #ddd; padding: 1.5rem; text-align: left; display: flex; align-items: center; gap: 1rem;">
                        <span style="font-size: 2rem;"></span>
                        <div>
                            <strong>Experto</strong>
                            <p style="margin:0; font-size: 0.8rem; opacity: 0.7;">Manejo estados financieros y cuentas T.</p>
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        /**
         * ECHO
         */
    </script>
</body>
</html>
