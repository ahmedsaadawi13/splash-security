<?php
/**
 * IP Controller
 * Manages monitored IP addresses
 */
class IpController extends Controller {

    public function index() {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $ips = MonitoredIp::all($tenantId);

        View::renderWithLayout('ips/index', [
            'title' => 'Monitored IPs',
            'user' => $this->user,
            'ips' => $ips,
            'success' => Redirect::getFlash('success'),
            'error' => Redirect::getFlash('error')
        ]);
    }

    public function show($id) {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $ip = MonitoredIp::findById($id, $tenantId);
        if (!$ip) {
            http_response_code(404);
            View::renderWithLayout('errors/404', ['title' => '404 - Not Found', 'user' => $this->user]);
            return;
        }

        $scans = ScanResult::getByAsset('ip', $id, $tenantId);
        $vulnerabilities = Vulnerability::getByAsset('ip', $id, $tenantId);

        View::renderWithLayout('ips/show', [
            'title' => $ip['ip_address'],
            'user' => $this->user,
            'ip' => $ip,
            'scans' => $scans,
            'vulnerabilities' => $vulnerabilities,
            'csrf_token' => Security::generateCsrfToken()
        ]);
    }

    public function create() {
        $this->requireRole(['platform_admin', 'tenant_admin']);
        $tenantId = $this->getTenantId();

        if (!Tenant::canAddAsset($tenantId)) {
            Redirect::withError('You have reached your plan limit for monitored assets');
            Redirect::to('/ips');
        }

        View::renderWithLayout('ips/create', [
            'title' => 'Add IP Address',
            'user' => $this->user,
            'csrf_token' => Security::generateCsrfToken(),
            'error' => Redirect::getFlash('error')
        ]);
    }

