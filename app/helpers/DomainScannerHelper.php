<?php
/**
 * Domain Scanner Helper
 * Simulates domain scanning and DNS analysis
 */
class DomainScannerHelper {

    public static function scanDns($domain) {
        // Simulate DNS lookup
        $records = [
            'A' => [gethostbyname($domain)],
            'MX' => self::simulateMxRecords($domain),
            'TXT' => self::simulateTxtRecords($domain),
            'NS' => self::simulateNsRecords($domain),
            'SOA' => self::simulateSoaRecord($domain)
        ];

        return [
            'domain' => $domain,
            'records' => $records,
            'status' => 'success',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private static function simulateMxRecords($domain) {
        return [
            ['priority' => 10, 'host' => 'mail1.' . $domain],
            ['priority' => 20, 'host' => 'mail2.' . $domain]
        ];
    }

    private static function simulateTxtRecords($domain) {
        return [
            'v=spf1 include:_spf.' . $domain . ' ~all',
            'google-site-verification=' . bin2hex(random_bytes(16))
        ];
    }

    private static function simulateNsRecords($domain) {
        return [
            'ns1.' . $domain,
            'ns2.' . $domain
        ];
    }

    private static function simulateSoaRecord($domain) {
        return [
            'mname' => 'ns1.' . $domain,
            'rname' => 'admin.' . $domain,
            'serial' => time(),
            'refresh' => 3600,
            'retry' => 600,
            'expire' => 86400,
            'minimum' => 3600
        ];
    }

    public static function checkWhois($domain) {
        // Simulate WHOIS data
        $expiryDays = rand(30, 365);

        return [
            'domain' => $domain,
            'registrar' => self::getRandomRegistrar(),
            'registered_date' => date('Y-m-d', strtotime('-2 years')),
            'expiry_date' => date('Y-m-d', strtotime("+$expiryDays days")),
            'status' => 'active',
            'nameservers' => ['ns1.' . $domain, 'ns2.' . $domain]
        ];
    }

    private static function getRandomRegistrar() {
        $registrars = ['GoDaddy', 'Namecheap', 'Google Domains', 'Cloudflare', 'Name.com'];
        return $registrars[array_rand($registrars)];
    }

    public static function checkDnsChanges($domain, $previousRecords) {
        $currentRecords = self::scanDns($domain);

        $changes = [];

        foreach ($currentRecords['records'] as $type => $records) {
            if (!isset($previousRecords[$type])) {
                $changes[] = "New $type record added";
            } elseif ($previousRecords[$type] !== $records) {
                $changes[] = "$type record modified";
            }
        }

        return [
            'has_changes' => !empty($changes),
            'changes' => $changes,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
