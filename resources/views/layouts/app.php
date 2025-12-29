<!DOCTYPE html>
<?php require_once APP_PATH . '/Helpers/AvatarHelper.php'; ?>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MessV2' ?> - Fanpage Manager</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/build/app.css">
    
    <!-- Icons (Lucide) -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>
    <div class="app-layout">
        <!-- Global Sidebar (Left - Icons) -->
        <?php require RESOURCES_PATH . '/views/components/sidebar-global.php'; ?>
        
        <!-- Local Sidebar (Module-specific) -->
        <?php 
        $localSidebarFile = RESOURCES_PATH . '/views/components/sidebar-' . ($activeNav ?? 'dashboard') . '.php';
        if (file_exists($localSidebarFile)) {
            require $localSidebarFile;
            $hasLocalSidebar = true;
        } else {
            $hasLocalSidebar = false;
        }
        ?>
        
        <!-- Main Content -->
        <main class="main-content <?= !$hasLocalSidebar ? 'no-local-sidebar' : '' ?>">
            <?php 
            $pageFile = RESOURCES_PATH . '/views/' . str_replace('.', '/', $_viewPage ?? 'pages/dashboard') . '.php';
            if (file_exists($pageFile)) {
                require $pageFile;
            } else {
                echo "<p>Page not found: {$_viewPage}</p>";
            }
            ?>
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="<?= APP_URL ?>/public/build/app.js" type="module"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Dark Mode Functions
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeIcons(isDark);
        }
        
        function updateThemeIcons(isDark) {
            const moonIcon = document.getElementById('theme-icon-moon');
            const sunIcon = document.getElementById('theme-icon-sun');
            if (moonIcon && sunIcon) {
                moonIcon.style.display = isDark ? 'none' : 'block';
                sunIcon.style.display = isDark ? 'block' : 'none';
            }
        }
        
        // Initialize theme on page load
        (function initTheme() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = savedTheme === 'dark' || (!savedTheme && prefersDark);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
            }
            
            // Wait for DOM to update icons
            setTimeout(() => updateThemeIcons(isDark), 100);
        })();
    </script>
</body>
</html>

