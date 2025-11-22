<?php
/**
 * Redirect Helper
 * Handles HTTP redirects
 */
class Redirect {
    public static function to($path, $statusCode = 302) {
        $url = APP_URL . $path;
        header("Location: $url", true, $statusCode);
        exit;
    }

    public static function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: $referer");
        exit;
    }

    public static function with($key, $value) {
        $_SESSION['flash_' . $key] = $value;
    }

    public static function withSuccess($message) {
        self::with('success', $message);
    }

    public static function withError($message) {
        self::with('error', $message);
    }

    public static function getFlash($key) {
        $flashKey = 'flash_' . $key;
        if (isset($_SESSION[$flashKey])) {
            $value = $_SESSION[$flashKey];
            unset($_SESSION[$flashKey]);
            return $value;
        }
        return null;
    }
}
