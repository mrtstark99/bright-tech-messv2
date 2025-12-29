<!-- Inbox Page - With Real Data -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="font-size: 24px;">Tin nhắn</h1>
        <p class="text-secondary mt-1">Quản lý tất cả cuộc hội thoại</p>
    </div>
    <div class="flex gap-2">
        <button class="btn btn-secondary">
            <i data-lucide="tag"></i>
            Gắn nhãn
        </button>
        <button class="btn btn-primary">
            <i data-lucide="send"></i>
            Gửi tin mới
        </button>
    </div>
</div>

<!-- Main Chat Interface -->
<div class="card" style="height: calc(100vh - 180px); display: flex; flex-direction: column;">
    <!-- Tabs -->
    <div class="card-header" style="border-bottom: 1px solid var(--color-border-light);">
        <div class="tabs">
            <div class="tab active">Tất cả</div>
            <div class="tab">Chưa đọc</div>
            <div class="tab">AI Mode</div>
        </div>
        <div class="flex gap-2">
            <div class="input-group" style="width: 240px;">
                <span class="input-group-icon">
                    <i data-lucide="search"></i>
                </span>
                <input type="text" class="input" placeholder="Tìm kiếm...">
            </div>
        </div>
    </div>
    
    <!-- Chat Layout: List + Detail -->
    <div style="display: flex; flex: 1; overflow: hidden;">
        <!-- Conversation List -->
        <div style="width: 360px; border-right: 1px solid var(--color-border-light); overflow-y: auto;" id="conversation-list">
            <?php if (empty($conversations)): ?>
            <div class="empty-state" style="padding: 40px;">
                <div class="empty-state-icon">
                    <i data-lucide="inbox"></i>
                </div>
                <div class="empty-state-title">Chưa có cuộc hội thoại</div>
                <div class="empty-state-text">Các tin nhắn từ khách hàng sẽ xuất hiện ở đây</div>
            </div>
            <?php else: ?>
                <?php foreach ($conversations as $index => $conv): 
                    $isActive = ($activeConversation && $activeConversation['id'] == $conv['id']);
                ?>
                <a href="<?= APP_URL ?>/inbox/<?= $conv['id'] ?>" 
                   style="display: block; padding: 16px 20px; cursor: pointer; text-decoration: none; color: inherit;
                          <?= $isActive ? 'background: var(--color-primary-lighter); border-left: 3px solid var(--color-primary);' : 'border-bottom: 1px solid var(--color-border-light);' ?>"
                   onmouseover="if(!<?= $isActive ? 'true' : 'false' ?>)this.style.background='var(--color-surface-hover)'" 
                   onmouseout="if(!<?= $isActive ? 'true' : 'false' ?>)this.style.background='transparent'">
                    <div class="flex items-center gap-3">
                        <div style="position: relative;">
                            <img src="<?= avatar($conv['avatar'] ?? null, $conv['user_id'] ?? $conv['user_name'] ?? 'user', 40) ?>" class="avatar border-tier-<?= $conv['border_level'] ?? 0 ?>">
                            <?php if (($conv['unread_count'] ?? 0) > 0): ?>
                            <span style="position: absolute; top: -2px; right: -2px; min-width: 18px; height: 18px; background: var(--color-error); color: #fff; font-size: 10px; font-weight: 600; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid var(--color-surface); padding: 0 4px;"><?= $conv['unread_count'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold"><?= htmlspecialchars($conv['user_name'] ?? 'Người dùng') ?></span>
                                <span class="text-sm text-muted">
                                    <?php 
                                    $time = strtotime($conv['last_message_at'] ?? 'now');
                                    $diff = time() - $time;
                                    if ($diff < 60) echo 'vừa xong';
                                    elseif ($diff < 3600) echo round($diff / 60) . ' phút';
                                    elseif ($diff < 86400) echo round($diff / 3600) . ' giờ';
                                    else echo round($diff / 86400) . ' ngày';
                                    ?>
                                </span>
                            </div>
                            <div style="font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--color-text-secondary); <?= ($conv['unread_count'] ?? 0) > 0 ? 'font-weight: 500; color: var(--color-text-primary);' : '' ?>">
                                <?= htmlspecialchars($conv['last_message'] ?? '') ?>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="badge badge-primary" style="font-size: 10px; padding: 2px 6px;"><?= htmlspecialchars($conv['page_name'] ?? 'Page') ?></span>
                                <?php if ($conv['ai_mode'] ?? false): ?>
                                <span class="badge badge-success" style="font-size: 10px; padding: 2px 6px;">
                                    <i data-lucide="bot" style="width: 10px; height: 10px;"></i>
                                    AI
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Chat Detail -->
        <?php if ($activeConversation): ?>
        <div style="flex: 1; display: flex; flex-direction: column;">
            <!-- Chat Header -->
            <div style="padding: 16px 24px; border-bottom: 1px solid var(--color-border-light); display: flex; align-items: center; justify-content: space-between;">
                <div class="flex items-center gap-3">
                    <img src="<?= avatar($activeConversation['avatar'] ?? null, $activeConversation['user_id'] ?? $activeConversation['user_name'] ?? 'user', 40) ?>" class="avatar border-tier-<?= $activeConversation['border_level'] ?? 0 ?>">
                    <div>
                        <div class="font-semibold"><?= htmlspecialchars($activeConversation['user_name'] ?? 'Người dùng') ?></div>
                        <div class="text-sm text-muted flex items-center gap-2">
                            <span class="status-dot online"></span>
                            <?= htmlspecialchars($activeConversation['page_name'] ?? '') ?>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <label class="toggle">
                        <input type="checkbox" <?= ($activeConversation['ai_mode'] ?? false) ? 'checked' : '' ?> id="ai-mode-toggle" data-conversation="<?= $activeConversation['id'] ?>">
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="text-sm text-secondary">AI Mode</span>
                    <button class="btn btn-sm btn-ghost">
                        <i data-lucide="more-vertical"></i>
                    </button>
                </div>
            </div>
            
            <!-- Messages -->
            <div style="flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column; gap: 16px;" id="messages-container">
                <?php if (empty($messages)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i data-lucide="message-circle"></i>
                    </div>
                    <div class="empty-state-title">Bắt đầu cuộc trò chuyện</div>
                    <div class="empty-state-text">Gửi tin nhắn đầu tiên để bắt đầu</div>
                </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): 
                        $isUser = ($msg['sender_type'] === 'user');
                        $isAi = ($msg['sender_type'] === 'ai');
                    ?>
                    <?php if ($isUser): ?>
                    <!-- User Message -->
                    <div style="display: flex; gap: 12px; max-width: 70%;">
                        <img src="<?= avatar($activeConversation['avatar'] ?? null, $activeConversation['user_id'] ?? $activeConversation['user_name'] ?? 'user', 32) ?>" class="avatar-sm border-tier-<?= $activeConversation['border_level'] ?? 0 ?>" style="flex-shrink: 0;">
                        <div>
                            <div style="background: var(--color-surface-hover); padding: 12px 16px; border-radius: 12px 12px 12px 0;">
                                <?= nl2br(htmlspecialchars($msg['content'])) ?>
                            </div>
                            <div class="text-sm text-muted mt-1">
                                <?= date('H:i', strtotime($msg['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Admin/AI Message -->
                    <div style="display: flex; gap: 12px; max-width: 70%; margin-left: auto; flex-direction: row-reverse;">
                        <img src="https://ui-avatars.com/api/?name=<?= $isAi ? 'AI' : 'Admin' ?>&background=<?= $isAi ? '10b981' : '7c3aed' ?>&color=fff&size=32" class="avatar-sm" style="flex-shrink: 0;">
                        <div>
                            <div style="background: var(--color-primary); color: #fff; padding: 12px 16px; border-radius: 12px 12px 0 12px;">
                                <?= nl2br(htmlspecialchars($msg['content'])) ?>
                            </div>
                            <div class="text-sm text-muted mt-1" style="text-align: right;">
                                <?= date('H:i', strtotime($msg['created_at'])) ?>
                                <?php if ($isAi): ?>
                                <span style="color: var(--color-success); margin-left: 4px;">
                                    <i data-lucide="bot" style="width: 12px; height: 12px; display: inline;"></i>
                                    AI
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Message Input -->
            <div style="padding: 16px 24px; border-top: 1px solid var(--color-border-light);">
                <form id="send-message-form" data-conversation="<?= $activeConversation['id'] ?>">
                    <div style="display: flex; gap: 12px; align-items: flex-end;">
                        <div style="flex: 1;">
                            <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                                <button type="button" class="btn btn-sm btn-ghost">
                                    <i data-lucide="paperclip" style="width: 16px; height: 16px;"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-ghost">
                                    <i data-lucide="image" style="width: 16px; height: 16px;"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-ghost">
                                    <i data-lucide="smile" style="width: 16px; height: 16px;"></i>
                                </button>
                            </div>
                            <textarea class="input" name="message" placeholder="Nhập tin nhắn..." style="resize: none; min-height: 44px;" id="message-input"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="height: 44px;">
                            <i data-lucide="send"></i>
                            Gửi
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Right Panel: Customer Info -->
        <div style="width: 500px; border-left: 1px solid var(--color-border-light); overflow-y: auto; padding: 24px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <img src="<?= avatar($activeConversation['avatar'] ?? null, $activeConversation['user_id'] ?? $activeConversation['user_name'] ?? 'user', 80) ?>" class="avatar-lg border-tier-<?= $activeConversation['border_level'] ?? 0 ?>" style="width: 80px; height: 80px; margin-bottom: 12px;">
                <h3 class="font-semibold" style="font-size: 16px;"><?= htmlspecialchars($activeConversation['user_name'] ?? 'Người dùng') ?></h3>
                <p class="text-sm text-muted">User ID: <?= htmlspecialchars($activeConversation['user_id'] ?? '') ?></p>
            </div>
            
            <!-- Labels -->
            <div class="mb-4">
                <div class="text-sm font-semibold mb-2">Nhãn</div>
                <div class="flex gap-2" style="flex-wrap: wrap;" id="tags-container">
                    <?php 
                    $tags = json_decode($activeConversation['tags'] ?? '[]', true) ?: [];
                    foreach ($tags as $tag): 
                    ?>
                    <span class="badge badge-primary"><?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                    <button class="btn btn-sm btn-ghost" onclick="addTag()">
                        <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                    </button>
                </div>
            </div>
            
            <!-- AI Mode Toggle -->
            <div class="mb-4" style="padding: 12px; background: var(--color-surface-hover); border-radius: 8px;">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm font-semibold">🤖 AI Auto-Reply</div>
                    <button id="ai-toggle-btn" 
                            onclick="toggleAiMode(<?= $activeConversation['id'] ?>)" 
                            class="btn btn-sm <?= ($activeConversation['ai_mode'] ?? 0) ? 'btn-primary' : 'btn-secondary' ?>">
                        <span id="ai-toggle-text"><?= ($activeConversation['ai_mode'] ?? 0) ? 'AI: ON' : 'AI: OFF' ?></span>
                    </button>
                </div>
                
                <!-- Timer Dropdown -->
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs text-muted">Tự bật lại sau:</span>
                    <select id="ai-timer-select" class="input" style="padding: 4px 8px; font-size: 12px; width: auto;" 
                            onchange="setAiTimer(<?= $activeConversation['id'] ?>, this.value)">
                        <option value="0">-- Chọn --</option>
                        <option value="5">5 phút</option>
                        <option value="15">15 phút</option>
                        <option value="30">30 phút</option>
                        <option value="60">1 giờ</option>
                    </select>
                </div>
                
                <!-- Timer Countdown Display -->
                <div id="ai-timer-display" class="text-xs text-center mt-2" 
                     style="display: none; color: var(--color-primary); font-weight: 600;"></div>
            </div>
            
            <!-- Notes -->
            <div>
                <div class="text-sm font-semibold mb-2">Ghi chú</div>
                <textarea class="input" id="customer-notes" placeholder="Thêm ghi chú về khách hàng..." style="resize: none; min-height: 80px;" data-conversation="<?= $activeConversation['id'] ?>"><?= htmlspecialchars($activeConversation['notes'] ?? '') ?></textarea>
            </div>
            
            <!-- Summary with Markdown (AI-Powered) -->
            <div class="mt-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm font-semibold">AI Portrait</div>
                    <div class="flex gap-1">
                        <button class="btn btn-sm btn-ghost" onclick="toggleSummaryEdit()" title="Sửa thủ công">
                            <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Generate Button -->
                <button onclick="generateAISummary(<?= $activeConversation['id'] ?>)" id="btn-generate-summary" 
                        class="btn btn-primary btn-sm w-full mb-3" style="justify-content: center;">
                    <i data-lucide="sparkles" style="width: 14px; height: 14px;"></i>
                    <span id="generate-summary-text">Generate Summary</span>
                </button>
                
                <!-- View Mode (Markdown rendered) -->
                <div id="summary-view" class="markdown-content" style="min-height: 80px; padding: 12px; background: var(--color-surface-hover); border-radius: 8px; font-size: 13px; max-height: 300px; overflow-y: auto;">
                    <?php if (!empty($activeConversation['summary'])): ?>
                        <div id="summary-rendered"><?= $activeConversation['summary'] ?></div>
                    <?php else: ?>
                        <div class="text-muted" style="font-style: italic;">Click "Generate Summary" để AI phân tích hội thoại...</div>
                    <?php endif; ?>
                </div>
                
                <!-- Edit Mode -->
                <div id="summary-edit" style="display: none;">
                    <textarea class="input" id="summary-textarea" placeholder="Nhập tóm tắt cuộc hội thoại... (hỗ trợ Markdown)" style="resize: none; min-height: 120px; font-family: monospace; font-size: 12px;" data-conversation="<?= $activeConversation['id'] ?>"><?= htmlspecialchars($activeConversation['summary'] ?? '') ?></textarea>
                    <div class="flex gap-2 mt-2">
                        <button class="btn btn-sm btn-primary" onclick="saveSummary()">
                            <i data-lucide="check" style="width: 12px; height: 12px;"></i>
                            Lưu
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="cancelSummaryEdit()">Hủy</button>
                    </div>
                    <div class="text-muted mt-2" style="font-size: 10px;">
                        Hỗ trợ: **bold**, *italic*, - list, # heading, `code`
                    </div>
                </div>
            </div>
            
            <!-- Quick Info -->
            <div class="mt-4">
                <div class="text-sm font-semibold mb-2">Thông tin</div>
                <div style="font-size: 12px; color: var(--color-text-secondary);">
                    <div class="flex justify-between mb-2">
                        <span>Cuộc hội thoại:</span>
                        <span>#<?= $activeConversation['id'] ?></span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span>Tạo lúc:</span>
                        <span><?= date('d/m/Y H:i', strtotime($activeConversation['created_at'] ?? 'now')) ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Border Level Selector -->
            <div class="mt-4">
                <div class="text-sm font-semibold mb-2">VIP Level</div>
                <div class="flex gap-1" id="tier-selector" style="flex-wrap: wrap;">
                    <?php for ($tier = 0; $tier <= 5; $tier++): 
                        $isActive = ($activeConversation['border_level'] ?? 0) == $tier;
                    ?>
                    <button class="tier-btn <?= $isActive ? 'active' : '' ?>" 
                            data-tier="<?= $tier ?>" 
                            onclick="updateBorderLevel(<?= $activeConversation['id'] ?>, <?= $tier ?>)">
                        <div class="tier-preview"></div>
                        <span style="font-size: 10px;"><?= $tier ?></span>
                    </button>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div style="flex: 1; display: flex; align-items: center; justify-content: center;">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-lucide="message-square"></i>
                </div>
                <div class="empty-state-title">Chọn cuộc hội thoại</div>
                <div class="empty-state-text">Chọn một cuộc hội thoại từ danh sách bên trái để xem tin nhắn</div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-scroll to bottom of messages
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
});

// Update border level
async function updateBorderLevel(conversationId, tier) {
    try {
        const response = await fetch('<?= APP_URL ?>/api/messages/border-level', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ conversation_id: conversationId, border_level: tier })
        });
        const data = await response.json();
        if (data.success) {
            // Update button states
            document.querySelectorAll('.tier-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`.tier-btn[data-tier="${tier}"]`).classList.add('active');
            
            // Only update avatars in the right panel (customer info) and chat area
            // These are the avatars for the current conversation only
            const rightPanel = document.querySelector('div[style*="width: 500px"]');
            const chatHeader = document.querySelector('div[style*="padding: 16px 24px"]');
            
            if (rightPanel) {
                rightPanel.querySelectorAll('.avatar, .avatar-sm, .avatar-lg').forEach(img => {
                    for (let i = 0; i <= 5; i++) {
                        img.classList.remove('border-tier-' + i);
                    }
                    img.classList.add('border-tier-' + tier);
                });
            }
            
            if (chatHeader) {
                chatHeader.querySelectorAll('.avatar').forEach(img => {
                    for (let i = 0; i <= 5; i++) {
                        img.classList.remove('border-tier-' + i);
                    }
                    img.classList.add('border-tier-' + tier);
                });
            }
            
            // Update message avatars (user messages only)
            document.querySelectorAll('#messages-container > div:nth-child(odd) .avatar-sm').forEach(img => {
                for (let i = 0; i <= 5; i++) {
                    img.classList.remove('border-tier-' + i);
                }
                img.classList.add('border-tier-' + tier);
            });
            
            showNotification('Đã cập nhật VIP Level!', 'success');
        }
    } catch (e) {
        showNotification('Lỗi kết nối', 'error');
    }
}

