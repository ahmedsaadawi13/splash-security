<?php
/**
 * Tenant Model
 */
class Tenant extends Model {
    protected static $table = 'tenants';

    public static function findBySlug($slug) {
        $sql = "SELECT * FROM " . self::$table . " WHERE slug = ?";
        return self::getDb()->fetchOne($sql, [$slug]);
    }

    public static function getActiveSubscription($tenantId) {
        $sql = "SELECT ts.*, p.*
                FROM tenant_subscriptions ts
                JOIN plans p ON ts.plan_id = p.id
                WHERE ts.tenant_id = ? AND ts.status = 'active'
                ORDER BY ts.id DESC LIMIT 1";
        return self::getDb()->fetchOne($sql, [$tenantId]);
    }

    public static function getCurrentUsage($tenantId) {
        $periodStart = date('Y-m-01');
        $periodEnd = date('Y-m-t');

        $sql = "SELECT * FROM tenant_usage
                WHERE tenant_id = ? AND period_start = ? AND period_end = ?";
        $usage = self::getDb()->fetchOne($sql, [$tenantId, $periodStart, $periodEnd]);

        if (!$usage) {
            // Create usage record for current period
            $data = [
                'tenant_id' => $tenantId,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'monitored_assets_count' => 0,
                'scans_count' => 0,
                'alerts_count' => 0,
                'api_calls_count' => 0
            ];
            TenantUsage::create($data);
            $usage = $data;
        }

        return $usage;
    }

    public static function canAddAsset($tenantId) {
        $subscription = self::getActiveSubscription($tenantId);
        if (!$subscription) {
            return false;
        }

        $currentCount = MonitoredDomain::count($tenantId) + MonitoredIp::count($tenantId);
        return $currentCount < $subscription['max_monitored_assets'];
    }

    public static function canRunScan($tenantId) {
        $subscription = self::getActiveSubscription($tenantId);
        if (!$subscription) {
            return false;
        }

        $usage = self::getCurrentUsage($tenantId);
        return $usage['scans_count'] < $subscription['max_monthly_scans'];
    }

    public static function incrementScanCount($tenantId) {
        $periodStart = date('Y-m-01');
        $periodEnd = date('Y-m-t');

        $sql = "UPDATE tenant_usage
                SET scans_count = scans_count + 1, updated_at = NOW()
                WHERE tenant_id = ? AND period_start = ? AND period_end = ?";
        self::getDb()->execute($sql, [$tenantId, $periodStart, $periodEnd]);
    }

    public static function incrementApiCallCount($tenantId) {
        $periodStart = date('Y-m-01');
        $periodEnd = date('Y-m-t');

        $sql = "UPDATE tenant_usage
                SET api_calls_count = api_calls_count + 1, updated_at = NOW()
                WHERE tenant_id = ? AND period_start = ? AND period_end = ?";
        self::getDb()->execute($sql, [$tenantId, $periodStart, $periodEnd]);
    }
}
