<?php
/**
 * Page Repository
 * Data access for Fanpages
 */

require_once APP_PATH . '/Repositories/Repository.php';

class PageRepository extends Repository {
    protected string $table = 'pages';
    
    /**
     * Find by Facebook Page ID
     */
    public function findByPageId(string $pageId): ?array {
        return $this->findBy('page_id', $pageId);
    }
    
    /**
     * Get all active pages
     */
    public function getActive(): array {
        return Connection::query(
            "SELECT * FROM pages WHERE is_active = 1 ORDER BY name ASC"
        );
    }
    
    /**
     * Get pages with conversation counts
     */
    public function getAllWithStats(): array {
        return Connection::query("
            SELECT 
                p.*,
                COUNT(DISTINCT c.id) as conversation_count,
                SUM(c.unread_count) as total_unread
            FROM pages p
            LEFT JOIN conversations c ON c.page_id = p.page_id
            WHERE p.is_active = 1
            GROUP BY p.id
            ORDER BY p.name ASC
        ");
    }
    
    /**
     * Upsert page (insert or update)
     */
    public function upsert(string $pageId, array $data): int {
        $existing = $this->findByPageId($pageId);
        
        if ($existing) {
            $this->update($existing['id'], $data);
            return $existing['id'];
        }
        
        $data['page_id'] = $pageId;
        return $this->create($data);
    }
    
    /**
     * Update access token
     */
    public function updateToken(string $pageId, string $token): bool {
        return Connection::execute(
            "UPDATE pages SET access_token = ?, updated_at = ? WHERE page_id = ?",
            [$token, date('Y-m-d H:i:s'), $pageId]
        ) > 0;
    }
    
    /**
     * Get access token for page
     */
    public function getAccessToken(string $pageId): ?string {
        $page = $this->findByPageId($pageId);
        return $page['access_token'] ?? null;
    }
    
    /**
     * Toggle page active status
     */
    public function toggleActive(int $id): bool {
        return Connection::execute(
            "UPDATE pages SET is_active = NOT is_active, updated_at = ? WHERE id = ?",
            [date('Y-m-d H:i:s'), $id]
        ) > 0;
    }
}