// Notification helper
function showNotification(message, type = 'info') {
    const colors = { success: '#10b981', error: '#ef4444', info: '#3b82f6' };
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px;
        background: ${colors[type]}; color: white;
        padding: 12px 20px; border-radius: 8px;
        font-size: 14px; z-index: 9999;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Summary functions
let originalSummary = '';

function toggleSummaryEdit() {
    const viewDiv = document.getElementById('summary-view');
    const editDiv = document.getElementById('summary-edit');
    const isEditing = editDiv.style.display !== 'none';
    
    if (isEditing) {
        // Switch to view mode
        viewDiv.style.display = 'block';
        editDiv.style.display = 'none';
    } else {
        // Switch to edit mode
        originalSummary = document.getElementById('summary-textarea').value;
        viewDiv.style.display = 'none';
        editDiv.style.display = 'block';
        document.getElementById('summary-textarea').focus();
    }
}

function cancelSummaryEdit() {
    document.getElementById('summary-textarea').value = originalSummary;
    toggleSummaryEdit();
}

async function saveSummary() {
    const textarea = document.getElementById('summary-textarea');
    const conversationId = textarea.dataset.conversation;
    const summary = textarea.value;
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/messages/summary', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ conversation_id: conversationId, summary: summary })
        });
        const data = await response.json();
        
        if (data.success) {
            // Render markdown and update view
            const rendered = renderMarkdown(summary);
            document.getElementById('summary-view').innerHTML = 
                summary ? `<div id="summary-rendered">${rendered}</div>` 
                        : '<div class="text-muted" style="font-style: italic;">Chưa có tóm tắt...</div>';
            
            toggleSummaryEdit();
            showNotification('Đã lưu tóm tắt!', 'success');
        }
    } catch (e) {
        showNotification('Lỗi kết nối', 'error');
    }
}

