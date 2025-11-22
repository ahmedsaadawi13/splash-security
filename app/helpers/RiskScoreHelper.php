<?php
/**
 * Risk Score Helper
 * Calculates overall risk scores for domains and IPs
 */
class RiskScoreHelper {

    public static function calculateDomainRiskScore($domainId, $tenantId) {
        $domain = MonitoredDomain::findById($domainId, $tenantId);
        if (!$domain) {
            return null;
        }

        // Get latest scan results
        $tlsScan = ScanResult::getLatestByType('domain', $domainId, 'ssl_tls', $tenantId);
        $emailScan = ScanResult::getLatestByType('domain', $domainId, 'email_security', $tenantId);
        $vulnScan = ScanResult::getLatestByType('domain', $domainId, 'vulnerability_scan', $tenantId);
        $darkwebScan = ScanResult::getLatestByType('domain', $domainId, 'darkweb_scan', $tenantId);

        // Get vulnerabilities
        $vulnerabilities = Vulnerability::getByAsset('domain', $domainId, $tenantId);

        // Calculate component scores
        $scores = [
            'tls' => self::getTlsScore($tlsScan),
            'email' => self::getEmailScore($emailScan),
            'vulnerabilities' => self::getVulnerabilityScore($vulnerabilities),
            'darkweb' => self::getDarkwebScore($darkwebScan)
        ];

        // Weighted average
        $weights = [
            'tls' => 0.25,
            'email' => 0.20,
            'vulnerabilities' => 0.40,
            'darkweb' => 0.15
        ];

        $totalScore = 0;
        $totalWeight = 0;

        foreach ($scores as $component => $score) {
            if ($score !== null) {
                $totalScore += $score * $weights[$component];
                $totalWeight += $weights[$component];
            }
        }

        $finalScore = $totalWeight > 0 ? round($totalScore / $totalWeight) : 50;
        $rating = self::scoreToRating($finalScore);

        return [
            'score' => $finalScore,
            'rating' => $rating,
            'component_scores' => $scores,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    public static function calculateIpRiskScore($ipId, $tenantId) {
        $ip = MonitoredIp::findById($ipId, $tenantId);
        if (!$ip) {
            return null;
        }

        // Get latest scan results
        $portScan = ScanResult::getLatestByType('ip', $ipId, 'port_scan', $tenantId);
        $vulnScan = ScanResult::getLatestByType('ip', $ipId, 'vulnerability_scan', $tenantId);
        $blacklistScan = ScanResult::getLatestByType('ip', $ipId, 'blacklist_check', $tenantId);

        // Get vulnerabilities
        $vulnerabilities = Vulnerability::getByAsset('ip', $ipId, $tenantId);

        // Calculate component scores
        $scores = [
            'ports' => self::getPortScore($portScan),
            'vulnerabilities' => self::getVulnerabilityScore($vulnerabilities),
            'blacklist' => self::getBlacklistScore($blacklistScan)
        ];

        // Weighted average
        $weights = [
            'ports' => 0.30,
            'vulnerabilities' => 0.50,
            'blacklist' => 0.20
        ];

        $totalScore = 0;
        $totalWeight = 0;

        foreach ($scores as $component => $score) {
            if ($score !== null) {
                $totalScore += $score * $weights[$component];
                $totalWeight += $weights[$component];
            }
        }

        $finalScore = $totalWeight > 0 ? round($totalScore / $totalWeight) : 50;
        $rating = self::scoreToRating($finalScore);

        return [
            'score' => $finalScore,
            'rating' => $rating,
            'component_scores' => $scores,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private static function getTlsScore($scan) {
        if (!$scan) return null;
        return $scan['score'] ?? 50;
    }

    private static function getEmailScore($scan) {
        if (!$scan) return null;
        return $scan['score'] ?? 50;
    }

    private static function getVulnerabilityScore($vulnerabilities) {
        if (empty($vulnerabilities)) {
            return 100; // No vulnerabilities = perfect score
        }

        $score = 100;
        $openVulns = array_filter($vulnerabilities, function($v) {
            return $v['status'] === 'open';
        });

        foreach ($openVulns as $vuln) {
            switch ($vuln['severity']) {
                case 'critical':
                    $score -= 25;
                    break;
                case 'high':
                    $score -= 15;
                    break;
                case 'medium':
                    $score -= 8;
                    break;
                case 'low':
                    $score -= 3;
                    break;
            }
        }

        return max(0, $score);
    }

    private static function getDarkwebScore($scan) {
        if (!$scan) return null;

        $result = json_decode($scan['result_json'], true);
        $totalLeaks = $result['total_leaks'] ?? 0;

        if ($totalLeaks === 0) return 100;
        if ($totalLeaks <= 2) return 80;
        if ($totalLeaks <= 5) return 60;
        if ($totalLeaks <= 10) return 40;
        return 20;
    }

    private static function getPortScore($scan) {
        if (!$scan) return null;

        $result = json_decode($scan['result_json'], true);
        $riskLevel = $result['risk_level'] ?? 'low';

        switch ($riskLevel) {
            case 'high':
                return 50;
            case 'medium':
                return 75;
            case 'low':
            default:
                return 90;
        }
    }

    private static function getBlacklistScore($scan) {
        if (!$scan) return null;

        $result = json_decode($scan['result_json'], true);
        $isBlacklisted = $result['is_blacklisted'] ?? false;
        $numBlacklists = count($result['blacklists'] ?? []);

        if (!$isBlacklisted) return 100;
        if ($numBlacklists === 1) return 60;
        if ($numBlacklists === 2) return 40;
        return 20;
    }

    public static function scoreToRating($score) {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }

    public static function getRatingColor($rating) {
        $colors = [
            'A' => '#10b981', // green
            'B' => '#3b82f6', // blue
            'C' => '#f59e0b', // yellow
            'D' => '#f97316', // orange
            'F' => '#ef4444'  // red
        ];

        return $colors[$rating] ?? '#6b7280';
    }

    public static function getSeverityColor($severity) {
        $colors = [
            'critical' => '#dc2626',
            'high' => '#f97316',
            'medium' => '#f59e0b',
            'low' => '#3b82f6',
            'info' => '#6b7280'
        ];

        return $colors[$severity] ?? '#6b7280';
    }
}
