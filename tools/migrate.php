<?php
/**
 * Database Migration Runner
 * CLI tool to run database migrations
 * 
 * Usage: php tools/migrate.php
 */

// Load configuration
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Database/Connection.php';

echo "=================================\n";
echo "MessV2 Database Migration Tool\n";
echo "=================================\n\n";

try {
    // Run migrations
    Connection::migrate();
    echo "\n✅ All migrations completed successfully!\n";
    
    // Show tables
    echo "\n📊 Database Tables:\n";
    $tables = Connection::query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    foreach ($tables as $table) {
        echo "   - {$table['name']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