// Simple markdown renderer
function renderMarkdown(text) {
    if (!text) return '';
    
    return text
        // Headers
        .replace(/^### (.*$)/gm, '<h4 style="font-weight:600;margin:8px 0 4px;">$1</h4>')
        .replace(/^## (.*$)/gm, '<h3 style="font-weight:600;margin:8px 0 4px;">$1</h3>')
        .replace(/^# (.*$)/gm, '<h2 style="font-weight:600;margin:8px 0 4px;">$1</h2>')
        // Bold, italic
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        // Code
        .replace(/`(.+?)`/g, '<code style="background:#e0e0e0;padding:1px 4px;border-radius:3px;font-size:12px;">$1</code>')
        // Lists
        .replace(/^\- (.*$)/gm, '<li style="margin-left:16px;">$1</li>')
        .replace(/^\* (.*$)/gm, '<li style="margin-left:16px;">$1</li>')
        // Line breaks
        .replace(/\n/g, '<br>');
}

// Initialize: render existing summary on page load
document.addEventListener('DOMContentLoaded', function() {
    const rendered = document.getElementById('summary-rendered');
    if (rendered && rendered.textContent.trim()) {
        rendered.innerHTML = renderMarkdown(rendered.textContent);
    }
});

// AI Summary Generation
async function generateAISummary(conversationId) {
    const btn = document.getElementById('btn-generate-summary');
    const textSpan = document.getElementById('generate-summary-text');
    const summaryView = document.getElementById('summary-view');
    
    // Loading state
    btn.disabled = true;
    textSpan.textContent = 'Đang phân tích...';
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/summarize', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ conversation_id: conversationId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Render markdown
            const rendered = renderMarkdown(data.summary);
            summaryView.innerHTML = `<div id="summary-rendered">${rendered}</div>`;
            
            // Update textarea for edit mode
            document.getElementById('summary-textarea').value = data.summary;
            
            showNotification('✨ AI đã tạo tóm tắt!', 'success');
        } else {
            showNotification(data.error || 'Lỗi không xác định', 'error');
        }
    } catch (e) {
        console.error('Summary error:', e);
        showNotification('Lỗi kết nối đến server', 'error');
    } finally {
        btn.disabled = false;
        textSpan.textContent = 'Generate Summary';
    }
}

// ===== SEND MESSAGE =====
const messageForm = document.getElementById('send-message-form');
const messageInput = document.getElementById('message-input');

if (messageForm) {
    messageForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (!message) return;
        
        const conversationId = messageForm.dataset.conversation;
        const submitBtn = messageForm.querySelector('button[type="submit"]');
        
        // Optimistic UI - add message immediately
        addMessageToUI(message, 'sending');
        messageInput.value = '';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('<?= APP_URL ?>/api/messages/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ conversation_id: conversationId, message: message })
            });
            
            const data = await response.json();
            
            if (data.success) {
                updateLastMessageStatus('sent', data.facebook_sent);
                if (!data.facebook_sent) {
                    showNotification('⚠️ Tin nhắn đã lưu nhưng chưa gửi được đến Facebook', 'info');
                }
            } else {
                updateLastMessageStatus('error');
                showNotification(data.error || 'Lỗi gửi tin nhắn', 'error');
            }
        } catch (e) {
            updateLastMessageStatus('error');
            showNotification('Lỗi kết nối', 'error');
        } finally {
            submitBtn.disabled = false;
            messageInput.focus();
        }
    });
    
    // Enter to send (Shift+Enter for newline)
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            messageForm.requestSubmit();
        }
    });
}

