<?php
/**
 * Run All Tests
 * Execute all test files
 */

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║           SPLASHSECURITY TEST SUITE                          ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$tests = [
    'test_database.php',
    'test_scan_simulation.php',
    'test_risk_score.php',
    'test_alert_generation.php',
    'test_api_calls.php',
    'test_tenant_isolation.php'
];

$passed = 0;
$failed = 0;

foreach ($tests as $test) {
    echo "\n";
    echo str_repeat('─', 65) . "\n";

    $exitCode = 0;
    passthru("php " . __DIR__ . "/$test", $exitCode);

    if ($exitCode === 0) {
        $passed++;
    } else {
        $failed++;
    }
}

echo "\n";
echo str_repeat('═', 65) . "\n";
echo "TEST SUMMARY\n";
echo str_repeat('═', 65) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total:  " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✓ ALL TESTS PASSED!\n\n";
    exit(0);
} else {
    echo "\n✗ SOME TESTS FAILED\n\n";
    exit(1);
}
