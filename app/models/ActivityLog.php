<?php
/**
 * ActivityLog Model
 */
class ActivityLog extends Model {
    protected static $table = 'activity_logs';

    public static function getRecent($tenantId, $limit = 50) {
        $sql = "SELECT al.*, u.first_name, u.last_name, u.email
                FROM " . self::$table . " al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.tenant_id = ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        return self::getDb()->fetchAll($sql, [$tenantId, $limit]);
    }

    public static function getByUser($userId, $limit = 50) {
        $sql = "SELECT * FROM " . self::$table . "
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?";
        return self::getDb()->fetchAll($sql, [$userId, $limit]);
    }

    public static function log($tenantId, $userId, $action, $entityType = null, $entityId = null, $description = null) {
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

        return self::create($data);
    }
}
