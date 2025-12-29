<?php
/**
 * Debug script for migrations
 */
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Database/Connection.php';

echo "DB_PATH: " . DB_PATH . "\n";
echo "APP_PATH: " . APP_PATH . "\n";

$migrationsPath = APP_PATH . '/Database/Migrations';
echo "Migrations Path: " . $migrationsPath . "\n";
echo "Path exists: " . (is_dir($migrationsPath) ? "YES" : "NO") . "\n";

$files = glob($migrationsPath . '/*.php');
echo "Migration files found: " . count($files) . "\n";
foreach ($files as $f) {
    echo "  - " . basename($f) . "\n";
}

// Try to run migrations manually
echo "\n--- Running migrations ---\n";
$pdo = Connection::getInstance();

// Check current tables
echo "\nTables before migration:\n";
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
foreach ($tables as $t) {
    echo "  - " . $t['name'] . "\n";
}

// Get executed migrations
$executed = $pdo->query("SELECT name FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
echo "\nExecuted migrations: " . count($executed) . "\n";
foreach ($executed as $e) {
    echo "  - " . $e . "\n";
}

// Try to run each migration
echo "\n--- Running pending migrations ---\n";
foreach ($files as $file) {
    $name = basename($file, '.php');
    
    if (!in_array($name, $executed)) {
        echo "Running: {$name}\n";
        try {
            require_once $file;
            echo "  Done!\n";
            
            // Record migration
            $stmt = $pdo->prepare("INSERT INTO migrations (name) VALUES (?)");
            $stmt->execute([$name]);
        } catch (Exception $e) {
            echo "  ERROR: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Skipping (already executed): {$name}\n";
    }
}

// Check tables after
echo "\nTables after migration:\n";
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
foreach ($tables as $t) {
    echo "  - " . $t['name'] . "\n";
}
