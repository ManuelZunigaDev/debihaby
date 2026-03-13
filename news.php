<?php
session_start();
require_once 'includes/config.php';

$stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
$news = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Noticias y Actualizaciones - DebiHaby</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .news-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .news-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; }
        .news-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .news-body { padding: 1.5rem; }
        .news-tag { background: var(--primary); color: white; padding: 0.2rem 0.8rem; border-radius: 20px; font-size: 0.8rem; }
    </style>
</head>
<body>
    <header class="dashboard-header" style="text-align: center; padding: 3rem;">
        <h1>📰 Noticias y Actualizaciones</h1>
        <p>Mantente al día con el mundo de la contabilidad</p>
    </header>

    <div class="news-grid">
        <?php foreach ($news as $item): ?>
            <div class="news-card">
                <div class="news-body">
                    <span class="news-tag"><?php echo htmlspecialchars($item['categoria'] ?? ''); ?></span>
                    <h3><?php echo htmlspecialchars($item['titulo'] ?? ''); ?></h3>
                    <p><?php echo substr(htmlspecialchars($item['contenido'] ?? ''), 0, 150); ?>...</p>
                    <small><?php echo date('d M, Y', strtotime($item['creado_en'] ?? 'now')); ?></small>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if(empty($news)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 4rem;">
                <p>No hay noticias recientes. ¡Vuelve pronto!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
