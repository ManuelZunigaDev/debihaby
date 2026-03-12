<?php
session_start();
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DebiHaby - Plataforma educativa para aprender contabilidad jugando. CBTis 171 Mariano Abasolo.">
    <meta name="keywords" content="contabilidad, educación, gamificación, CBTis 171, DebiHaby, aprender jugando">
    <meta name="author" content="Bernardo Martínez, Milka Morales - CBTis 171">
    <title>DebiHaby - Aprende Contabilidad Jugando</title>
    <link rel="icon" type="image/ico" href="assets/logo2.ico">
    <link rel="preload" href="css/styles.css" as="style">
    <link rel="preload" href="js/script.js" as="script">
    <link rel="preload" href="assets/debi_pet.png" as="image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <div class="logo">
                <img src="assets/logo.png" alt="DebiHaby Logo" class="logo-img">
                DebiHaby
            </div>
            <ul class="nav-menu">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#que-ofrecemos">¿Qué Ofrecemos?</a></li>
                <li><a href="#video">Video</a></li>
                <li><a href="#diagnostico">Diagnóstico</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php" class="nav-btn">Dashboard</a></li>
                    <li><a href="logout.php" class="nav-btn logout">Salir</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-btn">Ingresar</a></li>
                <?php endif; ?>
            </ul>
            <button class="mobile-menu-toggle" aria-label="Toggle menu"><i>☰</i></button>
        </nav>
    </header>

    <section id="inicio" class="hero">
        <div class="hero-content">
            <div class="hero-main">
                <div class="hero-text">
                    <h1>Aprende Contabilidad<br>Jugando</h1>
                    <p class="hero-subtitle">Transforma tu forma de aprender contabilidad con DebiHaby, la plataforma educativa interactiva.</p>
                </div>
                <div class="hero-mascot">
                    <img src="assets/debi_pet.png" alt="Debi" class="mascot-img">
                </div>
            </div>
            <div class="hero-objectives">
                <div class="objective-card"><h3>Misión</h3><p>Fortalecer el aprendizaje contable mediante tecnología innovadora.</p></div>
                <div class="objective-card"><h3>Método</h3><p>Gamificación aplicada a la educación para facilitar la comprensión.</p></div>
                <div class="objective-card"><h3>Objetivo</h3><p>Desarrollar competencias contables en estudiantes para el éxito profesional.</p></div>
            </div>
        </div>
    </section>

    <section id="que-ofrecemos" class="section">
        <div class="section-header">
            <h2 class="section-title">¿Qué Ofrecemos?</h2>
            <p class="section-subtitle">Explora nuestros recursos diseñados para ti</p>
        </div>
        <div class="quick-access-grid">
            <a href="#noticias" class="access-card"><h3>Noticias</h3><p>Mantente al día con el mundo contable.</p></a>
            <a href="#cursos" class="access-card"><h3>Cursos</h3><p>Cursos interactivos con retroalimentación inmediata.</p></a>
            <a href="#expertos" class="access-card"><h3>Expertos</h3><p>Resuelve tus dudas con profesionales.</p></a>
            <a href="#mitos" class="access-card"><h3>Mitos</h3><p>Descubre la verdad sobre la contabilidad.</p></a>
        </div>
    </section>

    <section id="diagnostico" class="section">
        <div class="cta-section">
            <h2>¿Listo para Comenzar?</h2>
            <p>Evalúa tus conocimientos con nuestro diagnóstico inicial.</p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
                <a href="register.php" class="btn btn-secondary">Registrarse</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-primary">Ir a Dashboard</a>
                <a href="diagnosis.php" class="btn btn-secondary">Iniciar Diagnóstico</a>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section"><h3>DebiHaby</h3><p>Plataforma educativa interactiva.</p></div>
            <div class="footer-section"><h3>Institución</h3><p>CBTis No. 171 "Mariano Abasolo"</p></div>
            <div class="footer-section"><h3>Autores</h3><p>Bernardo Martínez, Milka Morales</p></div>
        </div>
        <div class="footer-bottom"><p>&copy; <span id="current-year">2026</span> DebiHaby. Todos los derechos reservados.</p></div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>