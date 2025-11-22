<?php
/**
 * SplashSecurity Configuration File
 * Loads environment variables and sets application constants
 */

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die('.env file not found. Please copy .env.example to .env and configure it.');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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

            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
            }
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/../.env');

// Helper function to get environment variables
function env($key, $default = null) {
    return isset($_ENV[$key]) ? $_ENV[$key] : $default;
}

// Database Configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'splash_security'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// Application Configuration
define('APP_NAME', env('APP_NAME', 'SplashSecurity'));
define('APP_URL', env('APP_URL', 'http://localhost'));
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', env('APP_DEBUG', 'false') === 'true');
define('APP_TIMEZONE', env('APP_TIMEZONE', 'UTC'));

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOAD_PATH', STORAGE_PATH . '/uploads');
define('LOG_PATH', STORAGE_PATH . '/logs');

// Session Configuration
define('SESSION_LIFETIME', env('SESSION_LIFETIME', 7200));
define('SESSION_NAME', env('SESSION_NAME', 'splash_security_session'));

// Security
define('CSRF_TOKEN_NAME', env('CSRF_TOKEN_NAME', 'csrf_token'));

// API Configuration
define('API_RATE_LIMIT_DEFAULT', env('API_RATE_LIMIT_DEFAULT', 100));
define('API_RATE_LIMIT_WINDOW', env('API_RATE_LIMIT_WINDOW', 3600));

// Scanning Configuration
define('SCAN_TIMEOUT', env('SCAN_TIMEOUT', 30));
define('MAX_CONCURRENT_SCANS', env('MAX_CONCURRENT_SCANS', 5));

// File Upload
define('MAX_FILE_SIZE', env('MAX_FILE_SIZE', 10485760)); // 10MB
define('ALLOWED_FILE_TYPES', env('ALLOWED_FILE_TYPES', 'pdf,txt,jpg,png,cert,pem'));

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
    session_name(SESSION_NAME);
    session_start();
}
