<?php
/**
 * Setting Repository
 * Data access for application settings
 */

require_once APP_PATH . '/Repositories/Repository.php';

class SettingRepository extends Repository {
    protected string $table = 'settings';
    
    /**
     * Get setting value by key
     */
    public function get(string $key, $default = null) {
        $row = $this->findBy('key', $key);
        return $row ? $row['value'] : $default;
    }
    
    /**
     * Set setting value
     */
    public function set(string $key, $value): bool {
        $existing = $this->findBy('key', $key);
        
        if ($existing) {
            return Connection::execute(
                "UPDATE settings SET value = ?, updated_at = ? WHERE key = ?",
                [$value, date('Y-m-d H:i:s'), $key]
            ) > 0;
        }
        
        return $this->create([
            'key' => $key,
            'value' => $value
        ]) > 0;
    }
    
    /**
     * Get all settings as key-value array
     */
    public function getAllAsArray(): array {
        $rows = $this->all(1000);
        $settings = [];
        
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        
        return $settings;
    }
    
    /**
     * Delete setting
     */
    public function remove(string $key): bool {
        return Connection::execute(
            "DELETE FROM settings WHERE key = ?",
            [$key]
        ) > 0;
    }
    
    /**
     * Bulk update settings
     */
    public function bulkUpdate(array $settings): void {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }
}
