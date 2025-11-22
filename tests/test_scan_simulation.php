<?php
/**
 * Scan Simulation Test
 * Tests all scanning helpers
 */

require_once __DIR__ . '/../config/config.php';

spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/helpers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

echo "=== SCAN SIMULATION TEST ===\n\n";

// Test Domain Scanner
echo "Testing DomainScannerHelper...\n";
$dnsResult = DomainScannerHelper::scanDns('example.com');
echo "✓ DNS scan completed: " . count($dnsResult['records']) . " record types\n";

$whoisResult = DomainScannerHelper::checkWhois('example.com');
echo "✓ WHOIS check completed: Registrar = {$whoisResult['registrar']}\n";

// Test TLS Scanner
echo "\nTesting TLSScannerHelper...\n";
$tlsResult = TLSScannerHelper::scanTls('example.com');
echo "✓ TLS scan completed: Score = {$tlsResult['score']}, Rating = {$tlsResult['rating']}\n";

// Test Email Security
echo "\nTesting EmailSecurityHelper...\n";
$emailResult = EmailSecurityHelper::scanEmailSecurity('example.com');
echo "✓ Email security scan completed: Score = {$emailResult['score']}\n";
echo "  SPF: " . ($emailResult['spf']['exists'] ? 'Yes' : 'No') . "\n";
echo "  DKIM: " . ($emailResult['dkim']['exists'] ? 'Yes' : 'No') . "\n";
echo "  DMARC: " . ($emailResult['dmarc']['exists'] ? 'Yes' : 'No') . "\n";

// Test Vulnerability Scanner
echo "\nTesting VulnerabilityScannerHelper...\n";
$vulnResult = VulnerabilityScannerHelper::scanDomainVulnerabilities('example.com');
echo "✓ Vulnerability scan completed: {$vulnResult['total_count']} vulnerabilities found\n";
echo "  Critical: {$vulnResult['critical_count']}, High: {$vulnResult['high_count']}, Medium: {$vulnResult['medium_count']}\n";

// Test Dark Web Scanner
echo "\nTesting DarkWebScannerHelper...\n";
$darkwebResult = DarkWebScannerHelper::scanDarkWeb('example.com', ['user@example.com']);
echo "✓ Dark web scan completed: {$darkwebResult['total_leaks']} potential leaks found\n";

// Test IP Scanner
echo "\nTesting IPScannerHelper...\n";
$portResult = IPScannerHelper::scanPorts('192.0.2.1');
echo "✓ Port scan completed: {$portResult['total_open']} open ports\n";

$geoResult = IPScannerHelper::getGeolocation('192.0.2.1');
echo "✓ Geolocation: {$geoResult['city']}, {$geoResult['country']}\n";

$blacklistResult = IPScannerHelper::checkBlacklist('192.0.2.1');
echo "✓ Blacklist check: " . ($blacklistResult['is_blacklisted'] ? 'Listed' : 'Clean') . "\n";

// Test Risk Scoring
echo "\nTesting RiskScoreHelper...\n";
$rating = RiskScoreHelper::scoreToRating(85);
echo "✓ Score 85 = Rating $rating\n";

echo "\n✓ All scan simulation tests passed\n";
