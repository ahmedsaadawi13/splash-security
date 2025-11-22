<?php
/**
 * Email Security Helper
 * Checks SPF, DKIM, DMARC configuration
 */
class EmailSecurityHelper {

    public static function scanEmailSecurity($domain) {
        $spf = self::checkSpf($domain);
        $dkim = self::checkDkim($domain);
        $dmarc = self::checkDmarc($domain);

        $score = self::calculateEmailScore($spf, $dkim, $dmarc);
        $rating = RiskScoreHelper::scoreToRating($score);

        return [
            'domain' => $domain,
            'spf' => $spf,
            'dkim' => $dkim,
            'dmarc' => $dmarc,
            'score' => $score,
            'rating' => $rating,
            'issues' => self::findIssues($spf, $dkim, $dmarc),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private static function checkSpf($domain) {
        // Simulate SPF check (70% have SPF)
        $hasSpf = rand(0, 10) > 3;

        if (!$hasSpf) {
            return [
                'exists' => false,
                'record' => null,
                'valid' => false,
                'policy' => null
            ];
        }

        $policies = ['~all', '-all', '?all', '+all'];
        $policy = $policies[rand(0, count($policies) - 1)];

        return [
            'exists' => true,
            'record' => "v=spf1 include:_spf.$domain $policy",
            'valid' => true,
            'policy' => $policy,
            'includes' => ["_spf.$domain"],
            'ip4' => [],
            'ip6' => []
        ];
    }

    private static function checkDkim($domain) {
        // Simulate DKIM check (60% have DKIM)
        $hasDkim = rand(0, 10) > 4;

        return [
            'exists' => $hasDkim,
            'selector' => $hasDkim ? 'default' : null,
            'valid' => $hasDkim,
            'key_size' => $hasDkim ? (rand(0, 10) > 3 ? 2048 : 1024) : null
        ];
    }

    private static function checkDmarc($domain) {
        // Simulate DMARC check (50% have DMARC)
        $hasDmarc = rand(0, 10) >= 5;

        if (!$hasDmarc) {
            return [
                'exists' => false,
                'record' => null,
                'valid' => false,
                'policy' => null
            ];
        }

        $policies = ['none', 'quarantine', 'reject'];
        $policy = $policies[rand(0, count($policies) - 1)];

        return [
            'exists' => true,
            'record' => "v=DMARC1; p=$policy; rua=mailto:dmarc@$domain",
            'valid' => true,
            'policy' => $policy,
            'subdomain_policy' => $policy,
            'percentage' => 100,
            'rua' => ["mailto:dmarc@$domain"],
            'ruf' => []
        ];
    }

    private static function calculateEmailScore($spf, $dkim, $dmarc) {
        $score = 0;

        // SPF scoring
        if ($spf['exists'] && $spf['valid']) {
            $score += 30;
            if ($spf['policy'] === '-all') {
                $score += 5;
            } elseif ($spf['policy'] === '~all') {
                $score += 3;
            }
        }

        // DKIM scoring
        if ($dkim['exists'] && $dkim['valid']) {
            $score += 30;
            if ($dkim['key_size'] >= 2048) {
                $score += 5;
            }
        }

        // DMARC scoring
        if ($dmarc['exists'] && $dmarc['valid']) {
            $score += 25;
            if ($dmarc['policy'] === 'reject') {
                $score += 10;
            } elseif ($dmarc['policy'] === 'quarantine') {
                $score += 5;
            }
        }

        return min(100, $score);
    }

    private static function findIssues($spf, $dkim, $dmarc) {
        $issues = [];

        if (!$spf['exists']) {
            $issues[] = 'No SPF record found - email spoofing risk';
        } elseif ($spf['policy'] === '+all' || $spf['policy'] === '?all') {
            $issues[] = 'SPF policy is too permissive';
        }

        if (!$dkim['exists']) {
            $issues[] = 'No DKIM signature found - email authenticity cannot be verified';
        } elseif ($dkim['key_size'] < 2048) {
            $issues[] = 'DKIM key size is weak (less than 2048 bits)';
        }

        if (!$dmarc['exists']) {
            $issues[] = 'No DMARC record found - no email authentication policy';
        } elseif ($dmarc['policy'] === 'none') {
            $issues[] = 'DMARC policy is set to "none" (monitoring only)';
        }

        if (empty($issues)) {
            $issues[] = 'Email security configuration is strong';
        }

        return $issues;
    }

    public static function checkMxRecords($domain) {
        // Simulate MX record check
        return [
            [
                'priority' => 10,
                'host' => 'mail1.' . $domain,
                'ip' => '192.0.2.' . rand(1, 254)
            ],
            [
                'priority' => 20,
                'host' => 'mail2.' . $domain,
                'ip' => '192.0.2.' . rand(1, 254)
            ]
        ];
    }
}
