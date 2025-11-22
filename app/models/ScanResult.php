<?php
/**
 * ScanResult Model
 */
class ScanResult extends Model {
    protected static $table = 'scan_results';

    public static function getByAsset($assetType, $assetId, $tenantId) {
        $sql = "SELECT * FROM " . self::$table . "
                WHERE asset_type = ? AND asset_id = ? AND tenant_id = ?
                ORDER BY created_at DESC";
        return self::getDb()->fetchAll($sql, [$assetType, $assetId, $tenantId]);
    }

    public static function getLatestByType($assetType, $assetId, $scanType, $tenantId) {
        $sql = "SELECT * FROM " . self::$table . "
                WHERE asset_type = ? AND asset_id = ? AND scan_type = ? AND tenant_id = ?
                ORDER BY created_at DESC LIMIT 1";
        return self::getDb()->fetchOne($sql, [$assetType, $assetId, $scanType, $tenantId]);
    }

    public static function createScanResult($tenantId, $assetType, $assetId, $scanType, $result, $severity = 'info', $score = null) {
        $data = [
            'tenant_id' => $tenantId,
            'asset_type' => $assetType,
            'asset_id' => $assetId,
            'scan_type' => $scanType,
            'result_json' => json_encode($result),
            'severity' => $severity,
            'score' => $score,
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return self::create($data);
    }

    public static function getRecent($tenantId, $limit = 10) {
        $sql = "SELECT sr.*,
                    CASE
                        WHEN sr.asset_type = 'domain' THEN md.domain
                        WHEN sr.asset_type = 'ip' THEN mi.ip_address
                    END as asset_name
                FROM " . self::$table . " sr
                LEFT JOIN monitored_domains md ON sr.asset_type = 'domain' AND sr.asset_id = md.id
                LEFT JOIN monitored_ips mi ON sr.asset_type = 'ip' AND sr.asset_id = mi.id
                WHERE sr.tenant_id = ?
                ORDER BY sr.created_at DESC
                LIMIT ?";
        return self::getDb()->fetchAll($sql, [$tenantId, $limit]);
    }

    public static function getBySeverity($tenantId, $severity) {
        $sql = "SELECT * FROM " . self::$table . "
                WHERE tenant_id = ? AND severity = ?
                ORDER BY created_at DESC";
        return self::getDb()->fetchAll($sql, [$tenantId, $severity]);
    }
}
