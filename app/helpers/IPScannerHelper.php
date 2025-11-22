<?php
/**
 * IP Scanner Helper
 * Simulates IP address scanning (ports, services, geolocation)
 */
class IPScannerHelper {

    public static function scanPorts($ipAddress) {
        $openPorts = self::simulatePortScan($ipAddress);
        $services = self::identifyServices($openPorts);

        $riskLevel = self::assessPortRisk($openPorts);

        return [
            'ip_address' => $ipAddress,
            'open_ports' => $openPorts,
            'services' => $services,
            'total_open' => count($openPorts),
            'risk_level' => $riskLevel,
            'recommendations' => self::getPortRecommendations($openPorts),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private static function simulatePortScan($ipAddress) {
        $commonPorts = [
            22 => 'SSH',
            80 => 'HTTP',
            443 => 'HTTPS',
            21 => 'FTP',
            25 => 'SMTP',
            3306 => 'MySQL',
            5432 => 'PostgreSQL',
            3389 => 'RDP',
            8080 => 'HTTP-Alt',
            27017 => 'MongoDB'
        ];

        $openPorts = [];

        // Always open web ports
        $openPorts[] = ['port' => 80, 'protocol' => 'TCP', 'state' => 'open'];
        $openPorts[] = ['port' => 443, 'protocol' => 'TCP', 'state' => 'open'];

        // Randomly open other ports (30% chance each)
        foreach ([22, 25, 3306, 8080] as $port) {
            if (rand(0, 10) > 7) {
                $openPorts[] = ['port' => $port, 'protocol' => 'TCP', 'state' => 'open'];
            }
        }

        // Rarely open risky ports (10% chance)
        foreach ([21, 3389, 27017] as $port) {
            if (rand(0, 10) > 9) {
                $openPorts[] = ['port' => $port, 'protocol' => 'TCP', 'state' => 'open'];
            }
        }

        return $openPorts;
    }

    private static function identifyServices($openPorts) {
        $services = [];
        $serviceNames = [
            21 => 'FTP',
            22 => 'SSH',
            25 => 'SMTP',
            80 => 'HTTP',
            443 => 'HTTPS',
            3306 => 'MySQL',
            5432 => 'PostgreSQL',
            3389 => 'RDP',
            8080 => 'HTTP-Alt',
            27017 => 'MongoDB'
        ];

        foreach ($openPorts as $portInfo) {
            $port = $portInfo['port'];
            $serviceName = $serviceNames[$port] ?? 'Unknown';

            $services[] = [
                'port' => $port,
                'service' => $serviceName,
                'version' => self::simulateVersion($serviceName),
                'risk' => self::getServiceRisk($port)
            ];
        }

        return $services;
    }

    private static function simulateVersion($serviceName) {
        $versions = [
            'SSH' => 'OpenSSH ' . rand(7, 9) . '.' . rand(0, 9),
            'HTTP' => 'nginx/' . rand(1, 2) . '.' . rand(10, 20),
            'HTTPS' => 'nginx/' . rand(1, 2) . '.' . rand(10, 20),
            'MySQL' => 'MySQL ' . rand(5, 8) . '.' . rand(0, 5),
            'PostgreSQL' => 'PostgreSQL ' . rand(12, 15) . '.' . rand(0, 5),
            'FTP' => 'vsftpd ' . rand(2, 3) . '.' . rand(0, 5),
            'SMTP' => 'Postfix',
            'RDP' => 'Microsoft Terminal Services',
            'MongoDB' => 'MongoDB ' . rand(4, 6) . '.' . rand(0, 5)
        ];

        return $versions[$serviceName] ?? 'Unknown';
    }

    private static function getServiceRisk($port) {
        $highRiskPorts = [21, 23, 3389, 27017, 6379];
        $mediumRiskPorts = [22, 3306, 5432];

        if (in_array($port, $highRiskPorts)) {
            return 'high';
        } elseif (in_array($port, $mediumRiskPorts)) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    private static function assessPortRisk($openPorts) {
        $riskScore = 0;

        foreach ($openPorts as $portInfo) {
            $port = $portInfo['port'];

            if (in_array($port, [21, 23, 3389])) {
                $riskScore += 30;
            } elseif (in_array($port, [22, 3306, 5432, 27017])) {
                $riskScore += 15;
            } else {
                $riskScore += 5;
            }
        }

        if ($riskScore >= 50) return 'high';
        if ($riskScore >= 25) return 'medium';
        return 'low';
    }

    private static function getPortRecommendations($openPorts) {
        $recommendations = [];
        $ports = array_column($openPorts, 'port');

        if (in_array(21, $ports)) {
            $recommendations[] = 'FTP port 21 is open - consider using SFTP instead';
        }

        if (in_array(3389, $ports)) {
            $recommendations[] = 'RDP port 3389 is exposed - restrict access or use VPN';
        }

        if (in_array(22, $ports)) {
            $recommendations[] = 'SSH port 22 is open - ensure key-based auth and disable root login';
        }

        if (in_array(3306, $ports) || in_array(5432, $ports) || in_array(27017, $ports)) {
            $recommendations[] = 'Database port is exposed - restrict to internal network only';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Port configuration appears secure';
        }

        return $recommendations;
    }

    public static function getGeolocation($ipAddress) {
        // Simulate geolocation lookup
        $countries = ['United States', 'United Kingdom', 'Germany', 'France', 'Japan', 'Canada', 'Australia'];
        $cities = ['New York', 'London', 'Berlin', 'Paris', 'Tokyo', 'Toronto', 'Sydney'];

        $index = crc32($ipAddress) % count($countries);

        return [
            'ip_address' => $ipAddress,
            'country' => $countries[$index],
            'city' => $cities[$index],
            'latitude' => rand(-90, 90) + rand(0, 100) / 100,
            'longitude' => rand(-180, 180) + rand(0, 100) / 100,
            'isp' => self::getRandomIsp(),
            'asn' => 'AS' . rand(10000, 99999),
            'timezone' => 'UTC' . (rand(0, 1) ? '+' : '-') . rand(0, 12)
        ];
    }

    private static function getRandomIsp() {
        $isps = ['Amazon AWS', 'Google Cloud', 'Microsoft Azure', 'DigitalOcean', 'Cloudflare', 'OVH', 'Linode'];
        return $isps[array_rand($isps)];
    }

    public static function checkBlacklist($ipAddress) {
        // Simulate blacklist check
        $isBlacklisted = rand(0, 10) > 9; // 10% chance

        $blacklists = [];
        if ($isBlacklisted) {
            $possibleLists = ['Spamhaus', 'SpamCop', 'SORBS', 'Barracuda'];
            $numLists = rand(1, 2);
            for ($i = 0; $i < $numLists; $i++) {
                $blacklists[] = $possibleLists[array_rand($possibleLists)];
            }
        }

        return [
            'ip_address' => $ipAddress,
            'is_blacklisted' => $isBlacklisted,
            'blacklists' => $blacklists,
            'checked_lists' => ['Spamhaus', 'SpamCop', 'SORBS', 'Barracuda', 'UCEPROTECT'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
