<?php
/**
 * Migration: Create Settings Table
 * Stores application settings as key-value pairs
 */

$pdo = Connection::getInstance();

$pdo->exec("
    CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        key TEXT NOT NULL UNIQUE,
        value TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

// Insert default settings
$defaults = [
    ['poll_interval', '5000'],
    ['ai_enabled', '1'],
    ['ai_model', 'gpt-3.5-turbo'],
    ['theme', 'light'],
    ['timezone', 'Asia/Ho_Chi_Minh'],
];

$stmt = $pdo->prepare("INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)");
foreach ($defaults as $setting) {
    $stmt->execute($setting);
}
