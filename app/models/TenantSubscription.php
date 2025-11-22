<?php
/**
 * TenantSubscription Model
 */
class TenantSubscription extends Model {
    protected static $table = 'tenant_subscriptions';

    public static function getActiveByTenant($tenantId) {
        $sql = "SELECT ts.*, p.name as plan_name, p.slug as plan_slug,
                       p.max_monitored_assets, p.max_monthly_scans, p.max_alerts,
                       p.api_rate_limit, p.features_json
                FROM " . self::$table . " ts
                JOIN plans p ON ts.plan_id = p.id
                WHERE ts.tenant_id = ? AND ts.status = 'active'
                ORDER BY ts.id DESC LIMIT 1";
        return self::getDb()->fetchOne($sql, [$tenantId]);
    }

    public static function isExpired($subscriptionId) {
        $subscription = self::findById($subscriptionId);
        if (!$subscription || !$subscription['expires_at']) {
            return false;
        }

        return strtotime($subscription['expires_at']) < time();
    }

    public static function expireSubscriptions() {
        $sql = "UPDATE " . self::$table . "
                SET status = 'expired'
                WHERE status = 'active' AND expires_at < NOW()";
        return self::getDb()->execute($sql);
    }
}
