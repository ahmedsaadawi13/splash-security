<?php
/**
 * User Model
 */
class User extends Model {
    protected static $table = 'users';

    public static function findByEmail($email) {
        $sql = "SELECT * FROM " . self::$table . " WHERE email = ?";
        return self::getDb()->fetchOne($sql, [$email]);
    }

    public static function authenticate($email, $password) {
        $user = self::findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!Security::verifyPassword($password, $user['password'])) {
            return false;
        }

        if ($user['status'] !== 'active') {
            return false;
        }

        // Update last login
        self::update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return $user;
    }

    public static function createUser($data) {
        if (isset($data['password'])) {
            $data['password'] = Security::hashPassword($data['password']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return self::create($data);
    }

    public static function updatePassword($userId, $newPassword) {
        $hashedPassword = Security::hashPassword($newPassword);
        return self::update($userId, ['password' => $hashedPassword]);
    }

    public static function getByTenant($tenantId) {
        return self::where([], $tenantId);
    }

    public static function hasPermission($user, $permission) {
        $permissions = [
            'platform_admin' => ['*'],
            'tenant_admin' => ['manage_assets', 'manage_scans', 'manage_alerts', 'manage_team', 'view_reports'],
            'security_analyst' => ['view_assets', 'manage_scans', 'view_alerts', 'view_reports'],
            'read_only' => ['view_assets', 'view_alerts', 'view_reports']
        ];

        $rolePermissions = $permissions[$user['role']] ?? [];

        return in_array('*', $rolePermissions) || in_array($permission, $rolePermissions);
    }
}
