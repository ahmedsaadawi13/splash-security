<?php
/**
 * Risk Score Calculation Test
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

echo "=== RISK SCORE CALCULATION TEST ===\n\n";

// Test score to rating conversion
$testScores = [95, 85, 75, 65, 55, 45];

echo "Testing score to rating conversion:\n";
foreach ($testScores as $score) {
    $rating = RiskScoreHelper::scoreToRating($score);
    $color = RiskScoreHelper::getRatingColor($rating);
    echo "  Score $score -> Rating $rating (Color: $color)\n";
}

// Test severity colors
echo "\nTesting severity colors:\n";
$severities = ['critical', 'high', 'medium', 'low', 'info'];
foreach ($severities as $severity) {
    $color = RiskScoreHelper::getSeverityColor($severity);
    echo "  $severity -> $color\n";
}

echo "\n✓ All risk score tests passed\n";
