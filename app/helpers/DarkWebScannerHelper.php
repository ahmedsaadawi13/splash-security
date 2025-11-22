<?php
/**
 * Dark Web Scanner Helper
 * Simulates dark web exposure checks
 */
class DarkWebScannerHelper {

    public static function scanDarkWeb($domain, $emails = []) {
        $breaches = self::simulateBreachCheck($domain);
        $exposedEmails = self::simulateEmailLeaks($emails);
        $pasteLeaks = self::simulatePasteLeaks($domain);

        $totalLeaks = count($breaches) + count($exposedEmails) + count($pasteLeaks);
        $severity = self::calculateSeverity($totalLeaks);

        return [
            'domain' => $domain,
            'breaches' => $breaches,
            'exposed_emails' => $exposedEmails,
            'paste_leaks' => $pasteLeaks,
            'total_leaks' => $totalLeaks,
            'severity' => $severity,
            'recommendations' => self::getRecommendations($totalLeaks),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private static function simulateBreachCheck($domain) {
        $breaches = [];

        // 30% chance of finding breaches
        if (rand(0, 10) > 7) {
            $possibleBreaches = [
                [
                    'name' => 'Data Breach 2023',
                    'date' => date('Y-m-d', strtotime('-' . rand(30, 365) . ' days')),
                    'exposed_records' => rand(1000, 50000),
                    'data_types' => ['emails', 'passwords', 'usernames'],
                    'severity' => 'high'
                ],
                [
                    'name' => 'Forum Leak 2024',
                    'date' => date('Y-m-d', strtotime('-' . rand(10, 180) . ' days')),
                    'exposed_records' => rand(500, 5000),
                    'data_types' => ['emails', 'usernames', 'ip_addresses'],
                    'severity' => 'medium'
                ],
                [
                    'name' => 'Database Dump 2022',
                    'date' => date('Y-m-d', strtotime('-' . rand(365, 730) . ' days')),
                    'exposed_records' => rand(10000, 100000),
                    'data_types' => ['emails', 'passwords', 'phone_numbers', 'addresses'],
                    'severity' => 'critical'
                ]
            ];

            $numBreaches = rand(1, 2);
            for ($i = 0; $i < $numBreaches; $i++) {
                $breaches[] = $possibleBreaches[array_rand($possibleBreaches)];
            }
        }

        return $breaches;
    }

    private static function simulateEmailLeaks($emails) {
        $leakedEmails = [];

        foreach ($emails as $email) {
            // 25% chance each email is leaked
            if (rand(0, 10) > 7) {
                $leakedEmails[] = [
                    'email' => $email,
                    'first_seen' => date('Y-m-d', strtotime('-' . rand(30, 365) . ' days')),
                    'sources' => rand(1, 5),
                    'exposed_data' => self::getRandomExposedData(),
                    'severity' => rand(0, 10) > 7 ? 'high' : 'medium'
                ];
            }
        }

        return $leakedEmails;
    }

    private static function simulatePasteLeaks($domain) {
        $pastes = [];

        // 20% chance of finding paste leaks
        if (rand(0, 10) > 8) {
            $numPastes = rand(1, 3);
            for ($i = 0; $i < $numPastes; $i++) {
                $pastes[] = [
                    'source' => self::getRandomPasteSource(),
                    'title' => $domain . ' credentials',
                    'date' => date('Y-m-d', strtotime('-' . rand(10, 180) . ' days')),
                    'content_preview' => 'Email and password combinations for ' . $domain,
                    'severity' => rand(0, 10) > 6 ? 'high' : 'medium'
                ];
            }
        }

        return $pastes;
    }

    private static function getRandomExposedData() {
        $allTypes = ['email', 'password', 'username', 'phone', 'address', 'credit_card', 'ssn', 'dob'];
        $numTypes = rand(2, 5);
        shuffle($allTypes);
        return array_slice($allTypes, 0, $numTypes);
    }

    private static function getRandomPasteSource() {
        $sources = ['Pastebin', 'GitHub Gist', 'Dark Web Forum', 'Telegram Channel', 'Discord Server'];
        return $sources[array_rand($sources)];
    }

    private static function calculateSeverity($totalLeaks) {
        if ($totalLeaks === 0) {
            return 'info';
        } elseif ($totalLeaks <= 2) {
            return 'low';
        } elseif ($totalLeaks <= 5) {
            return 'medium';
        } elseif ($totalLeaks <= 10) {
            return 'high';
        } else {
            return 'critical';
        }
    }

    private static function getRecommendations($totalLeaks) {
        $recommendations = [];

        if ($totalLeaks === 0) {
            $recommendations[] = 'No dark web exposure detected. Continue monitoring.';
        } else {
            $recommendations[] = 'Force password reset for all affected accounts';
            $recommendations[] = 'Enable multi-factor authentication (MFA)';
            $recommendations[] = 'Monitor accounts for suspicious activity';
            $recommendations[] = 'Notify affected users about the breach';
            $recommendations[] = 'Review and strengthen security measures';
        }

        return $recommendations;
    }

    public static function monitorCredentials($domain, $credentials) {
        // Simulate credential monitoring
        $compromised = [];

        foreach ($credentials as $credential) {
            // 15% chance each credential is compromised
            if (rand(0, 10) > 8) {
                $compromised[] = [
                    'credential' => $credential,
                    'found_in' => self::getRandomPasteSource(),
                    'discovered_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 90) . ' days')),
                    'action_required' => 'immediate_reset'
                ];
            }
        }

        return [
            'total_checked' => count($credentials),
            'compromised_count' => count($compromised),
            'compromised_credentials' => $compromised,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
