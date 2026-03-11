<div class="sidebar-overlay" id="sidebar-overlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <img src="assets/logo.png" alt="Logo">
        <span>DebiHaby</span>
    </div>
    <nav class="sidebar-nav">
        <!-- Dashboard Link -->
        <a href="javascript:void(0)" onclick="switchTab('dashboard')" class="nav-item active">
            <span class="icon"><i class="fas fa-th-large"></i></span> Dashboard
        </a>
        
        <!-- Courses Link -->
        <a href="javascript:void(0)" onclick="switchTab('dashboard')" class="nav-item">
            <span class="icon"><i class="fas fa-graduation-cap"></i></span> Mis Cursos
        </a>
        
        <!-- Achievements Link -->
        <a href="javascript:void(0)" onclick="switchTab('certification')" class="nav-item">
            <span class="icon"><i class="fas fa-medal"></i></span> Logros
        </a>
        
        <!-- Stats Link -->
        <a href="javascript:void(0)" onclick="switchTab('stats')" class="nav-item">
            <span class="icon"><i class="fas fa-chart-bar"></i></span> Estadísticas
        </a>

        <!-- New Modules -->
        <div class="nav-divider"></div>
        <p class="nav-section-title">Aprende Más</p>

        <a href="javascript:void(0)" onclick="switchTab('news')" class="nav-item">
            <span class="icon"><i class="fas fa-newspaper"></i></span> Noticias
        </a>

        <a href="javascript:void(0)" onclick="switchTab('experts')" class="nav-item">
            <span class="icon"><i class="fas fa-user-tie"></i></span> Expertos
        </a>

        <a href="javascript:void(0)" onclick="switchTab('myths')" class="nav-item">
            <span class="icon"><i class="fas fa-brain"></i></span> Mitos
        </a>

        <a href="javascript:void(0)" onclick="switchTab('resources')" class="nav-item">
            <span class="icon"><i class="fas fa-book-atlas"></i></span> Directorio
        </a>

        <a href="javascript:void(0)" onclick="switchTab('tools')" class="nav-item">
            <span class="icon"><i class="fas fa-calculator"></i></span> Calculadoras
        </a>

        <a href="javascript:void(0)" onclick="switchTab('library')" class="nav-item">
            <span class="icon"><i class="fas fa-file-pdf"></i></span> Biblioteca
        </a>

        <?php if(isset($userRole) && $userRole === 'admin'): ?>
            <div class="nav-divider"></div>
            <a href="javascript:void(0)" onclick="switchTab('admin')" class="nav-item">
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
