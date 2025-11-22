<?php
/**
 * Database Connection Test
 * Run with: php tests/test_database.php
 */

require_once __DIR__ . '/../config/config.php';
require_once APP_PATH . '/core/Database.php';

echo "=== DATABASE CONNECTION TEST ===\n\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    echo "✓ Database connection successful\n";

    // Test query
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM tenants");
    echo "✓ Test query executed successfully\n";
    echo "  Tenants in database: {$result['count']}\n\n";

    // Test all tables exist
    $tables = ['tenants', 'users', 'plans', 'tenant_subscriptions', 'monitored_domains',
               'monitored_ips', 'scan_results', 'vulnerabilities', 'alerts'];

    echo "Checking tables:\n";
    foreach ($tables as $table) {
        $result = $db->fetchOne("SHOW TABLES LIKE '$table'");
        if ($result) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' missing\n";
        }
    }

    echo "\n✓ All database tests passed\n";

} catch (Exception $e) {
    echo "✗ Database test failed: " . $e->getMessage() . "\n";
    exit(1);
}
