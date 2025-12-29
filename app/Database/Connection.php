<?php
/**
 * Database Connection Handler
 * SQLite connection with PDO
 */

class Connection {
    private static ?PDO $instance = null;
    private string $dbPath;
    
    public function __construct(?string $dbPath = null) {
        $this->dbPath = $dbPath ?? DB_PATH;
    }
    
    /**
     * Get PDO connection instance (Singleton)
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dbPath = DB_PATH;
            $dbDir = dirname($dbPath);
            
            // Ensure database directory exists
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            try {
                self::$instance = new PDO(
                    "sqlite:{$dbPath}",
                    null,
                    null,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                
                // Enable foreign keys
                self::$instance->exec('PRAGMA foreign_keys = ON');
                
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Run migrations
     */
    public static function migrate(): void {
        $pdo = self::getInstance();
        $migrationsPath = APP_PATH . '/Database/Migrations';
        
        // Create migrations table if not exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Get executed migrations
        $executed = $pdo->query("SELECT name FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
        
        // Get migration files
        $files = glob($migrationsPath . '/*.php');
        sort($files);
        
        foreach ($files as $file) {
            $name = basename($file, '.php');
            
            if (!in_array($name, $executed)) {
                echo "Running migration: {$name}\n";
                
                require_once $file;
                
                // Record migration
                $stmt = $pdo->prepare("INSERT INTO migrations (name) VALUES (?)");
                $stmt->execute([$name]);
            }
        }
    }
    
    /**
     * Execute raw SQL
     */
    public static function exec(string $sql): int {
        return self::getInstance()->exec($sql);
    }
    
    /**
     * Query with prepared statement
     */
    public static function query(string $sql, array $params = []): array {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get single row
     */
    public static function first(string $sql, array $params = []): ?array {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Insert and return last ID
     */
    public static function insert(string $sql, array $params = []): int {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) self::getInstance()->lastInsertId();
    }
    
    /**
     * Update/Delete and return affected rows
     */
    public static function execute(string $sql, array $params = []): int {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