function addMessageToUI(message, status = 'sending') {
    const container = document.getElementById('messages-container');
    if (!container) return;
    
    const time = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    const statusIcon = status === 'sending' ? '⏳' : status === 'sent' ? '✓' : '❌';
    
    const msgDiv = document.createElement('div');
    msgDiv.className = 'message-bubble admin';
    msgDiv.id = 'msg-pending';
    msgDiv.innerHTML = `
        <div class="message-content">${escapeHtml(message)}</div>
        <div class="message-time">${time} <span class="msg-status">${statusIcon}</span></div>
    `;
    container.appendChild(msgDiv);
    container.scrollTop = container.scrollHeight;
}

function updateLastMessageStatus(status, fbSent = true) {
    const pending = document.getElementById('msg-pending');
    if (!pending) return;
    
    pending.id = '';
    const statusSpan = pending.querySelector('.msg-status');
    if (statusSpan) {
        statusSpan.textContent = status === 'sent' ? (fbSent ? '✓✓' : '✓') : '❌';
        statusSpan.style.color = status === 'sent' ? '#10b981' : '#ef4444';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ===== REAL-TIME POLLING =====
let lastMessageTimestamp = '<?= $messages[0]['created_at'] ?? '' ?>';
let pollingInterval = null;
const POLL_INTERVAL = 5000; // 5 seconds

function startPolling() {
    if (pollingInterval) return;
    pollingInterval = setInterval(pollMessages, POLL_INTERVAL);
    console.log('📡 Polling started');
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
        console.log('📡 Polling stopped');
    }
}

async function pollMessages() {
    const form = document.getElementById('send-message-form');
    if (!form) return;
    
    const conversationId = form.dataset.conversation;
    
    try {
        const response = await fetch(`<?= APP_URL ?>/api/messages/${conversationId}?after=${encodeURIComponent(lastMessageTimestamp)}`);
        const data = await response.json();
        
        if (data.success && data.messages && data.messages.length > 0) {
            // Add new messages to UI
            data.messages.forEach(msg => {
                if (msg.sender_type === 'user') {
                    addIncomingMessageToUI(msg);
                }
            });
            
            // Update timestamp
            lastMessageTimestamp = data.messages[data.messages.length - 1].created_at;
            
            // Play notification sound or visual cue
            showNotification('📩 Tin nhắn mới!', 'info');
        }
    } catch (e) {
        console.error('Polling error:', e);
    }
}

function addIncomingMessageToUI(msg) {
    const container = document.getElementById('messages-container');
    if (!container) return;
    
    const time = new Date(msg.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    
    const msgDiv = document.createElement('div');
    msgDiv.className = 'message-bubble user';
    msgDiv.innerHTML = `
        <div class="message-content">${escapeHtml(msg.content)}</div>
        <div class="message-time">${time}</div>
    `;
    container.appendChild(msgDiv);
    container.scrollTop = container.scrollHeight;
}

// Start polling when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('send-message-form')) {
        startPolling();
    }
});

