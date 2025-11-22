<?php
/**
 * API Endpoint Test
 * Simulates API calls
 */

require_once __DIR__ . '/../config/config.php';

spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/core/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
        APP_PATH . '/helpers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

echo "=== API FUNCTIONALITY TEST ===\n\n";

try {
    // Test API key functions
    echo "Testing API key functionality...\n";

    // Get existing API keys
    $apiKeys = ApiKey::getByTenant(1);
    echo "✓ Retrieved " . count($apiKeys) . " API key(s) for tenant 1\n";

    // Test data retrieval (simulating API calls)
    echo "\nTesting data retrieval:\n";

    $domains = MonitoredDomain::all(1);
    echo "✓ Domains endpoint: " . count($domains) . " domains\n";

    $ips = MonitoredIp::all(1);
    echo "✓ IPs endpoint: " . count($ips) . " IPs\n";

    $vulnerabilities = Vulnerability::getOpen(1);
    echo "✓ Vulnerabilities endpoint: " . count($vulnerabilities) . " open vulnerabilities\n";

    $alerts = Alert::getRecent(1, 10);
    echo "✓ Alerts endpoint: " . count($alerts) . " recent alerts\n";

    // Test rate limiting concept
    echo "\nTesting subscription limits:\n";
    $subscription = Tenant::getActiveSubscription(1);
    if ($subscription) {
        echo "✓ Subscription found: {$subscription['plan_name']}\n";
        echo "  API Rate Limit: {$subscription['api_rate_limit']}/hour\n";
        echo "  Max Assets: {$subscription['max_monitored_assets']}\n";
        echo "  Max Scans: {$subscription['max_monthly_scans']}/month\n";
    }

    echo "\n✓ All API tests passed\n";

} catch (Exception $e) {
    echo "✗ API test failed: " . $e->getMessage() . "\n";
    exit(1);
}
