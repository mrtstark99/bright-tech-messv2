<?php
/**
 * MessV2 - Modern SaaS Fanpage Manager
 * Configuration File
 */

// Define path constants
// Handle both CLI and web contexts
if (!empty($_SERVER['DOCUMENT_ROOT'])) {
    define('ROOT_PATH', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/messv2');
} else {
    // CLI mode - use __DIR__ to calculate root path
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}
define('APP_PATH', ROOT_PATH . '/app');
define('RESOURCES_PATH', ROOT_PATH . '/resources');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Load .env
function loadEnv($path) {
    if (!file_exists($path)) return;
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            if (!defined($key)) define($key, $value);
        }
    }
}

loadEnv(ROOT_PATH . '/.env');

// Default values
if (!defined('DEBUG_MODE')) define('DEBUG_MODE', true);
if (!defined('APP_NAME')) define('APP_NAME', 'MessV2');
if (!defined('APP_URL')) define('APP_URL', '/messv2');
if (!defined('TIMEZONE')) define('TIMEZONE', 'Asia/Ho_Chi_Minh');

// Database
if (!defined('DB_PATH')) define('DB_PATH', STORAGE_PATH . '/database/app.db');

// External services
if (!defined('N8N_WEBHOOK_URL')) define('N8N_WEBHOOK_URL', '');
if (!defined('FACEBOOK_APP_ID')) define('FACEBOOK_APP_ID', '');
if (!defined('FACEBOOK_APP_SECRET')) define('FACEBOOK_APP_SECRET', '');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Debug helper
function debug_log($message, $file = 'app.log') {
    if (!DEBUG_MODE) return;
    $logPath = STORAGE_PATH . '/logs/' . $file;
    $logDir = dirname($logPath);
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    file_put_contents($logPath, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}
