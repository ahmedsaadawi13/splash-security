<?php
/**
 * Alert Generation Test
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

echo "=== ALERT GENERATION TEST ===\n\n";

try {
    // Get demo tenant
    $tenant = Tenant::findById(1);
    if (!$tenant) {
        echo "✗ Demo tenant not found\n";
        exit(1);
    }

    echo "✓ Found tenant: {$tenant['name']}\n";

    // Get alert statistics
    $stats = Alert::getStats(1);
    echo "✓ Alert statistics retrieved:\n";
    echo "  Total: {$stats['total']}\n";
    echo "  Open: {$stats['open']}\n";
    echo "  Critical: {$stats['critical']}\n";
    echo "  High: {$stats['high']}\n";

    // Get open alerts
    $openAlerts = Alert::getOpen(1);
    echo "✓ Retrieved " . count($openAlerts) . " open alerts\n";

    if (!empty($openAlerts)) {
        echo "\nSample alert:\n";
        $alert = $openAlerts[0];
        echo "  Title: {$alert['title']}\n";
        echo "  Severity: {$alert['severity']}\n";
        echo "  Status: {$alert['status']}\n";
    }

    echo "\n✓ All alert tests passed\n";

} catch (Exception $e) {
    echo "✗ Alert test failed: " . $e->getMessage() . "\n";
    exit(1);
}
