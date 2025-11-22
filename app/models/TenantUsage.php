<?php
/**
 * TenantUsage Model
 */
class TenantUsage extends Model {
    protected static $table = 'tenant_usage';

    public static function getCurrentPeriod($tenantId) {
        $periodStart = date('Y-m-01');
        $periodEnd = date('Y-m-t');

        $sql = "SELECT * FROM " . self::$table . "
                WHERE tenant_id = ? AND period_start = ? AND period_end = ?";
        return self::getDb()->fetchOne($sql, [$tenantId, $periodStart, $periodEnd]);
    }

    public static function getHistory($tenantId, $months = 6) {
        $sql = "SELECT * FROM " . self::$table . "
                WHERE tenant_id = ?
                ORDER BY period_start DESC
                LIMIT ?";
        return self::getDb()->fetchAll($sql, [$tenantId, $months]);
    }
}