// Stop polling when leaving page
window.addEventListener('beforeunload', stopPolling);

// ===== AI MODE TOGGLE =====
async function toggleAiMode(conversationId) {
    const btn = document.getElementById('ai-toggle-btn');
    if (btn) btn.disabled = true;
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/messages/ai-mode', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ conversation_id: conversationId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const isEnabled = data.ai_mode;
            updateAiModeUI(isEnabled);
            showNotification(isEnabled ? '🤖 AI Mode đã bật!' : '🔕 AI Mode đã tắt', 'success');
        }
    } catch (e) {
        showNotification('Lỗi kết nối', 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

function updateAiModeUI(isEnabled) {
    const btn = document.getElementById('ai-toggle-btn');
    const icon = document.getElementById('ai-toggle-icon');
    const text = document.getElementById('ai-toggle-text');
    
    if (btn) {
        btn.classList.toggle('btn-primary', isEnabled);
        btn.classList.toggle('btn-secondary', !isEnabled);
    }
    if (text) text.textContent = isEnabled ? 'AI: ON' : 'AI: OFF';
}

async function setAiTimer(conversationId, minutes) {
    try {
        const response = await fetch('<?= APP_URL ?>/api/messages/ai-timer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ conversation_id: conversationId, minutes: minutes })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(`⏱️ AI sẽ tự bật lại sau ${minutes} phút`, 'success');
            startTimerCountdown(minutes * 60);
        }
    } catch (e) {
        showNotification('Lỗi kết nối', 'error');
    }
}

let timerCountdown = null;
function startTimerCountdown(seconds) {
    const timerEl = document.getElementById('ai-timer-display');
    if (!timerEl) return;
    
    if (timerCountdown) clearInterval(timerCountdown);
    
    timerEl.style.display = 'block';
    timerCountdown = setInterval(() => {
        seconds--;
        if (seconds <= 0) {
            clearInterval(timerCountdown);
            timerEl.style.display = 'none';
            showNotification('🤖 AI Mode đã tự động bật lại!', 'success');
            updateAiModeUI(true);
            return;
        }
        
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        timerEl.textContent = `AI bật lại sau: ${mins}:${secs.toString().padStart(2, '0')}`;
    }, 1000);
}
</script>

