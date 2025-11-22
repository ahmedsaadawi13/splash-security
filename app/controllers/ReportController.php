<?php
/**
 * Report Controller
 */
class ReportController extends Controller {

    public function index() {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $reports = Report::getByTenant($tenantId);

        View::renderWithLayout('reports/index', [
            'title' => 'Reports',
            'user' => $this->user,
            'reports' => $reports,
            'success' => Redirect::getFlash('success')
        ]);
    }

    public function generate() {
        $this->requireAuth();

        View::renderWithLayout('reports/generate', [
            'title' => 'Generate Report',
            'user' => $this->user,
            'csrf_token' => Security::generateCsrfToken()
        ]);
    }

    public function create() {
        $this->requireAuth();
        $this->validateCsrf();
        $tenantId = $this->getTenantId();

        $reportType = $_POST['report_type'] ?? 'summary';
        $format = $_POST['format'] ?? 'html';

        $title = $this->getReportTitle($reportType);

        $reportId = Report::createReport($tenantId, $this->user['id'], $reportType, $title, $format);

        Logger::activity($tenantId, $this->user['id'], 'report_generated', 'report', $reportId, "Generated $reportType report");

        Redirect::withSuccess('Report generated successfully');
        Redirect::to('/reports/' . $reportId);
    }

    public function show($id) {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $report = Report::findById($id, $tenantId);
        if (!$report) {
            http_response_code(404);
            View::renderWithLayout('errors/404', ['title' => '404 - Not Found', 'user' => $this->user]);
            return;
        }

        $reportData = $this->generateReportData($report['report_type'], $tenantId);

        View::renderWithLayout('reports/show', [
            'title' => $report['title'],
            'user' => $this->user,
            'report' => $report,
            'data' => $reportData
        ]);
    }

    private function getReportTitle($reportType) {
        $titles = [
            'summary' => 'Security Summary Report',
            'vulnerabilities' => 'Vulnerabilities Report',
            'domains' => 'Domain Security Report',
            'ips' => 'IP Security Report',
            'weekly' => 'Weekly Security Report'
        ];

        return $titles[$reportType] ?? 'Security Report';
    }

    private function generateReportData($reportType, $tenantId) {
        $data = [
            'generated_at' => date('Y-m-d H:i:s'),
            'report_type' => $reportType
        ];

        switch ($reportType) {
            case 'summary':
                $data['domains'] = MonitoredDomain::all($tenantId);
                $data['ips'] = MonitoredIp::all($tenantId);
                $data['domain_stats'] = MonitoredDomain::getStats($tenantId);
                $data['ip_stats'] = MonitoredIp::getStats($tenantId);
                $data['vuln_stats'] = Vulnerability::getStats($tenantId);
                $data['alert_stats'] = Alert::getStats($tenantId);
                break;

            case 'vulnerabilities':
                $data['vulnerabilities'] = Vulnerability::getOpen($tenantId);
                $data['stats'] = Vulnerability::getStats($tenantId);
                break;

            case 'domains':
                $data['domains'] = MonitoredDomain::all($tenantId);
                $data['stats'] = MonitoredDomain::getStats($tenantId);
                break;

            case 'ips':
                $data['ips'] = MonitoredIp::all($tenantId);
                $data['stats'] = MonitoredIp::getStats($tenantId);
                break;
        }

        return $data;
    }
}
