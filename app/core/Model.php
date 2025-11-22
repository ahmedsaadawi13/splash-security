<?php
/**
 * Base Model Class
 * All models extend this class
 */
class Model {
    protected static $table = '';
    protected static $db;

    protected static function getDb() {
        if (!self::$db) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    public static function all($tenantId = null) {
        $sql = "SELECT * FROM " . static::$table;
        $params = [];

        if ($tenantId !== null) {
            $sql .= " WHERE tenant_id = ?";
            $params[] = $tenantId;
        }

        $sql .= " ORDER BY id DESC";

        return self::getDb()->fetchAll($sql, $params);
    }

    public static function findById($id, $tenantId = null) {
        $sql = "SELECT * FROM " . static::$table . " WHERE id = ?";
        $params = [$id];

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }

        return self::getDb()->fetchOne($sql, $params);
    }

    public static function create($data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $fields) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        self::getDb()->execute($sql, array_values($data));
        return self::getDb()->lastInsertId();
    }

    public static function update($id, $data, $tenantId = null) {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $fields) . " WHERE id = ?";
        $values[] = $id;

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $values[] = $tenantId;
        }

        return self::getDb()->execute($sql, $values);
    }

    public static function delete($id, $tenantId = null) {
        $sql = "DELETE FROM " . static::$table . " WHERE id = ?";
        $params = [$id];

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }

        return self::getDb()->execute($sql, $params);
    }

    public static function count($tenantId = null) {
        $sql = "SELECT COUNT(*) FROM " . static::$table;
        $params = [];

        if ($tenantId !== null) {
            $sql .= " WHERE tenant_id = ?";
            $params[] = $tenantId;
        }

        return self::getDb()->fetchColumn($sql, $params);
    }

    public static function where($conditions, $tenantId = null) {
        $whereClauses = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $whereClauses[] = "$key = ?";
            $params[] = $value;
        }

        if ($tenantId !== null) {
            $whereClauses[] = "tenant_id = ?";
            $params[] = $tenantId;
        }

        $sql = "SELECT * FROM " . static::$table . " WHERE " . implode(' AND ', $whereClauses);

        return self::getDb()->fetchAll($sql, $params);
    }
}
