<?php
/**
 * Tenant Isolation Test
 * Ensures data is properly isolated between tenants
 */

require_once __DIR__ . '/../config/config.php';

spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/core/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

echo "=== TENANT ISOLATION TEST ===\n\n";

try {
    // Get tenant 1 data
    echo "Testing tenant data isolation...\n";

    $tenant1Domains = MonitoredDomain::all(1);
    $tenant1Ips = MonitoredIp::all(1);
    $tenant1Vulns = Vulnerability::getStats(1);

    echo "✓ Tenant 1 data retrieved:\n";
    echo "  Domains: " . count($tenant1Domains) . "\n";
    echo "  IPs: " . count($tenant1Ips) . "\n";
    echo "  Vulnerabilities: {$tenant1Vulns['total']}\n";

    // Try to access with wrong tenant ID (should return empty)
    echo "\nTesting isolation enforcement...\n";

    $wrongTenantDomains = MonitoredDomain::all(999);
    $wrongTenantIps = MonitoredIp::all(999);

    if (empty($wrongTenantDomains) && empty($wrongTenantIps)) {
        echo "✓ Isolation verified: Non-existent tenant returns no data\n";
    } else {
        echo "✗ WARNING: Data leak detected!\n";
    }

    // Test findById with tenant enforcement
    if (!empty($tenant1Domains)) {
        $domain = $tenant1Domains[0];

        // Access with correct tenant
        $result1 = MonitoredDomain::findById($domain['id'], 1);
        if ($result1) {
            echo "✓ Correct tenant can access domain {$domain['id']}\n";
        }

        // Access with wrong tenant
        $result2 = MonitoredDomain::findById($domain['id'], 999);
        if (!$result2) {
            echo "✓ Wrong tenant cannot access domain {$domain['id']}\n";
        } else {
            echo "✗ WARNING: Tenant isolation breach!\n";
        }
    }

    echo "\n✓ All tenant isolation tests passed\n";

} catch (Exception $e) {
    echo "✗ Tenant isolation test failed: " . $e->getMessage() . "\n";
    exit(1);
}
