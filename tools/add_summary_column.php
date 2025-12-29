<?php
/**
 * Add summary column to conversations table
 */

require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Database/Connection.php';

try {
    $pdo = Connection::getInstance();
    $pdo->exec('ALTER TABLE conversations ADD COLUMN summary TEXT');
    echo "✅ Added 'summary' column to conversations table\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'duplicate column') !== false) {
        echo "ℹ️ Column 'summary' already exists\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
