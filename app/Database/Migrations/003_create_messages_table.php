<?php
/**
 * Migration: Create Messages Table
 * Stores individual chat messages
 */

$pdo = Connection::getInstance();

$pdo->exec("
    CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        conversation_id INTEGER NOT NULL,
        sender_type TEXT NOT NULL CHECK(sender_type IN ('user', 'admin', 'ai')),
        sender_id TEXT,
        content TEXT NOT NULL,
        message_type TEXT DEFAULT 'text',
        attachments TEXT DEFAULT '[]',
        fb_message_id TEXT,
        status TEXT DEFAULT 'sent' CHECK(status IN ('sending', 'sent', 'delivered', 'read', 'failed')),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
    )
");

$pdo->exec("CREATE INDEX IF NOT EXISTS idx_messages_conversation_id ON messages(conversation_id)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_messages_created_at ON messages(created_at DESC)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_messages_fb_message_id ON messages(fb_message_id)");
