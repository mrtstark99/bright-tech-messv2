<?php
/**
 * Migration: Create Pages Table
 * Stores Facebook Fanpage information
 */

$pdo = Connection::getInstance();

$pdo->exec("
    CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        page_id TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        avatar TEXT,
        access_token TEXT,
        settings TEXT DEFAULT '{}',
        is_active INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

$pdo->exec("CREATE INDEX IF NOT EXISTS idx_pages_page_id ON pages(page_id)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_pages_is_active ON pages(is_active)");
