<!-- Settings Page -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="font-size: 24px;">Cài đặt</h1>
        <p class="text-secondary mt-1">Quản lý Facebook App, Fanpages và tích hợp</p>
    </div>
</div>

<!-- Settings Tabs -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--color-border-light);">
        <div class="tabs" id="settings-tabs">
            <div class="tab active" data-tab="facebook">
                <i data-lucide="facebook" style="width: 16px; height: 16px;"></i>
                Facebook
            </div>
            <div class="tab" data-tab="pages">
                <i data-lucide="file-text" style="width: 16px; height: 16px;"></i>
                Fanpages
            </div>
            <div class="tab" data-tab="ai">
                <i data-lucide="bot" style="width: 16px; height: 16px;"></i>
                AI Config
            </div>
            <div class="tab" data-tab="n8n">
                <i data-lucide="workflow" style="width: 16px; height: 16px;"></i>
                n8n
            </div>
        </div>
    </div>
    
    <div class="card-body" style="padding: 32px;">
        <!-- Facebook Tab -->
        <div class="tab-content active" id="tab-facebook">
            <div style="max-width: 640px;">
                <h3 class="font-semibold mb-4" style="font-size: 16px;">Cấu hình Facebook App</h3>
                
                <!-- App Info -->
                <div class="mb-6">
                    <div style="background: var(--color-surface-hover); padding: 16px; border-radius: 12px; margin-bottom: 16px;">
                        <div class="flex items-center gap-3 mb-3">
                            <div style="width: 40px; height: 40px; background: #1877f2; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="facebook" style="color: #fff; width: 20px; height: 20px;"></i>
                            </div>
                            <div>
                                <div class="font-semibold">Facebook Developer</div>
                                <div class="text-sm text-muted">Cấu hình trong file .env</div>
                            </div>
                        </div>
                        
                        <div style="display: grid; gap: 12px; font-size: 13px;">
                            <div class="flex justify-between">
                                <span class="text-muted">App ID:</span>
                                <code style="background: var(--color-surface); padding: 2px 8px; border-radius: 4px;">
                                    <?= htmlspecialchars($facebookConfig['app_id'] ?: 'Chưa cấu hình') ?>
                                </code>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted">App Secret:</span>
                                <code style="background: var(--color-surface); padding: 2px 8px; border-radius: 4px;">
                                    <?= $facebookConfig['app_secret'] ?: 'Chưa cấu hình' ?>
                                </code>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Webhook Info -->
                <div class="mb-6">
                    <h4 class="font-medium mb-3">Webhook Configuration</h4>
                    <div style="background: var(--color-primary-lighter); padding: 16px; border-radius: 12px;">
                        <p class="text-sm mb-3">Sử dụng thông tin sau trong Facebook Developer Console:</p>
                        
                        <div style="display: grid; gap: 12px;">
                            <div>
                                <label class="text-sm text-muted">Callback URL:</label>
                                <div class="input-group mt-1">
                                    <input type="text" class="input" value="<?= htmlspecialchars($facebookConfig['webhook_url']) ?>" readonly id="webhook-url">
                                    <button class="btn btn-sm btn-ghost" onclick="copyToClipboard('webhook-url')">
                                        <i data-lucide="copy" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="text-sm text-muted">Verify Token:</label>
                                <div class="input-group mt-1">
                                    <input type="text" class="input" value="<?= htmlspecialchars($facebookConfig['webhook_token']) ?>" readonly id="verify-token">
                                    <button class="btn btn-sm btn-ghost" onclick="copyToClipboard('verify-token')">
                                        <i data-lucide="copy" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Get Page Tokens -->
                <div class="mb-6">
                    <h4 class="font-medium mb-3">Lấy Page Access Tokens</h4>
                    <p class="text-sm text-muted mb-3">
                        Nhập User Access Token để lấy tokens của tất cả Fanpages bạn quản lý.
                        <a href="https://developers.facebook.com/tools/explorer/" target="_blank" style="color: var(--color-primary);">Lấy token tại đây →</a>
                    </p>
                    
                    <div class="input-group mb-3">
                        <input type="text" class="input" placeholder="Paste User Access Token..." id="user-access-token">
                    </div>
                    <button class="btn btn-primary" onclick="getPageTokens()" id="get-tokens-btn">
                        <i data-lucide="download"></i>
                        Lấy Page Tokens
                    </button>
                    
                    <div id="tokens-result" style="margin-top: 16px; display: none;"></div>
                </div>
            </div>
        </div>
        
        <!-- Pages Tab -->
        <div class="tab-content" id="tab-pages" style="display: none;">
            <h3 class="font-semibold mb-4" style="font-size: 16px;">Quản lý Fanpages</h3>
            
            <?php if (empty($pages)): ?>
            <div class="empty-state" style="padding: 60px;">
                <div class="empty-state-icon">
                    <i data-lucide="file-text"></i>
                </div>
                <div class="empty-state-title">Chưa có Fanpage nào</div>
                <div class="empty-state-text">Vào tab Facebook để lấy Page Tokens</div>
            </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fanpage</th>
                            <th>Page ID</th>
                            <th>Tin nhắn</th>
                            <th>Token</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $page): ?>
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <img src="<?= htmlspecialchars($page['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($page['name'])) ?>" class="avatar-sm">
                                    <span class="font-medium"><?= htmlspecialchars($page['name']) ?></span>
                                </div>
                            </td>
                            <td><code style="font-size: 12px;"><?= htmlspecialchars($page['page_id']) ?></code></td>
                            <td>
                                <span class="badge"><?= $page['conversation_count'] ?? 0 ?> cuộc hội thoại</span>
                            </td>
                            <td>
                                <?php if (!empty($page['access_token'])): ?>
                                <span class="badge badge-success">
                                    <i data-lucide="check" style="width: 12px; height: 12px;"></i>
                                    Có token
                                </span>
                                <?php else: ?>
                                <span class="badge badge-warning">Chưa có</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <label class="toggle">
                                    <input type="checkbox" <?= $page['is_active'] ? 'checked' : '' ?> onchange="togglePage('<?= $page['page_id'] ?>')">
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn btn-sm btn-ghost" onclick="subscribePage('<?= $page['page_id'] ?>')" title="Đăng ký Webhook">
                                        <i data-lucide="webhook" style="width: 14px; height: 14px;"></i>
                                    </button>
                                    <button class="btn btn-sm btn-ghost" onclick="deletePage('<?= $page['page_id'] ?>')" title="Xóa" style="color: var(--color-error);">
                                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- AI Tab -->
        <div class="tab-content" id="tab-ai" style="display: none;">
            <div style="max-width: 640px;">
                <h3 class="font-semibold mb-4" style="font-size: 16px;">Cấu hình AI</h3>
                
                <div style="background: var(--color-surface-hover); padding: 20px; border-radius: 12px; margin-bottom: 24px;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div style="width: 40px; height: 40px; background: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="bot" style="color: #fff; width: 20px; height: 20px;"></i>
                            </div>
                            <div>
                                <div class="font-semibold">AI Auto-Reply</div>
                                <div class="text-sm text-muted">Tự động trả lời tin nhắn</div>
                            </div>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" <?= $aiConfig['enabled'] ? 'checked' : '' ?> id="ai-enabled">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div style="display: grid; gap: 12px; font-size: 13px;">
                        <div class="flex justify-between">
                            <span class="text-muted">Provider:</span>
                            <span class="font-medium"><?= ucfirst($aiConfig['provider']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Model:</span>
                            <span class="font-medium"><?= $aiConfig['model'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">API Key:</span>
                            <span class="font-medium <?= $aiConfig['has_key'] ? 'text-success' : 'text-error' ?>">
                                <?= $aiConfig['has_key'] ? '✓ Đã cấu hình' : '✗ Chưa cấu hình' ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div style="background: #fef3c7; padding: 16px; border-radius: 12px; border-left: 4px solid #d97706;">
                    <div class="flex gap-3">
                        <i data-lucide="info" style="width: 20px; height: 20px; color: #d97706; flex-shrink: 0;"></i>
                        <div class="text-sm">
                            <strong>Lưu ý:</strong> Cấu hình API Key trong file <code>.env</code> để bảo mật. 
                            Không lưu API keys trong database.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- n8n Tab -->
        <div class="tab-content" id="tab-n8n" style="display: none;">
            <div style="max-width: 640px;">
                <h3 class="font-semibold mb-4" style="font-size: 16px;">Tích hợp n8n</h3>
                
                <div style="background: var(--color-surface-hover); padding: 20px; border-radius: 12px; margin-bottom: 24px;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div style="width: 40px; height: 40px; background: #ea4b71; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="workflow" style="color: #fff; width: 20px; height: 20px;"></i>
                            </div>
                            <div>
                                <div class="font-semibold">n8n Automation</div>
                                <div class="text-sm text-muted">Kết nối với n8n workflows</div>
                            </div>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" <?= $n8nConfig['enabled'] ? 'checked' : '' ?> id="n8n-enabled">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <?php if ($n8nConfig['enabled'] && $n8nConfig['webhook_url']): ?>
                    <div style="font-size: 13px;">
                        <div class="flex justify-between">
                            <span class="text-muted">Webhook URL:</span>
                            <code style="font-size: 11px;"><?= htmlspecialchars($n8nConfig['webhook_url']) ?></code>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="text-sm text-muted">Cấu hình N8N_WEBHOOK_URL trong file .env để kích hoạt.</p>
                    <?php endif; ?>
                </div>
                
                <div style="background: var(--color-primary-lighter); padding: 16px; border-radius: 12px;">
                    <h4 class="font-medium mb-2">Hướng dẫn tích hợp</h4>
                    <ol class="text-sm" style="padding-left: 20px; color: var(--color-text-secondary);">
                        <li style="margin-bottom: 8px;">Tạo workflow mới trong n8n</li>
                        <li style="margin-bottom: 8px;">Thêm Webhook trigger node</li>
                        <li style="margin-bottom: 8px;">Copy webhook URL vào .env</li>
                        <li>Kích hoạt trong Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tab switching
document.querySelectorAll('#settings-tabs .tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Remove active from all tabs
        document.querySelectorAll('#settings-tabs .tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        
        // Activate clicked tab
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).style.display = 'block';
    });
});

