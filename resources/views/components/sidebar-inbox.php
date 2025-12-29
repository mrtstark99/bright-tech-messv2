<!-- Inbox Local Sidebar - With Real Data -->
<aside class="sidebar-local">
    <div class="sidebar-local-header">
        <h2 class="sidebar-local-title">Tin nhắn</h2>
        <button class="btn btn-sm btn-ghost">
            <i data-lucide="filter" style="width: 16px; height: 16px;"></i>
        </button>
    </div>
    
    <div class="sidebar-local-content">
        <!-- Search -->
        <div class="input-group mb-4">
            <span class="input-group-icon">
                <i data-lucide="search"></i>
            </span>
            <input type="text" class="input" placeholder="Tìm cuộc hội thoại..." id="search-conversations">
        </div>
        
        <!-- Quick Filters -->
        <div class="nav-group">
            <div class="nav-group-title">Bộ lọc</div>
            <div class="nav-group-items">
                <div class="local-nav-item active" data-filter="all">
                    <i data-lucide="inbox"></i>
                    <span>Tất cả</span>
                    <span class="badge"><?= $stats['total'] ?? 0 ?></span>
                </div>
                <div class="local-nav-item" data-filter="unread">
                    <i data-lucide="circle-dot"></i>
                    <span>Chưa đọc</span>
                    <span class="badge"><?= $stats['unread'] ?? 0 ?></span>
                </div>
                <div class="local-nav-item" data-filter="ai">
                    <i data-lucide="bot"></i>
                    <span>AI đang xử lý</span>
                    <span class="badge"><?= $stats['ai_enabled'] ?? 0 ?></span>
                </div>
            </div>
        </div>
        
        <!-- Pages Filter -->
        <?php if (!empty($pages)): ?>
        <div class="nav-group">
            <div class="nav-group-title">Fanpages</div>
            <div class="nav-group-items">
                <?php foreach ($pages as $page): ?>
                <div class="local-nav-item" data-page-filter="<?= htmlspecialchars($page['page_id']) ?>">
                    <img src="<?= htmlspecialchars($page['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($page['name']) . '&size=32') ?>" class="avatar-sm">
                    <span><?= htmlspecialchars($page['name']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</aside>
