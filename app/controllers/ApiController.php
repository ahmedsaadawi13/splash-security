<?php
/**
 * API Controller
 * REST API endpoints with API key authentication
 */
class ApiController extends Controller {

    private $apiUser = null;
    private $apiTenantId = null;

    public function __construct() {
        // Skip parent constructor for API
        $this->authenticateApi();
    }

    private function authenticateApi() {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

        if (empty($apiKey)) {
            $this->jsonError('API key required', 401);
        }

        $keyData = ApiKey::findByKey($apiKey);

        if (!$keyData) {
            $this->jsonError('Invalid API key', 403);
        }

        // Check rate limit
        if (!$this->checkRateLimit($keyData['tenant_id'])) {
            $this->jsonError('Rate limit exceeded', 429);
        }

        $this->apiUser = $keyData;
        $this->apiTenantId = $keyData['tenant_id'];

        // Update last used
        ApiKey::updateLastUsed($keyData['id']);

        // Increment API call count
        Tenant::incrementApiCallCount($keyData['tenant_id']);
    }

    private function checkRateLimit($tenantId) {
        $subscription = Tenant::getActiveSubscription($tenantId);
        if (!$subscription) {
            return false;
        }

        $usage = Tenant::getCurrentUsage($tenantId);
        $limit = $subscription['api_rate_limit'] ?? 100;

        // Simple hourly rate limit
        $key = 'api_rate_' . $tenantId;
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 0,
                'reset_at' => time() + 3600
            ];
        }

        $rateData = $_SESSION[$key];

        if (time() > $rateData['reset_at']) {
            $_SESSION[$key] = [
                'count' => 0,
                'reset_at' => time() + 3600
            ];
            $rateData = $_SESSION[$key];
        }

        if ($rateData['count'] >= $limit) {
            return false;
        }

        $_SESSION[$key]['count']++;
        return true;
    }

    private function jsonError($message, $code = 400) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'code' => $code
        ]);
        exit;
    }

    private function jsonSuccess($data) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
        exit;
    }

    public function assets() {
        $domains = MonitoredDomain::all($this->apiTenantId);
        $ips = MonitoredIp::all($this->apiTenantId);

        $this->jsonSuccess([
            'domains' => $domains,
            'ips' => $ips,
            'total' => count($domains) + count($ips)
        ]);
    }

    public function scanDomain($id) {
        $domain = MonitoredDomain::findById($id, $this->apiTenantId);
        if (!$domain) {
            $this->jsonError('Domain not found', 404);
        }

        if (!Tenant::canRunScan($this->apiTenantId)) {
            $this->jsonError('Monthly scan limit reached', 403);
        }

        // Run scan (simplified for API)
        $domainName = $domain['domain'];

        $tlsResult = TLSScannerHelper::scanTls($domainName);
        ScanResult::createScanResult($this->apiTenantId, 'domain', $id, 'ssl_tls', $tlsResult, 'info', $tlsResult['score']);

        Tenant::incrementScanCount($this->apiTenantId);

        $this->jsonSuccess([
            'message' => 'Scan initiated',
            'domain_id' => $id,
            'domain' => $domainName
        ]);
    }

    public function scanIp($id) {
        $ip = MonitoredIp::findById($id, $this->apiTenantId);
        if (!$ip) {
            $this->jsonError('IP not found', 404);
        }

        if (!Tenant::canRunScan($this->apiTenantId)) {
            $this->jsonError('Monthly scan limit reached', 403);
        }

        $ipAddress = $ip['ip_address'];
        $portResult = IPScannerHelper::scanPorts($ipAddress);
        ScanResult::createScanResult($this->apiTenantId, 'ip', $id, 'port_scan', $portResult, 'info', null);

        Tenant::incrementScanCount($this->apiTenantId);

        $this->jsonSuccess([
            'message' => 'Scan initiated',
            'ip_id' => $id,
            'ip_address' => $ipAddress
        ]);
    }

    public function getScanResult($id) {
        $scan = ScanResult::findById($id, $this->apiTenantId);
        if (!$scan) {
            $this->jsonError('Scan not found', 404);
        }

        $scan['result'] = json_decode($scan['result_json'], true);
        unset($scan['result_json']);

        $this->jsonSuccess($scan);
    }

    public function getAssetScore($id) {
        // Try domain first
        $domain = MonitoredDomain::findById($id, $this->apiTenantId);
        if ($domain) {
            $this->jsonSuccess([
                'asset_type' => 'domain',
                'asset_id' => $id,
                'asset_name' => $domain['domain'],
                'risk_score' => $domain['risk_score'],
                'risk_rating' => $domain['risk_rating']
            ]);
        }

        // Try IP
        $ip = MonitoredIp::findById($id, $this->apiTenantId);
        if ($ip) {
            $this->jsonSuccess([
                'asset_type' => 'ip',
                'asset_id' => $id,
                'asset_name' => $ip['ip_address'],
                'risk_score' => $ip['risk_score'],
                'risk_rating' => $ip['risk_rating']
            ]);
        }

        $this->jsonError('Asset not found', 404);
    }

    public function alerts() {
        $alerts = Alert::getRecent($this->apiTenantId, 50);

        $this->jsonSuccess([
            'alerts' => $alerts,
            'total' => count($alerts)
        ]);
    }

    public function vulnerabilities() {
        $vulnerabilities = Vulnerability::getOpen($this->apiTenantId);
        $stats = Vulnerability::getStats($this->apiTenantId);

        $this->jsonSuccess([
            'vulnerabilities' => $vulnerabilities,
            'stats' => $stats
        ]);
    }
}
