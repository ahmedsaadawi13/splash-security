<?php
/**
 * Plan Model
 */
class Plan extends Model {
    protected static $table = 'plans';

    public static function findBySlug($slug) {
        $sql = "SELECT * FROM " . self::$table . " WHERE slug = ?";
        return self::getDb()->fetchOne($sql, [$slug]);
    }

    public static function getActive() {
        $sql = "SELECT * FROM " . self::$table . " WHERE status = 'active' ORDER BY price ASC";
        return self::getDb()->fetchAll($sql);
    }

    public static function getFeatures($planId) {
        $plan = self::findById($planId);
        if ($plan && $plan['features_json']) {
            return json_decode($plan['features_json'], true);
        }
        return [];
    }

    public static function hasFeature($planId, $feature) {
        $features = self::getFeatures($planId);
        return isset($features[$feature]) && $features[$feature] === true;
    }
}
