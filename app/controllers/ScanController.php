<?php
/**
 * Scan Controller
 */
class ScanController extends Controller {

    public function index() {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $scans = ScanResult::getRecent($tenantId, 50);

        View::renderWithLayout('scans/index', [
            'title' => 'Scan History',
            'user' => $this->user,
            'scans' => $scans
        ]);
    }

    public function show($id) {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $scan = ScanResult::findById($id, $tenantId);
        if (!$scan) {
            http_response_code(404);
            View::renderWithLayout('errors/404', ['title' => '404 - Not Found', 'user' => $this->user]);
            return;
        }

        $result = json_decode($scan['result_json'], true);

        View::renderWithLayout('scans/show', [
            'title' => 'Scan Details',
            'user' => $this->user,
            'scan' => $scan,
            'result' => $result
        ]);
    }
}
