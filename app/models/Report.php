<?php
/**
 * Report Model
 */
class Report extends Model {
    protected static $table = 'reports';

    public static function getByTenant($tenantId, $limit = 20) {
        $sql = "SELECT r.*, u.first_name, u.last_name, u.email
                FROM " . self::$table . " r
                JOIN users u ON r.user_id = u.id
                WHERE r.tenant_id = ?
                ORDER BY r.created_at DESC
                LIMIT ?";
        return self::getDb()->fetchAll($sql, [$tenantId, $limit]);
    }

    public static function createReport($tenantId, $userId, $reportType, $title, $format = 'html', $parameters = []) {
        $data = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'report_type' => $reportType,
            'title' => $title,
            'format' => $format,
            'status' => 'completed',
            'parameters_json' => json_encode($parameters),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return self::create($data);
    }
}
