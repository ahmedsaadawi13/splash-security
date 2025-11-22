<?php
/**
 * ApiKey Model
 */
class ApiKey extends Model {
    protected static $table = 'api_keys';

    public static function findByKey($key) {
        $keyHash = hash('sha256', $key);
        $sql = "SELECT ak.*, u.tenant_id, u.role
                FROM " . self::$table . " ak
                JOIN users u ON ak.user_id = u.id
                WHERE ak.key_hash = ? AND ak.status = 'active'";
        return self::getDb()->fetchOne($sql, [$keyHash]);
    }

    public static function createKey($tenantId, $userId, $name) {
        $key = Security::generateApiKey();
        $keyHash = hash('sha256', $key);

        $data = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'key_hash' => $keyHash,
            'name' => $name,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = self::create($data);

        // Return the actual key only once (it won't be stored in plain text)
        return [
            'id' => $id,
            'key' => $key
        ];
    }

    public static function updateLastUsed($keyId) {
        $sql = "UPDATE " . self::$table . "
                SET last_used_at = NOW()
                WHERE id = ?";
        self::getDb()->execute($sql, [$keyId]);
    }

    public static function revoke($keyId, $tenantId = null) {
        return self::update($keyId, ['status' => 'revoked'], $tenantId);
    }

    public static function getByTenant($tenantId) {
        $sql = "SELECT ak.*, u.email, u.first_name, u.last_name
                FROM " . self::$table . " ak
                JOIN users u ON ak.user_id = u.id
                WHERE ak.tenant_id = ?
                ORDER BY ak.created_at DESC";
        return self::getDb()->fetchAll($sql, [$tenantId]);
    }
}
