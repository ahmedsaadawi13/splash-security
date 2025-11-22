<?php
/**
 * MonitoredDomain Model
 */
class MonitoredDomain extends Model {
    protected static $table = 'monitored_domains';

    public static function findByDomain($domain, $tenantId) {
        $sql = "SELECT * FROM " . self::$table . " WHERE domain = ? AND tenant_id = ?";
        return self::getDb()->fetchOne($sql, [$domain, $tenantId]);
    }

    public static function getActive($tenantId) {
        $sql = "SELECT * FROM " . self::$table . "
                WHERE tenant_id = ? AND status = 'active'
                ORDER BY domain ASC";
        return self::getDb()->fetchAll($sql, [$tenantId]);
    }

    public static function updateRiskScore($domainId, $score, $rating, $tenantId = null) {
        $data = [
            'risk_score' => $score,
            'risk_rating' => $rating,
            'last_scanned_at' => date('Y-m-d H:i:s')
        ];
        return self::update($domainId, $data, $tenantId);
    }

    public static function updateSslInfo($domainId, $sslInfo, $tenantId = null) {
        $data = [
            'ssl_info_json' => json_encode($sslInfo),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return self::update($domainId, $data, $tenantId);
    }

    public static function updateDnsRecords($domainId, $dnsRecords, $tenantId = null) {
        $data = [
            'dns_records_json' => json_encode($dnsRecords),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return self::update($domainId, $data, $tenantId);
    }

    public static function getStats($tenantId) {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    AVG(risk_score) as avg_score,
                    SUM(CASE WHEN risk_rating = 'A' THEN 1 ELSE 0 END) as rating_a,
                    SUM(CASE WHEN risk_rating = 'B' THEN 1 ELSE 0 END) as rating_b,
                    SUM(CASE WHEN risk_rating = 'C' THEN 1 ELSE 0 END) as rating_c,
                    SUM(CASE WHEN risk_rating = 'D' THEN 1 ELSE 0 END) as rating_d,
                    SUM(CASE WHEN risk_rating = 'F' THEN 1 ELSE 0 END) as rating_f
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
