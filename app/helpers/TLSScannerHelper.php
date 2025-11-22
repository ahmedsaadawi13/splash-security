<?php
/**
 * TLS/SSL Scanner Helper
 * Simulates SSL/TLS security assessment
 */
class TLSScannerHelper {

    public static function scanTls($domain) {
        $hasHttps = self::checkHttps($domain);

        if (!$hasHttps) {
            return [
                'domain' => $domain,
                'has_https' => false,
                'score' => 0,
                'rating' => 'F',
                'issues' => ['No HTTPS detected'],
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        $certInfo = self::getCertificateInfo($domain);
        $protocols = self::getSupportedProtocols();
        $ciphers = self::getSupportedCiphers();

        $score = self::calculateTlsScore($certInfo, $protocols, $ciphers);
        $rating = RiskScoreHelper::scoreToRating($score);

        return [
            'domain' => $domain,
            'has_https' => true,
            'certificate' => $certInfo,
            'protocols' => $protocols,
            'ciphers' => $ciphers,
            'score' => $score,
            'rating' => $rating,
            'issues' => self::findIssues($certInfo, $protocols, $ciphers),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private static function checkHttps($domain) {
        // Simulate HTTPS check (90% have HTTPS)
        return rand(1, 10) <= 9;
    }

    private static function getCertificateInfo($domain) {
        $daysToExpiry = rand(15, 365);
        $isExpiringSoon = $daysToExpiry <= 30;

        return [
            'issuer' => self::getRandomIssuer(),
            'subject' => $domain,
            'valid_from' => date('Y-m-d', strtotime('-1 year')),
            'valid_to' => date('Y-m-d', strtotime("+$daysToExpiry days")),
            'days_to_expiry' => $daysToExpiry,
            'is_expiring_soon' => $isExpiringSoon,
            'signature_algorithm' => 'sha256WithRSAEncryption',
            'key_size' => rand(0, 10) > 2 ? 2048 : 4096,
            'is_wildcard' => rand(0, 10) > 7,
            'san' => ['www.' . $domain, $domain]
        ];
    }

    private static function getRandomIssuer() {
        $issuers = ["Let's Encrypt", 'DigiCert', 'Sectigo', 'GlobalSign', 'GoDaddy'];
        return $issuers[array_rand($issuers)];
    }

    private static function getSupportedProtocols() {
        // Simulate protocol support
        $allProtocols = [
            'TLS 1.3' => rand(0, 10) > 3, // 70% support
            'TLS 1.2' => true,
            'TLS 1.1' => rand(0, 10) > 8, // 20% support (bad)
            'TLS 1.0' => rand(0, 10) > 9, // 10% support (very bad)
        ];

        return array_filter($allProtocols);
    }

    private static function getSupportedCiphers() {
        // Simulate cipher support
        return [
            'TLS_AES_256_GCM_SHA384',
            'TLS_CHACHA20_POLY1305_SHA256',
            'TLS_AES_128_GCM_SHA256',
            'TLS_ECDHE_RSA_WITH_AES_256_GCM_SHA384',
            'TLS_ECDHE_RSA_WITH_AES_128_GCM_SHA256'
        ];
    }

    private static function calculateTlsScore($certInfo, $protocols, $ciphers) {
        $score = 100;

        // Certificate issues
        if ($certInfo['days_to_expiry'] < 30) {
            $score -= 20;
        }
        if ($certInfo['key_size'] < 2048) {
            $score -= 30;
        }

        // Protocol issues
        if (isset($protocols['TLS 1.0'])) {
            $score -= 40;
        }
        if (isset($protocols['TLS 1.1'])) {
            $score -= 20;
        }
        if (!isset($protocols['TLS 1.3'])) {
            $score -= 10;
        }

        // Cipher strength
        if (count($ciphers) < 3) {
            $score -= 10;
        }

        return max(0, $score);
    }

    private static function findIssues($certInfo, $protocols, $ciphers) {
        $issues = [];

        if ($certInfo['is_expiring_soon']) {
            $issues[] = "SSL certificate expires in {$certInfo['days_to_expiry']} days";
        }

        if ($certInfo['key_size'] < 2048) {
            $issues[] = "Weak key size ({$certInfo['key_size']} bits)";
        }

        if (isset($protocols['TLS 1.0'])) {
            $issues[] = 'TLS 1.0 is enabled (deprecated and insecure)';
        }

        if (isset($protocols['TLS 1.1'])) {
            $issues[] = 'TLS 1.1 is enabled (deprecated)';
        }

        if (!isset($protocols['TLS 1.3'])) {
            $issues[] = 'TLS 1.3 not supported';
        }

        if (empty($issues)) {
            $issues[] = 'No major issues detected';
        }

        return $issues;
    }

    public static function checkHsts($domain) {
        // Simulate HSTS check
        $hasHsts = rand(0, 10) > 4; // 60% have HSTS

        return [
            'enabled' => $hasHsts,
            'max_age' => $hasHsts ? 31536000 : null,
            'include_subdomains' => $hasHsts ? (rand(0, 1) === 1) : false,
            'preload' => $hasHsts ? (rand(0, 10) > 7) : false
        ];
    }
}
