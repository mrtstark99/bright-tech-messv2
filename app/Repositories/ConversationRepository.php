<?php
/**
 * Conversation Repository
 * Data access for chat conversations
 */

require_once APP_PATH . '/Repositories/Repository.php';

class ConversationRepository extends Repository {
    protected string $table = 'conversations';
    
    /**
     * Get conversation by page_id and user_id
     */
    public function findByPageAndUser(string $pageId, string $userId): ?array {
        return Connection::first(
            "SELECT * FROM conversations WHERE page_id = ? AND user_id = ?",
            [$pageId, $userId]
        );
    }
    
    /**
     * Get all conversations with latest message info
     */
    public function getAllWithDetails(int $limit = 50, int $offset = 0, ?string $pageId = null): array {
        $where = $pageId ? "WHERE c.page_id = ?" : "";
        $params = $pageId ? [$pageId, $limit, $offset] : [$limit, $offset];
        
        return Connection::query("
            SELECT 
                c.*,
                p.name as page_name,
                p.avatar as page_avatar
            FROM conversations c
            LEFT JOIN pages p ON p.page_id = c.page_id
            {$where}
            ORDER BY c.last_message_at DESC
            LIMIT ? OFFSET ?
        ", $params);
    }
    
    /**
     * Get unread conversations
     */
    public function getUnread(int $limit = 50): array {
        return Connection::query("
            SELECT c.*, p.name as page_name
            FROM conversations c
            LEFT JOIN pages p ON p.page_id = c.page_id
            WHERE c.unread_count > 0
            ORDER BY c.last_message_at DESC
            LIMIT ?
        ", [$limit]);
    }
    
    /**
     * Get AI mode enabled conversations
     */
    public function getAiEnabled(): array {
        return Connection::query("
            SELECT * FROM conversations WHERE ai_mode = 1
        ");
    }
    
    /**
     * Upsert conversation
     */
    public function upsert(string $pageId, string $userId, array $data): int {
        $existing = $this->findByPageAndUser($pageId, $userId);
        
        if ($existing) {
            $this->update($existing['id'], $data);
            return $existing['id'];
        }
        
        $data['page_id'] = $pageId;
        $data['user_id'] = $userId;
        return $this->create($data);
    }
    
    /**
     * Update last message
     */
    public function updateLastMessage(int $id, string $message, bool $incrementUnread = false): bool {
        $update = "UPDATE conversations SET 
            last_message = ?, 
            last_message_at = ?,
            updated_at = ?";
        
        if ($incrementUnread) {
            $update .= ", unread_count = unread_count + 1";
        }
        
        $update .= " WHERE id = ?";
        
        $now = date('Y-m-d H:i:s');
        return Connection::execute($update, [$message, $now, $now, $id]) > 0;
    }
    
    /**
     * Mark as read
     */
    public function markAsRead(int $id): bool {
        return Connection::execute(
            "UPDATE conversations SET unread_count = 0, updated_at = ? WHERE id = ?",
            [date('Y-m-d H:i:s'), $id]
        ) > 0;
    }
    
    /**
     * Toggle AI mode
     */
    public function toggleAiMode(int $id): bool {
        return Connection::execute(
            "UPDATE conversations SET ai_mode = NOT ai_mode, updated_at = ? WHERE id = ?",
            [date('Y-m-d H:i:s'), $id]
        ) > 0;
    }
    
    /**
     * Set AI auto-enable timer
     */
    public function setAiTimer(int $id, ?string $enableAt): bool {
        return Connection::execute(
            "UPDATE conversations SET ai_auto_enable_at = ?, updated_at = ? WHERE id = ?",
            [$enableAt, date('Y-m-d H:i:s'), $id]
        ) > 0;
    }
    
    /**
     * Update tags
     */
    public function updateTags(int $id, array $tags): bool {
        return Connection::execute(
            "UPDATE conversations SET tags = ?, updated_at = ? WHERE id = ?",
            [json_encode($tags), date('Y-m-d H:i:s'), $id]
        ) > 0;
    }
    
    /**
     * Update notes
     */
    public function updateNotes(int $id, string $notes): bool {
        return Connection::execute(
            "UPDATE conversations SET notes = ?, updated_at = ? WHERE id = ?",
            [$notes, date('Y-m-d H:i:s'), $id]
        ) > 0;
    }
    
    /**
     * Search conversations
     */
    public function search(string $query, int $limit = 20): array {
        $searchTerm = "%{$query}%";
        return Connection::query("
            SELECT c.*, p.name as page_name
            FROM conversations c
            LEFT JOIN pages p ON p.page_id = c.page_id
            WHERE c.user_name LIKE ? OR c.last_message LIKE ?
            ORDER BY c.last_message_at DESC
            LIMIT ?
        ", [$searchTerm, $searchTerm, $limit]);
    }
    
    /**
     * Update border level (VIP tier)
     */
    public function updateBorderLevel(int $id, int $level): bool {
        return Connection::execute(
            "UPDATE conversations SET border_level = ?, updated_at = ? WHERE id = ?",
            [$level, date('Y-m-d H:i:s'), $id]
        ) > 0;
    }
    
    /**
     * Update conversation summary
     */
    public function updateSummary(int $id, string $summary): bool {
        return Connection::execute(
            "UPDATE conversations SET summary = ?, updated_at = ? WHERE id = ?",
            [$summary, date('Y-m-d H:i:s'), $id]
        ) > 0;
    }
    
    /**
     * Get stats
     */
    public function getStats(): array {
        return Connection::first("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN unread_count > 0 THEN 1 ELSE 0 END) as unread,
                SUM(CASE WHEN ai_mode = 1 THEN 1 ELSE 0 END) as ai_enabled,
                SUM(unread_count) as total_unread
            FROM conversations
        ") ?? ['total' => 0, 'unread' => 0, 'ai_enabled' => 0, 'total_unread' => 0];
    }
}