// Copy to clipboard
function copyToClipboard(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    document.execCommand('copy');
    showNotification('Đã copy!', 'success');
}

// Get page tokens
async function getPageTokens() {
    const token = document.getElementById('user-access-token').value.trim();
    if (!token) {
        showNotification('Vui lòng nhập User Access Token', 'error');
        return;
    }
    
    const btn = document.getElementById('get-tokens-btn');
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin"></i> Đang xử lý...';
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/settings/page-tokens', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_access_token: token })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(`Đã lưu ${data.pages_count} Fanpages!`, 'success');
            document.getElementById('tokens-result').innerHTML = `
                <div style="background: #dcfce7; padding: 16px; border-radius: 12px;">
                    <div class="font-medium mb-2">✓ Đã lấy ${data.pages_count} Fanpages:</div>
                    <ul style="font-size: 13px; padding-left: 20px;">
                        ${data.pages.map(p => `<li>${p.name}</li>`).join('')}
                    </ul>
                    <button class="btn btn-sm btn-primary mt-3" onclick="location.reload()">Làm mới trang</button>
                </div>
            `;
            document.getElementById('tokens-result').style.display = 'block';
        } else {
            showNotification(data.error || 'Lỗi khi lấy tokens', 'error');
        }
    } catch (e) {
        showNotification('Lỗi kết nối', 'error');
    }
    
    btn.disabled = false;
    btn.innerHTML = '<i data-lucide="download"></i> Lấy Page Tokens';
    lucide.createIcons();
}

