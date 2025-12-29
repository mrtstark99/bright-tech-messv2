<?php
/**
 * Message Repository
 * Data access for chat messages
 */

require_once APP_PATH . '/Repositories/Repository.php';

class MessageRepository extends Repository {
    protected string $table = 'messages';
    
    /**
     * Get messages for conversation
     */
    public function getByConversation(int $conversationId, int $limit = 50, int $offset = 0): array {
        return Connection::query("
            SELECT * FROM messages 
            WHERE conversation_id = ?
            ORDER BY created_at ASC, id ASC
            LIMIT ? OFFSET ?
        ", [$conversationId, $limit, $offset]);
    }
    
    /**
     * Get latest messages for conversation
     */
    public function getLatest(int $conversationId, int $limit = 20): array {
        // Get in reverse order then flip for correct display
        $messages = Connection::query("
            SELECT * FROM messages 
            WHERE conversation_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ", [$conversationId, $limit]);
        
        return array_reverse($messages);
    }
    
    /**
     * Add message
     */
    public function addMessage(
        int $conversationId,
        string $senderType,
        string $content,
        ?string $senderId = null,
        string $messageType = 'text'
    ): int {
        return $this->create([
            'conversation_id' => $conversationId,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'content' => $content,
            'message_type' => $messageType,
            'status' => 'sent',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Find by Facebook message ID
     */
    public function findByFbMessageId(string $fbMessageId): ?array {
        return $this->findBy('fb_message_id', $fbMessageId);
    }
    
    /**
     * Update message status
     */
    public function updateStatus(int $id, string $status): bool {
        return Connection::execute(
            "UPDATE messages SET status = ? WHERE id = ?",
            [$status, $id]
        ) > 0;
    }
    
    /**
     * Update Facebook message ID
     */
    public function updateFbMessageId(int $id, string $fbMessageId): bool {
        return Connection::execute(
            "UPDATE messages SET fb_message_id = ?, status = 'sent' WHERE id = ?",
            [$fbMessageId, $id]
        ) > 0;
    }
    
    /**
     * Count messages in conversation
     */
    public function countByConversation(int $conversationId): int {
        $result = Connection::first(
            "SELECT COUNT(*) as total FROM messages WHERE conversation_id = ?",
            [$conversationId]
        );
        return $result ? (int) $result['total'] : 0;
    }
    
    /**
     * Get messages after timestamp
     */
    public function getAfter(int $conversationId, string $timestamp): array {
        return Connection::query("
            SELECT * FROM messages 
            WHERE conversation_id = ? AND created_at > ?
            ORDER BY created_at ASC
        ", [$conversationId, $timestamp]);
    }
    
    /**
     * Get stats
     */
    public function getStats(): array {
        return Connection::first("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN sender_type = 'user' THEN 1 ELSE 0 END) as from_users,
                SUM(CASE WHEN sender_type = 'admin' THEN 1 ELSE 0 END) as from_admin,
                SUM(CASE WHEN sender_type = 'ai' THEN 1 ELSE 0 END) as from_ai
            FROM messages
        ") ?? ['total' => 0, 'from_users' => 0, 'from_admin' => 0, 'from_ai' => 0];
    }
    
    /**
     * Get recent messages across all conversations
     */
    public function getRecent(int $limit = 20): array {
        return Connection::query("
            SELECT 
                m.*,
                c.user_name,
                c.avatar as user_avatar,
                p.name as page_name
            FROM messages m
            JOIN conversations c ON c.id = m.conversation_id
            LEFT JOIN pages p ON p.page_id = c.page_id
            ORDER BY m.created_at DESC
            LIMIT ?
        ", [$limit]);
    }
}
