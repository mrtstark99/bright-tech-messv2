<?php
/**
 * Add ai_mode and ai_timer columns to conversations table
 */

require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Database/Connection.php';

try {
    $pdo = Connection::getInstance();
    
    // Add ai_mode column
    try {
        $pdo->exec('ALTER TABLE conversations ADD COLUMN ai_mode INTEGER DEFAULT 0');
        echo "✅ Added 'ai_mode' column\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column') !== false) {
            echo "ℹ️ Column 'ai_mode' already exists\n";
        } else {
            echo "⚠️ ai_mode: " . $e->getMessage() . "\n";
        }
    }
    
    // Add ai_timer column (stores when AI should auto-enable)
    try {
        $pdo->exec('ALTER TABLE conversations ADD COLUMN ai_timer_enable_at DATETIME');
        echo "✅ Added 'ai_timer_enable_at' column\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'duplicate column') !== false) {
            echo "ℹ️ Column 'ai_timer_enable_at' already exists\n";
        } else {
            echo "⚠️ ai_timer_enable_at: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎉 Database migration complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
