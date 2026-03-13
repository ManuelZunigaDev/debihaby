<?php
// Sidebar headers removed to avoid "already sent" warnings in parent pages
?>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <img src="assets/logo.png" alt="Logo">
        <span>DebiHaby</span>
    </div>
    <nav class="sidebar-nav" id="main-nav">
        <a href="dashboard.php" data-tab="dashboard" class="nav-item <?php echo (!isset($currentPage) || $currentPage === 'dashboard') ? 'active' : ''; ?>">
            <span class="icon"><i class="fas fa-th-large"></i></span> Dashboard
        </a>
        
        <a href="courses.php" data-tab="courses" class="nav-item <?php echo (isset($currentPage) && $currentPage === 'courses') ? 'active' : ''; ?>">
            <span class="icon"><i class="fas fa-graduation-cap"></i></span> Mis Cursos
        </a>
        
        <a href="javascript:void(0)" onclick="switchTab('certification')" data-tab="certification" class="nav-item">
            <span class="icon"><i class="fas fa-medal"></i></span> Logros
        </a>
        
        <a href="javascript:void(0)" onclick="switchTab('stats')" data-tab="stats" class="nav-item">
            <span class="icon"><i class="fas fa-chart-bar"></i></span> Estadísticas
        </a>
... (truncated)
        <a href="javascript:void(0)" onclick="switchTab('news')" data-tab="news" class="nav-item">
            <span class="icon"><i class="fas fa-newspaper"></i></span> Noticias
        </a>

        <a href="javascript:void(0)" onclick="switchTab('experts')" data-tab="experts" class="nav-item">
            <span class="icon"><i class="fas fa-user-tie"></i></span> Expertos
        </a>

        <a href="javascript:void(0)" onclick="switchTab('myths')" data-tab="myths" class="nav-item">
            <span class="icon"><i class="fas fa-brain"></i></span> Mitos
        </a>

        <a href="javascript:void(0)" onclick="switchTab('resources')" data-tab="resources" class="nav-item">
            <span class="icon"><i class="fas fa-book-atlas"></i></span> Directorio
        </a>

        <a href="javascript:void(0)" onclick="switchTab('tools')" data-tab="tools" class="nav-item">
            <span class="icon"><i class="fas fa-calculator"></i></span> Calculadoras
        </a>

        <a href="javascript:void(0)" onclick="switchTab('library')" data-tab="library" class="nav-item">
            <span class="icon"><i class="fas fa-file-pdf"></i></span> Biblioteca
        </a>

        <?php if(isset($_SESSION['rol_usuario']) && $_SESSION['rol_usuario'] === 'admin'): ?>
            <div class="nav-divider"></div>
            <a href="javascript:void(0)" onclick="switchTab('admin')" data-tab="admin" class="nav-item">
                <span class="icon"><i class="fas fa-lock"></i></span> Administración
            </a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php" class="nav-item">
            <span class="icon"><i class="fas fa-right-from-bracket"></i></span> Salir
        </a>
    </div>
</aside>