    public function store() {
        $this->requireRole(['platform_admin', 'tenant_admin']);
        $this->validateCsrf();
        $tenantId = $this->getTenantId();

        if (!Tenant::canAddAsset($tenantId)) {
            Redirect::withError('You have reached your plan limit for monitored assets');
            Redirect::to('/ips');
        }

        $validator = new Validator($_POST);
        if (!$validator->validate(['ip_address' => 'required|ip'])) {
            Redirect::withError($validator->getFirstError());
            Redirect::to('/ips/create');
        }

        $ipAddress = trim($_POST['ip_address']);
        $name = trim($_POST['name'] ?? $ipAddress);

        $existing = MonitoredIp::findByIp($ipAddress, $tenantId);
        if ($existing) {
            Redirect::withError('This IP address is already being monitored');
            Redirect::to('/ips/create');
        }

        $data = [
            'tenant_id' => $tenantId,
            'ip_address' => $ipAddress,
            'name' => $name,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $ipId = MonitoredIp::create($data);

        Logger::activity($tenantId, $this->user['id'], 'ip_added', 'ip', $ipId, "Added IP: $ipAddress");

        Redirect::withSuccess('IP address added successfully');
        Redirect::to('/ips/' . $ipId);
    }

    public function scan($id) {
        $this->requireAuth();
        $this->validateCsrf();
        $tenantId = $this->getTenantId();

        $ip = MonitoredIp::findById($id, $tenantId);
        if (!$ip) {
            $this->json(['status' => 'error', 'message' => 'IP not found'], 404);
        }

        if (!Tenant::canRunScan($tenantId)) {
            $this->json(['status' => 'error', 'message' => 'Monthly scan limit reached'], 403);
        }

        $this->runIpScans($ip, $tenantId);
        Tenant::incrementScanCount($tenantId);

        Redirect::withSuccess('IP scan completed successfully');
        Redirect::to('/ips/' . $id);
    }

    private function runIpScans($ip, $tenantId) {
        $ipAddress = $ip['ip_address'];
        $ipId = $ip['id'];

        // Port Scan
        $portResult = IPScannerHelper::scanPorts($ipAddress);
        ScanResult::createScanResult($tenantId, 'ip', $ipId, 'port_scan', $portResult, $this->getRiskSeverity($portResult['risk_level']), null);
        MonitoredIp::updateOpenPorts($ipId, $portResult['open_ports'], $tenantId);

        // Geolocation
        $geoResult = IPScannerHelper::getGeolocation($ipAddress);
        MonitoredIp::updateGeoInfo($ipId, $geoResult, $tenantId);

        // Blacklist Check
        $blacklistResult = IPScannerHelper::checkBlacklist($ipAddress);
        ScanResult::createScanResult($tenantId, 'ip', $ipId, 'blacklist_check', $blacklistResult, $blacklistResult['is_blacklisted'] ? 'high' : 'info', null);

        if ($blacklistResult['is_blacklisted']) {
            Alert::createAlert($tenantId, 'ip', $ipId,
                'IP Address Blacklisted',
                "IP $ipAddress found on " . count($blacklistResult['blacklists']) . ' blacklist(s)',
                'high',
                'blacklist'
            );
        }

        // Vulnerability Scan
        $vulnResult = VulnerabilityScannerHelper::scanIpVulnerabilities($ipAddress);
        $scanResultId = ScanResult::createScanResult($tenantId, 'ip', $ipId, 'vulnerability_scan', $vulnResult, $this->getVulnerabilitySeverity($vulnResult), null);

        foreach ($vulnResult['vulnerabilities'] as $vuln) {
            Vulnerability::create([
                'tenant_id' => $tenantId,
                'scan_result_id' => $scanResultId,
                'asset_type' => 'ip',
                'asset_id' => $ipId,
                'cve_id' => $vuln['cve_id'],
                'title' => $vuln['title'],
                'description' => $vuln['description'],
                'severity' => $vuln['severity'],
                'cvss_score' => $vuln['cvss_score'],
                'affected_component' => $vuln['affected_component'],
                'remediation' => $vuln['remediation'],
                'status' => 'open'
            ]);

            if (in_array($vuln['severity'], ['critical', 'high'])) {
                Alert::createAlert($tenantId, 'ip', $ipId,
                    ucfirst($vuln['severity']) . ' Vulnerability: ' . $vuln['title'],
                    $vuln['description'],
                    $vuln['severity'],
                    'vulnerability'
                );
            }
        }

        // Calculate risk score
        $riskScore = RiskScoreHelper::calculateIpRiskScore($ipId, $tenantId);
        if ($riskScore) {
            MonitoredIp::updateRiskScore($ipId, $riskScore['score'], $riskScore['rating'], $tenantId);
        }

        Logger::activity($tenantId, $this->user['id'], 'ip_scanned', 'ip', $ipId, "Scanned IP: $ipAddress");
    }

    private function getRiskSeverity($riskLevel) {
        $map = ['low' => 'info', 'medium' => 'medium', 'high' => 'high'];
        return $map[$riskLevel] ?? 'info';
    }

    private function getVulnerabilitySeverity($vulnResult) {
        if ($vulnResult['critical_count'] > 0) return 'critical';
        if ($vulnResult['high_count'] > 0) return 'high';
        if ($vulnResult['medium_count'] > 0) return 'medium';
        if ($vulnResult['low_count'] > 0) return 'low';
        return 'info';
    }

    public function delete($id) {
        $this->requireRole(['platform_admin', 'tenant_admin']);
        $this->validateCsrf();
        $tenantId = $this->getTenantId();

        $ip = MonitoredIp::findById($id, $tenantId);
        if (!$ip) {
            $this->json(['status' => 'error', 'message' => 'IP not found'], 404);
        }

        MonitoredIp::delete($id, $tenantId);

        Logger::activity($tenantId, $this->user['id'], 'ip_deleted', 'ip', $id, "Deleted IP: {$ip['ip_address']}");

        Redirect::withSuccess('IP address deleted successfully');
        Redirect::to('/ips');
    }
}
