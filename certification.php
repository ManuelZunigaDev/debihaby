<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT u.full_name, p.completed_at FROM users u JOIN user_progress p ON u.id = p.user_id WHERE u.id = ? AND p.lesson_id = 99 AND p.status = 'completed'");
$stmt->execute([$userId]);
$cert = $stmt->fetch();

if (!$cert) {
    die("Aún no has obtenido tu certificación. Completa la evaluación final primero.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Certificado - DebiHaby</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .cert-card { max-width: 800px; margin: 4rem auto; padding: 3rem; border: 15px double var(--primary); text-align: center; position: relative; background: #fff; }
        .cert-header { font-family: 'Georgia', serif; font-size: 2.5rem; color: #333; margin-bottom: 2rem; }
        .cert-body { font-size: 1.2rem; line-height: 1.6; }
        .qr-code { width: 120px; margin-top: 2rem; }
    </style>
</head>
<body>
    <div class="cert-card">
        <div class="cert-header">Certificado de Aprovechamiento</div>
        <div class="cert-body">
            <p>Se otorga el presente a:</p>
            <h2 style="font-size: 3rem; margin: 1rem 0; color: var(--primary);"><?php echo htmlspecialchars($cert['full_name']); ?></h2>
            <p>Por haber completado con éxito el curso interactivo de:</p>
            <h3>CONTABILIDAD BÁSICA LÚDICA</h3>
            <p>Fecha de emisión: <?php echo date('d/m/Y', strtotime($cert['completed_at'])); ?></p>
        </div>
        <div class="qr-validation">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://debihaby.edu/verify/<?php echo $userId; ?>" alt="QR Code" class="qr-code">
            <p style="font-size: 0.8rem; color: #777;">Escanea para validar este certificado</p>
        </div>
        <div style="margin-top: 2rem;">
            <button onclick="window.print()" class="btn btn-secondary">Descargar PDF (Imprimir)</button>
            <a href="dashboard.php" class="btn btn-primary">Volver al Inicio</a>
        </div>
    </div>
</body>
</html>
