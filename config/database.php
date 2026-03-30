<?php
/**
 * EduBridge Rwanda - Database Configuration
 * Reads configuration from .env file with fallback values
 */

// Load .env file if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove quotes if present
            $value = trim($value, '"\'');
            // Set environment variable if not already set
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

// Database configuration with fallbacks
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'edubridge_rwanda');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Create database connection
function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, don't expose error details
            if (getenv('APP_ENV') === 'production') {
                http_response_code(500);
                die('Database connection failed. Please try again later.');
            }
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $pdo;
}

// Site configuration with fallbacks
define('SITE_NAME', getenv('SITE_NAME') ?: 'EduBridge Rwanda');
define('SITE_URL', getenv('APP_URL') ?: 'http://localhost/InkingiX_Edubridge');
define('DEFAULT_LANGUAGE', getenv('DEFAULT_LANGUAGE') ?: 'en');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    // Secure session settings
    ini_set('session.cookie_httponly', 1);
    if (APP_ENV === 'production') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Set default timezone
date_default_timezone_set('Africa/Kigali');
