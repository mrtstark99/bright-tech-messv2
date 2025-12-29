<?php
/**
 * Base Repository
 * Common database operations for all repositories
 */

require_once APP_PATH . '/Database/Connection.php';

abstract class Repository {
    protected string $table;
    protected string $primaryKey = 'id';
    
    /**
     * Get PDO connection
     */
    protected function db(): PDO {
        return Connection::getInstance();
    }
    
    /**
     * Find by ID
     */
    public function find(int $id): ?array {
        return Connection::first(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }
    
    /**
     * Get all records
     */
    public function all(int $limit = 100, int $offset = 0): array {
        return Connection::query(
            "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }
    
    /**
     * Count all records
     */
    public function count(): int {
        $result = Connection::first("SELECT COUNT(*) as total FROM {$this->table}");
        return $result ? (int) $result['total'] : 0;
    }
    
    /**
     * Create new record
     */
    public function create(array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        return Connection::insert(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
    }
    
    /**
     * Update record
     */
    public function update(int $id, array $data): bool {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $sets = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $params = array_values($data);
        $params[] = $id;
        
        return Connection::execute(
            "UPDATE {$this->table} SET {$sets} WHERE {$this->primaryKey} = ?",
            $params
        ) > 0;
    }
    
    /**
     * Delete record
     */
    public function delete(int $id): bool {
        return Connection::execute(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        ) > 0;
    }
    
    /**
     * Find by column
     */
    public function findBy(string $column, $value): ?array {
        return Connection::first(
            "SELECT * FROM {$this->table} WHERE {$column} = ?",
            [$value]
        );
    }
    
    /**
     * Find all by column
     */
    public function where(string $column, $value, int $limit = 100): array {
        return Connection::query(
            "SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT ?",
            [$value, $limit]
        );
    }
}
