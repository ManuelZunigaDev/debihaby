<?php
session_start();
include 'includes/config.php';
?>
<!DOCTYPE html><html lang="es">
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
      
      <button class="mobile-menu-toggle" aria-label="Toggle menu">
        <i>☰</i>
      </button>
    </nav>
  </header>

  <section id="inicio" class="hero">
    <div class="hero-content">
      <div class="hero-main">
        <div class="hero-text">
          <h1>Aprende Contabilidad<br>Jugando</h1>
          <p class="hero-subtitle">
            Transforma tu forma de aprender contabilidad con DebiHaby, la plataforma educativa 
            que convierte conceptos financieros en experiencias interactivas y divertidas.
          </p>
        </div>
        
        <div class="hero-mascot">
          <img src="assets/debi_pet.png" alt="Debi - Mascota de DebiHaby" class="mascot-img">
        </div>
      </div>
      
      <div class="hero-objectives">
        <div class="objective-card">
          <h3>Nuestra Misión</h3>
          <p>
            Fortalecer el aprendizaje de la contabilidad mediante herramientas tecnológicas 
            innovadoras que motiven y faciliten la comprensión de conceptos financieros.
          </p>
        </div>
        
        <div class="objective-card">
          <h3>Nuestro Método</h3>
          <p>
            Gamificación aplicada a la educación: aprende registrando transacciones, 
            elaborando balances y resolviendo casos prácticos en un ambiente virtual interactivo.
          </p>
        </div>
        
        <div class="objective-card">
          <h3>Nuestro Objetivo</h3>
          <p>
            Desarrollar competencias contables en estudiantes del nivel medio superior, 
            preparándolos para el éxito académico y profesional del siglo XXI.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section id="que-ofrecemos" class="section">
    <div class="section-header">
      <h2 class="section-title">¿Qué Ofrecemos?</h2>
      <p class="section-subtitle">
        Explora nuestros recursos educativos diseñados para hacer tu aprendizaje más efectivo y entretenido
      </p>
    </div>
    
    <div class="quick-access-grid">
      <a href="#noticias" class="access-card fade-in">
        <div class="card-icon">N</div>
        <h3>Noticias de Contabilidad</h3>
        <p>
          Mantente actualizado con las últimas noticias, tendencias y cambios 
          en el mundo de la contabilidad y las finanzas.
        </p>
      </a>
      
      <a href="#cursos" class="access-card fade-in">
        <div class="card-icon">C</div>
        <h3>Cursos Interactivos</h3>
        <p>
          Accede a cursos de contabilidad básica y media con ejercicios prácticos, 
          simulaciones y retroalimentación inmediata.
        </p>
      </a>
      
      <a href="#expertos" class="access-card fade-in">
        <div class="card-icon">E</div>
        <h3>Pregunta a Expertos</h3>
        <p>
          Resuelve tus dudas con profesionales de la contabilidad. 
          Obtén respuestas claras a tus consultas más complejas.
        </p>
      </a>
      
      <a href="#mitos" class="access-card fade-in">
        <div class="card-icon">M</div>
        <h3>Mitos y Realidades</h3>
        <p>
          Descubre la verdad detrás de los mitos más comunes sobre la contabilidad 
          y aprende con información verificada y confiable.
        </p>
      </a>
    </div>
  </section>

  <section id="video" class="video-section">
    <div class="section-header">
      <h2 class="section-title">¿Cómo Funciona?</h2>
      <p class="section-subtitle">
        Descubre en 1 minuto cómo DebiHaby transforma el aprendizaje de la contabilidad
      </p>
    </div>
    
    <div class="video-container fade-in">
      <div class="video-wrapper">
        <div class="video-placeholder" data-video="">
          ▶
          <div style="position: absolute; bottom: 20px; font-size: 1rem; width: 100%;">
            Click para reproducir el video introductorio
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="diagnostico" class="section">
    <div class="cta-section fade-in">
      <h2>¿Listo para Comenzar?</h2>
      <p>
        Evalúa tus conocimientos previos en contabilidad con nuestro diagnóstico inicial 
        y descubre tu nivel actual. ¡Solo te tomará 5 minutos!
      </p>
      <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="login.php" class="btn btn-primary">
          Iniciar Sesión
        </a>
        <a href="register.php" class="btn btn-secondary">
          Registrarse
        </a>
      <?php else: ?>
        <a href="dashboard.php" class="btn btn-primary">
          Ir a mi Dashboard
        </a>
        <a href="diagnosis.php" class="btn btn-secondary">
          Iniciar Diagnóstico
        </a>
      <?php endif; ?>
    </div>
  </section>

  <section class="section" style="background: var(--gradient-card); border-radius: var(--border-radius); padding: var(--spacing-xl);">
    <div class="section-header">
      <h2 class="section-title">Sobre el Proyecto</h2>
    </div>
    
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
      <p style="font-size: 1.1rem; line-height: 1.8; color: var(--dark-light);">
        <strong>DebiHaby</strong> es un proyecto educativo desarrollado por estudiantes del 
        <strong>CBTis No. 171 "Mariano Abasolo"</strong> con el objetivo de revolucionar 
        la enseñanza de la contabilidad en el nivel medio superior.
      </p>
      <br>
      <p style="font-size: 1rem; line-height: 1.8; color: var(--dark-light);">
        Combinando conocimientos de programación y contabilidad adquiridos durante el bachillerato 
        tecnológico, este proyecto busca demostrar que es posible fortalecer la comprensión de 
        principios contables fundamentales mediante actividades lúdicas e interactivas accesibles 
        desde cualquier navegador.
      </p>
      <br>
      <div style="display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
        <div>
          <h3 style="color: var(--primary); font-size: 2rem; margin-bottom: 0.5rem;"></h3>
          <p style="font-weight: 600; color: var(--dark);">Educación Integral</p>
        </div>
        <div>
          <h3 style="color: var(--primary); font-size: 2rem; margin-bottom: 0.5rem;"></h3>
          <p style="font-weight: 600; color: var(--dark);">Gamificación</p>
        </div>
        <div>
          <h3 style="color: var(--primary); font-size: 2rem; margin-bottom: 0.5rem;"></h3>
          <p style="font-weight: 600; color: var(--dark);">Tecnología Web</p>
        </div>
        <div>
          <h3 style="color: var(--primary); font-size: 2rem; margin-bottom: 0.5rem;"></h3>
          <p style="font-weight: 600; color: var(--dark);">Contabilidad Práctica</p>
        </div>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="footer-content">
      <div class="footer-section">
        <h3>DebiHaby</h3>
        <p>
          Plataforma educativa para el aprendizaje de contabilidad 
          mediante gamificación y recursos interactivos.
        </p>
      </div>
      
      <div class="footer-section">
        <h3>Institución</h3>
        <p>
          <strong>CBTis No. 171</strong><br>
          "Mariano Abasolo"<br>
          Prolongación Echegaray Norte No. 416<br>
          Col. Juárez, Abasolo, Gto.<br>
          C.P. 36977
        </p>
      </div>
      
      <div class="footer-section">
        <h3>Autores</h3>
        <p>
          Bernardo Martínez González<br>
          Milka Ayizdeth Morales Contreras
        </p>
        <br>
        <h3>Asesores</h3>
        <p style="font-size: 0.85rem;">
          José Manuel González Zaragoza<br>
          Francisco Javier Tafolla Granados<br>
          Gabino Castillo Martínez<br>
          Olga Lidia Vargas Cuevas
        </p>
      </div>
      
      <div class="footer-section">
        <h3>Recursos</h3>
        <p>
          <a href="#noticias">Noticias</a><br>
          <a href="#cursos">Cursos</a><br>
          <a href="#expertos">Expertos</a><br>
          <a href="#mitos">Mitos y Realidades</a><br>
          <a href="#diagnostico">Diagnóstico</a>
        </p>
      </div>
    </div>
    
    <div class="footer-bottom">
      <p>
        &copy; <span id="current-year">2026</span> DebiHaby - CBTis 171 "Mariano Abasolo". 
        Todos los derechos reservados.
      </p>
      <p style="margin-top: 0.5rem; font-size: 0.85rem;">
        Proyecto educativo desarrollado con fines académicos.
      </p>
    </div>
  </footer>

  <script src="js/script.js"></script>
  
</body>
</html>