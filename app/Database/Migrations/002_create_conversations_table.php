<?php
/**
 * Migration: Create Conversations Table
 * Stores chat conversations with users
 */

$pdo = Connection::getInstance();

$pdo->exec("
    CREATE TABLE IF NOT EXISTS conversations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        page_id TEXT NOT NULL,
        user_id TEXT NOT NULL,
        user_name TEXT,
        avatar TEXT,
        last_message TEXT,
        last_message_at DATETIME,
        unread_count INTEGER DEFAULT 0,
        ai_mode INTEGER DEFAULT 1,
        ai_auto_enable_at DATETIME,
        tags TEXT DEFAULT '[]',
        notes TEXT,
        border_level INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (page_id) REFERENCES pages(page_id) ON DELETE CASCADE,
        UNIQUE(page_id, user_id)
    )
");

$pdo->exec("CREATE INDEX IF NOT EXISTS idx_conversations_page_id ON conversations(page_id)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_conversations_user_id ON conversations(user_id)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_conversations_last_message_at ON conversations(last_message_at DESC)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_conversations_unread ON conversations(unread_count)");
