<?php
/**
 * Domain Controller
 * Manages monitored domains
 */
class DomainController extends Controller {

    public function index() {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $domains = MonitoredDomain::all($tenantId);

        View::renderWithLayout('domains/index', [
            'title' => 'Monitored Domains',
            'user' => $this->user,
            'domains' => $domains,
            'success' => Redirect::getFlash('success'),
            'error' => Redirect::getFlash('error')
        ]);
    }

    public function show($id) {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $domain = MonitoredDomain::findById($id, $tenantId);
        if (!$domain) {
            http_response_code(404);
            View::renderWithLayout('errors/404', ['title' => '404 - Not Found', 'user' => $this->user]);
            return;
        }

        $scans = ScanResult::getByAsset('domain', $id, $tenantId);
        $vulnerabilities = Vulnerability::getByAsset('domain', $id, $tenantId);

        View::renderWithLayout('domains/show', [
            'title' => $domain['domain'],
            'user' => $this->user,
            'domain' => $domain,
            'scans' => $scans,
            'vulnerabilities' => $vulnerabilities,
            'csrf_token' => Security::generateCsrfToken()
        ]);
    }

    public function create() {
        $this->requireRole(['platform_admin', 'tenant_admin']);
        $tenantId = $this->getTenantId();

        // Check if can add more assets
        if (!Tenant::canAddAsset($tenantId)) {
            Redirect::withError('You have reached your plan limit for monitored assets');
            Redirect::to('/domains');
        }

        View::renderWithLayout('domains/create', [
            'title' => 'Add Domain',
            'user' => $this->user,
            'csrf_token' => Security::generateCsrfToken(),
            'error' => Redirect::getFlash('error')
        ]);
    }

    public function store() {
        $this->requireRole(['platform_admin', 'tenant_admin']);
        $this->validateCsrf();
        $tenantId = $this->getTenantId();

        // Check limits
        if (!Tenant::canAddAsset($tenantId)) {
            Redirect::withError('You have reached your plan limit for monitored assets');
            Redirect::to('/domains');
        }

        // Validate input
        $validator = new Validator($_POST);
        if (!$validator->validate(['domain' => 'required|domain'])) {
            Redirect::withError($validator->getFirstError());
            Redirect::to('/domains/create');
        }

        $domain = strtolower(trim($_POST['domain']));

        // Check if domain already exists
        $existing = MonitoredDomain::findByDomain($domain, $tenantId);
        if ($existing) {
            Redirect::withError('This domain is already being monitored');
            Redirect::to('/domains/create');
        }

        // Create domain
        $data = [
            'tenant_id' => $tenantId,
            'domain' => $domain,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $domainId = MonitoredDomain::create($data);

        // Log activity
        Logger::activity($tenantId, $this->user['id'], 'domain_added', 'domain', $domainId, "Added domain: $domain");

        Redirect::withSuccess('Domain added successfully');
        Redirect::to('/domains/' . $domainId);
    }

    public function scan($id) {
        $this->requireAuth();
        $this->validateCsrf();
        $tenantId = $this->getTenantId();

        $domain = MonitoredDomain::findById($id, $tenantId);
        if (!$domain) {
            $this->json(['status' => 'error', 'message' => 'Domain not found'], 404);
        }

        // Check scan limits
        if (!Tenant::canRunScan($tenantId)) {
            $this->json(['status' => 'error', 'message' => 'Monthly scan limit reached'], 403);
        }

        // Run scans
        $this->runDomainScans($domain, $tenantId);

        // Increment scan count
        Tenant::incrementScanCount($tenantId);

        Redirect::withSuccess('Domain scan completed successfully');
        Redirect::to('/domains/' . $id);
    }

    private function runDomainScans($domain, $tenantId) {
        $domainName = $domain['domain'];
        $domainId = $domain['id'];

        // DNS Scan
        $dnsResult = DomainScannerHelper::scanDns($domainName);
        MonitoredDomain::updateDnsRecords($domainId, $dnsResult['records'], $tenantId);

        // SSL/TLS Scan
        $tlsResult = TLSScannerHelper::scanTls($domainName);
        ScanResult::createScanResult($tenantId, 'domain', $domainId, 'ssl_tls', $tlsResult, $this->getSeverityFromScore($tlsResult['score']), $tlsResult['score']);
        MonitoredDomain::updateSslInfo($domainId, $tlsResult, $tenantId);

        // Email Security Scan
        $emailResult = EmailSecurityHelper::scanEmailSecurity($domainName);
        ScanResult::createScanResult($tenantId, 'domain', $domainId, 'email_security', $emailResult, $this->getSeverityFromScore($emailResult['score']), $emailResult['score']);

        // Vulnerability Scan
        $vulnResult = VulnerabilityScannerHelper::scanDomainVulnerabilities($domainName);
        ScanResult::createScanResult($tenantId, 'domain', $domainId, 'vulnerability_scan', $vulnResult, $this->getVulnerabilitySeverity($vulnResult), null);

        // Save vulnerabilities
        $scanResultId = Database::getInstance()->lastInsertId();
        foreach ($vulnResult['vulnerabilities'] as $vuln) {
            Vulnerability::create([
                'tenant_id' => $tenantId,
                'scan_result_id' => $scanResultId,
                'asset_type' => 'domain',
                'asset_id' => $domainId,
                'cve_id' => $vuln['cve_id'],
                'title' => $vuln['title'],
                'description' => $vuln['description'],
                'severity' => $vuln['severity'],
                'cvss_score' => $vuln['cvss_score'],
                'affected_component' => $vuln['affected_component'],
                'remediation' => $vuln['remediation'],
                'status' => 'open'
            ]);

            // Create alert for critical/high vulnerabilities
            if (in_array($vuln['severity'], ['critical', 'high'])) {
                Alert::createAlert($tenantId, 'domain', $domainId,
                    ucfirst($vuln['severity']) . ' Vulnerability: ' . $vuln['title'],
                    $vuln['description'],
                    $vuln['severity'],
                    'vulnerability'
                );
            }
        }

        // Calculate overall risk score
        $riskScore = RiskScoreHelper::calculateDomainRiskScore($domainId, $tenantId);
        if ($riskScore) {
            MonitoredDomain::updateRiskScore($domainId, $riskScore['score'], $riskScore['rating'], $tenantId);
        }

        // Log activity
        Logger::activity($tenantId, $this->user['id'], 'domain_scanned', 'domain', $domainId, "Scanned domain: {$domainName}");
    }

    private function getSeverityFromScore($score) {
        if ($score >= 80) return 'info';
        if ($score >= 60) return 'low';
        if ($score >= 40) return 'medium';
        if ($score >= 20) return 'high';
        return 'critical';
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

        $domain = MonitoredDomain::findById($id, $tenantId);
        if (!$domain) {
            $this->json(['status' => 'error', 'message' => 'Domain not found'], 404);
        }

        MonitoredDomain::delete($id, $tenantId);

        Logger::activity($tenantId, $this->user['id'], 'domain_deleted', 'domain', $id, "Deleted domain: {$domain['domain']}");

        Redirect::withSuccess('Domain deleted successfully');
        Redirect::to('/domains');
    }
}
