<?php
/**
 * Logger Helper
 * Handles application logging
 */
class Logger {
    private static function log($level, $message, $context = []) {
        $logFile = LOG_PATH . '/app.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logMessage = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;

        // Ensure log directory exists
        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }

        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }

    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }

    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }

    public static function debug($message, $context = []) {
        if (APP_DEBUG) {
            self::log('DEBUG', $message, $context);
        }
    }

    public static function activity($tenantId, $userId, $action, $entityType = null, $entityId = null, $description = null) {
        $data = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            ActivityLog::create($data);
        } catch (Exception $e) {
            self::error('Failed to log activity: ' . $e->getMessage());
        }
    }
}
