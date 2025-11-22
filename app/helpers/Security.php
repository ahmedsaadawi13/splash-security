<?php
/**
 * Security Helper
 * Handles CSRF tokens, password hashing, input sanitization
 */
class Security {
    public static function generateCsrfToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    public static function validateCsrfToken($token) {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function escape($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    public static function generateApiKey() {
        return 'sk_' . bin2hex(random_bytes(32));
    }

    public static function generateRandomToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    /**
     * Check for brute force attempts
     */
    public static function checkBruteForce($identifier, $maxAttempts = 5, $window = 900) {
        $key = 'bf_' . md5($identifier);

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }

        $data = $_SESSION[$key];

        // Reset if window expired
        if (time() - $data['first_attempt'] > $window) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
            return true;
        }

        // Check if exceeded
        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }

        return true;
    }

    public static function recordFailedAttempt($identifier) {
        $key = 'bf_' . md5($identifier);

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }

        $_SESSION[$key]['attempts']++;
    }

    public static function resetBruteForce($identifier) {
        $key = 'bf_' . md5($identifier);
        unset($_SESSION[$key]);
    }
}
