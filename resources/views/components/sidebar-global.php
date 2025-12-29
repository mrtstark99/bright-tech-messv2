<!-- Global Sidebar - Icon Navigation -->
<aside class="sidebar-global">
    <!-- Logo -->
    <div class="sidebar-logo">
        <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
            <rect width="36" height="36" rx="8" fill="#7c3aed"/>
            <path d="M10 18L16 24L26 12" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
    
    <!-- Navigation -->
    <nav class="sidebar-nav">
        <!-- Main Section -->
        <a href="<?= APP_URL ?>/" class="nav-item <?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span class="tooltip">Dashboard</span>
        </a>
        
        <a href="<?= APP_URL ?>/inbox" class="nav-item <?= ($activeNav ?? '') === 'inbox' ? 'active' : '' ?>">
            <i data-lucide="inbox"></i>
            <span class="tooltip">Tin nhắn</span>
        </a>
        
        <a href="<?= APP_URL ?>/pages" class="nav-item <?= ($activeNav ?? '') === 'pages' ? 'active' : '' ?>">
            <i data-lucide="file-text"></i>
            <span class="tooltip">Fanpages</span>
        </a>
        
        <a href="<?= APP_URL ?>/posts" class="nav-item <?= ($activeNav ?? '') === 'posts' ? 'active' : '' ?>">
            <i data-lucide="calendar"></i>
            <span class="tooltip">Bài đăng</span>
        </a>
        
        <div class="nav-divider"></div>
        
        <!-- Advanced Section -->
        <a href="<?= APP_URL ?>/automation" class="nav-item <?= ($activeNav ?? '') === 'automation' ? 'active' : '' ?>">
            <i data-lucide="zap"></i>
            <span class="tooltip">Automation</span>
        </a>
        
        <a href="<?= APP_URL ?>/tools" class="nav-item <?= ($activeNav ?? '') === 'tools' ? 'active' : '' ?>">
            <i data-lucide="wrench"></i>
            <span class="tooltip">Công cụ</span>
        </a>
        
        <div class="nav-divider"></div>
        
        <!-- Settings -->
        <a href="<?= APP_URL ?>/settings" class="nav-item <?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>">
            <i data-lucide="settings"></i>
            <span class="tooltip">Cài đặt</span>
        </a>
    </nav>
    
    <!-- Theme Toggle + User at bottom -->
    <div style="padding: 16px; margin-top: auto; display: flex; flex-direction: column; gap: 8px;">
        <!-- Dark Mode Toggle -->
        <button onclick="toggleTheme()" class="nav-item" id="theme-toggle" title="Chuyển chế độ sáng/tối">
            <i data-lucide="moon" id="theme-icon-moon"></i>
            <i data-lucide="sun" id="theme-icon-sun" style="display: none;"></i>
            <span class="tooltip">Dark Mode</span>
        </button>
        
        <!-- User Avatar -->
        <a href="<?= APP_URL ?>/settings" class="nav-item">
            <img src="https://ui-avatars.com/api/?name=Admin&background=7c3aed&color=fff" 
                 alt="Admin" 
                 class="avatar-sm" 
                 style="border-radius: 50%;">
        </a>
    </div>
</aside>

