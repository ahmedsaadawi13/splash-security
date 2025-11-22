<?php
/**
 * MonitoredIp Model
 */
class MonitoredIp extends Model {
    protected static $table = 'monitored_ips';

    public static function findByIp($ipAddress, $tenantId) {
        $sql = "SELECT * FROM " . self::$table . " WHERE ip_address = ? AND tenant_id = ?";
        return self::getDb()->fetchOne($sql, [$ipAddress, $tenantId]);
    }

    public static function getActive($tenantId) {
        $sql = "SELECT * FROM " . self::$table . "
                WHERE tenant_id = ? AND status = 'active'
                ORDER BY ip_address ASC";
        return self::getDb()->fetchAll($sql, [$tenantId]);
    }

    public static function updateRiskScore($ipId, $score, $rating, $tenantId = null) {
        $data = [
            'risk_score' => $score,
            'risk_rating' => $rating,
            'last_scanned_at' => date('Y-m-d H:i:s')
        ];
        return self::update($ipId, $data, $tenantId);
    }

    public static function updateGeoInfo($ipId, $geoInfo, $tenantId = null) {
        $data = [
            'geo_info_json' => json_encode($geoInfo),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return self::update($ipId, $data, $tenantId);
    }

    public static function updateOpenPorts($ipId, $openPorts, $tenantId = null) {
        $data = [
            'open_ports_json' => json_encode($openPorts),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return self::update($ipId, $data, $tenantId);
    }

    public static function getStats($tenantId) {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    AVG(risk_score) as avg_score
                FROM " . self::$table . "
                WHERE tenant_id = ?";
        return self::getDb()->fetchOne($sql, [$tenantId]);
    }

    public static function getNeedingScan($tenantId, $hours = 168) {
        $cutoff = date('Y-m-d H:i:s', strtotime("-$hours hours"));
        $sql = "SELECT * FROM " . self::$table . "
                WHERE tenant_id = ? AND status = 'active'
                AND (last_scanned_at IS NULL OR last_scanned_at < ?)
                ORDER BY last_scanned_at ASC";
        return self::getDb()->fetchAll($sql, [$tenantId, $cutoff]);
    }
}
