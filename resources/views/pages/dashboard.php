<!-- Dashboard Page - With Real Data -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="font-size: 24px;">Dashboard</h1>
        <p class="text-secondary mt-1">Tổng quan hoạt động Fanpage của bạn</p>
    </div>
    <div class="flex gap-2">
        <button class="btn btn-secondary">
            <i data-lucide="download"></i>
            Xuất báo cáo
        </button>
        <button class="btn btn-primary">
            <i data-lucide="plus"></i>
            Thêm Fanpage
        </button>
    </div>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;" class="mb-6">
    <!-- Total Messages -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <span class="text-secondary text-sm">Tổng tin nhắn</span>
                <div style="width: 40px; height: 40px; background: var(--color-primary-lighter); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="message-circle" style="color: var(--color-primary); width: 20px; height: 20px;"></i>
                </div>
            </div>
            <div style="font-size: 28px; font-weight: 700;"><?= number_format($stats['totalMessages'] ?? 0) ?></div>
            <div class="text-sm mt-1">
                <span class="text-muted">Tất cả tin nhắn</span>
            </div>
        </div>
    </div>
    
    <!-- Unread -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <span class="text-secondary text-sm">Chưa đọc</span>
                <div style="width: 40px; height: 40px; background: #fee2e2; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="mail" style="color: #dc2626; width: 20px; height: 20px;"></i>
                </div>
            </div>
            <div style="font-size: 28px; font-weight: 700;"><?= number_format($stats['unreadCount'] ?? 0) ?></div>
            <div class="text-sm mt-1">
                <span class="text-muted">Cần xử lý</span>
            </div>
        </div>
    </div>
    
    <!-- AI Responses -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <span class="text-secondary text-sm">AI đã trả lời</span>
                <div style="width: 40px; height: 40px; background: #dcfce7; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="bot" style="color: #16a34a; width: 20px; height: 20px;"></i>
                </div>
            </div>
            <div style="font-size: 28px; font-weight: 700;"><?= number_format($stats['aiResponses'] ?? 0) ?></div>
            <div class="text-sm mt-1">
                <?php 
                $aiPercent = ($stats['totalMessages'] > 0) 
                    ? round(($stats['aiResponses'] / $stats['totalMessages']) * 100) 
                    : 0;
                ?>
                <span style="color: var(--color-success);"><?= $aiPercent ?>%</span>
                <span class="text-muted">tỷ lệ tự động</span>
            </div>
        </div>
    </div>
    
    <!-- Active Pages -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <span class="text-secondary text-sm">Fanpages</span>
                <div style="width: 40px; height: 40px; background: #fef3c7; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="file" style="color: #d97706; width: 20px; height: 20px;"></i>
                </div>
            </div>
            <div style="font-size: 28px; font-weight: 700;"><?= $stats['pageCount'] ?? 0 ?></div>
            <div class="text-sm mt-1">
                <span style="color: var(--color-success);"><?= $stats['activePages'] ?? 0 ?></span>
                <span class="text-muted">đang hoạt động</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <!-- Recent Conversations -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tin nhắn gần đây</h3>
            <a href="<?= APP_URL ?>/inbox" class="btn btn-sm btn-ghost">
                Xem tất cả
                <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($recentConversations)): ?>
            <div class="empty-state" style="padding: 40px;">
                <div class="empty-state-icon">
                    <i data-lucide="inbox"></i>
                </div>
                <div class="empty-state-title">Chưa có tin nhắn</div>
                <div class="empty-state-text">Kết nối Fanpage để bắt đầu nhận tin nhắn</div>
            </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Khách hàng</th>
                            <th>Tin nhắn</th>
                            <th>Fanpage</th>
                            <th>Thời gian</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentConversations as $conv): ?>
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <img src="<?= avatar($conv['avatar'] ?? null, $conv['user_id'] ?? $conv['user_name'] ?? 'user', 32) ?>" class="avatar-sm">
                                    <span class="font-medium"><?= htmlspecialchars($conv['user_name'] ?? 'Người dùng') ?></span>
                                    <?php if (($conv['unread_count'] ?? 0) > 0): ?>
                                    <span style="background: var(--color-error); color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 10px;"><?= $conv['unread_count'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= htmlspecialchars($conv['last_message'] ?? '') ?>
                            </td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($conv['page_name'] ?? 'Unknown') ?></span></td>
                            <td class="text-muted">
                                <?php 
                                $time = strtotime($conv['last_message_at'] ?? 'now');
                                $diff = time() - $time;
                                if ($diff < 60) echo 'vừa xong';
                                elseif ($diff < 3600) echo round($diff / 60) . ' phút';
                                elseif ($diff < 86400) echo round($diff / 3600) . ' giờ';
                                else echo round($diff / 86400) . ' ngày';
                                ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= APP_URL ?>/inbox/<?= $conv['id'] ?>" class="table-action-btn">
                                        <i data-lucide="message-circle" style="width: 16px; height: 16px;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Actions & Pages List -->
    <div class="flex flex-col gap-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hành động nhanh</h3>
            </div>
            <div class="card-body">
                <div class="flex flex-col gap-3">
                    <a href="<?= APP_URL ?>/inbox" class="btn btn-secondary w-full" style="justify-content: flex-start;">
                        <i data-lucide="send"></i>
                        Gửi tin nhắn hàng loạt
                    </a>
                    <a href="<?= APP_URL ?>/automation" class="btn btn-secondary w-full" style="justify-content: flex-start;">
                        <i data-lucide="zap"></i>
                        Tạo kịch bản AI
                    </a>
                    <a href="<?= APP_URL ?>/settings" class="btn btn-secondary w-full" style="justify-content: flex-start;">
                        <i data-lucide="plus-circle"></i>
                        Thêm Fanpage mới
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Active Pages -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Fanpages</h3>
                <a href="<?= APP_URL ?>/pages" class="btn btn-sm btn-ghost">
                    Quản lý
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($pages)): ?>
                <div style="padding: 24px; text-align: center;">
                    <p class="text-muted text-sm">Chưa có Fanpage nào</p>
                </div>
                <?php else: ?>
                <div style="padding: 8px 0;">
                    <?php foreach ($pages as $page): ?>
                    <div style="padding: 12px 24px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--color-border-light);">
                        <img src="<?= htmlspecialchars($page['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($page['name'])) ?>" class="avatar-sm">
                        <div style="flex: 1;">
                            <div class="font-medium"><?= htmlspecialchars($page['name']) ?></div>
                            <div class="text-sm text-muted">ID: <?= htmlspecialchars($page['page_id']) ?></div>
                        </div>
                        <span class="status-dot online"></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
