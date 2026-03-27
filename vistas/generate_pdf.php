<?php
session_start();
require_once '../configuracion/config.php';
require_once '../configuracion/verificar_sesion.php';
protegerPagina();

$idUsuario = $_SESSION['id_usuario'];
$stmt = $pdo->prepare("
    SELECT u.nombre_completo, s.puntos, s.nivel 
    FROM usuarios u 
    JOIN estadisticas_usuario s ON u.id = s.usuario_id 
    WHERE u.id = ?
");
$stmt->execute([$idUsuario]);
$usuario = $stmt->fetch();

if (!$usuario) {
    die("Error al generar certificado.");
}

// Calcular progreso para seguridad
$stmt = $pdo->prepare("SELECT COUNT(*) FROM lecciones");
$total = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM progreso_usuario WHERE usuario_id = ? AND estado = 'completado'");
$stmt->execute([$idUsuario]);
$completadas = $stmt->fetchColumn();

if ($completadas < $total && $total > 0) {
    die("Debes completar todas las lecciones para obtener tu certificado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado - DebiHaby</title>
    <style>
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #fdfdfd; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .certificate-container { width: 900px; height: 600px; background: white; border: 20px solid #FFA726; padding: 50px; position: relative; box-shadow: 0 0 50px rgba(0,0,0,0.1); text-align: center; box-sizing: border-box; }
        .certificate-container::before { content: ''; position: absolute; top: 10px; left: 10px; right: 10px; bottom: 10px; border: 2px solid #F57C00; pointer-events: none; }
        .logo { width: 100px; margin-bottom: 20px; }
        h1 { font-family: 'Poppins', sans-serif; color: #F57C00; font-size: 3.5rem; margin: 0; }
        .subtitle { font-size: 1.5rem; color: #555; margin-bottom: 30px; }
        .presentado-a { font-size: 1.2rem; color: #888; font-style: italic; }
        .nombre { font-size: 3rem; font-weight: 700; color: #1a1a2e; text-decoration: underline; margin: 20px 0; }
        .descripcion { font-size: 1.1rem; max-width: 600px; margin: 0 auto; line-height: 1.6; color: #444; }
        .footer-cert { display: flex; justify-content: space-between; margin-top: 50px; border-top: 1px solid #ddd; padding-top: 20px; }
        .firma { text-align: center; }
        .firma-linea { width: 200px; border-top: 2px solid #555; margin-bottom: 10px; }
        .print-btn { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #F57C00; color: white; border: none; border-radius: 5px; cursor: pointer; }
        @media print { .print-btn { display: none; } body { background: none; } .certificate-container { box-shadow: none; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Imprimir / Guardar PDF</button>

    <div class="certificate-container">
        <img src="../public/assets/logo.png" alt="Logo" class="logo">
        <h1>Certificado de Logro</h1>
        <div class="subtitle">DebiHaby - Academia de Contabilidad Gamificada</div>
        
        <div class="presentado-a">Este reconocimiento se otorga a:</div>
        <div class="nombre"><?php echo htmlspecialchars($usuario['nombre_completo']); ?></div>
        
        <div class="descripcion">
            Por haber completado satisfactoriamente el programa de entrenamiento contable integral, demostrando un dominio excepcional en la clasificación de Activos, Pasivos y Capital, logrando un total de **<?php echo number_format($usuario['puntos']); ?> XP**.
        </div>
        
        <div class="footer-cert">
            <div class="info-meta">
                <strong>Fecha:</strong> <?php echo date('d/m/Y'); ?><br>
                <strong>ID Certificado:</strong> #DB-<?php echo str_pad($idUsuario, 5, '0', STR_PATH_LEFT); ?>
            </div>
            <div class="firma">
                <div class="firma-linea"></div>
                <strong>Dirección Académica</strong><br>
                DebiHaby Global
            </div>
        </div>
    </div>
</body>
</html>
/**
REFACTOS CON IA LISTO */