// Toggle page
async function togglePage(pageId) {
    try {
        const response = await fetch('<?= APP_URL ?>/api/settings/toggle-page', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ page_id: pageId })
        });
        const data = await response.json();
        if (data.success) {
            showNotification('Đã cập nhật trạng thái', 'success');
        }
    } catch (e) {
        showNotification('Lỗi kết nối', 'error');
    }
}

// Subscribe page to webhook
async function subscribePage(pageId) {
    try {
        const response = await fetch('<?= APP_URL ?>/api/settings/subscribe-page', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ page_id: pageId })
        });
        const data = await response.json();
        if (data.success) {
            showNotification('Đã đăng ký Webhook!', 'success');
        } else {
            showNotification(data.error || 'Lỗi đăng ký', 'error');
        }
    } catch (e) {
        showNotification('Lỗi kết nối', 'error');
    }
}

// Delete page
async function deletePage(pageId) {
    if (!confirm('Bạn có chắc muốn xóa Fanpage này?')) return;
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/settings/delete-page', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ page_id: pageId })
        });
        const data = await response.json();
        if (data.success) {
            showNotification('Đã xóa Fanpage', 'success');
            location.reload();
        }
    } catch (e) {
        showNotification('Lỗi kết nối', 'error');
    }
}

// Notification helper
function showNotification(message, type = 'info') {
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#3b82f6'
    };
    
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colors[type]};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.remove(), 3000);
}
</script>
