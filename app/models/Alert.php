<?php
/**
 * Alert Model
 */
class Alert extends Model {
    protected static $table = 'alerts';

    public static function getOpen($tenantId) {
        $sql = "SELECT a.*,
                    CASE
                        WHEN a.asset_type = 'domain' THEN md.domain
                        WHEN a.asset_type = 'ip' THEN mi.ip_address
                        ELSE 'System'
                    END as asset_name
                FROM " . self::$table . " a
                LEFT JOIN monitored_domains md ON a.asset_type = 'domain' AND a.asset_id = md.id
                LEFT JOIN monitored_ips mi ON a.asset_type = 'ip' AND a.asset_id = mi.id
                WHERE a.tenant_id = ? AND a.status = 'open'
                ORDER BY
                    CASE a.severity
                        WHEN 'critical' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'low' THEN 4
                        WHEN 'info' THEN 5
                    END,
                    a.created_at DESC";
        return self::getDb()->fetchAll($sql, [$tenantId]);
    }

    public static function getRecent($tenantId, $limit = 10) {
        $sql = "SELECT a.*,
                    CASE
                        WHEN a.asset_type = 'domain' THEN md.domain
                        WHEN a.asset_type = 'ip' THEN mi.ip_address
                        ELSE 'System'
                    END as asset_name
                FROM " . self::$table . " a
                LEFT JOIN monitored_domains md ON a.asset_type = 'domain' AND a.asset_id = md.id
                LEFT JOIN monitored_ips mi ON a.asset_type = 'ip' AND a.asset_id = mi.id
                WHERE a.tenant_id = ?
                ORDER BY a.created_at DESC
                LIMIT ?";
        return self::getDb()->fetchAll($sql, [$tenantId, $limit]);
    }

    public static function createAlert($tenantId, $assetType, $assetId, $title, $description, $severity, $category = null) {
        $data = [
            'tenant_id' => $tenantId,
            'asset_type' => $assetType,
            'asset_id' => $assetId,
            'title' => $title,
            'description' => $description,
            'severity' => $severity,
            'category' => $category,
            'status' => 'open',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return self::create($data);
    }

    public static function acknowledge($alertId, $userId, $tenantId = null) {
        $data = [
            'status' => 'acknowledged',
            'acknowledged_at' => date('Y-m-d H:i:s'),
            'acknowledged_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return self::update($alertId, $data, $tenantId);
    }

    public static function close($alertId, $userId, $tenantId = null) {
        $data = [
            'status' => 'closed',
            'closed_at' => date('Y-m-d H:i:s'),
            'closed_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return self::update($alertId, $data, $tenantId);
    }

    public static function getStats($tenantId) {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                    SUM(CASE WHEN status = 'acknowledged' THEN 1 ELSE 0 END) as acknowledged,
                    SUM(CASE WHEN severity = 'critical' AND status = 'open' THEN 1 ELSE 0 END) as critical,
                    SUM(CASE WHEN severity = 'high' AND status = 'open' THEN 1 ELSE 0 END) as high
                FROM " . self::$table . "
                WHERE tenant_id = ?";
        return self::getDb()->fetchOne($sql, [$tenantId]);
    }
}
