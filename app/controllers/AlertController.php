<?php
/**
 * Alert Controller
 */
class AlertController extends Controller {

    public function index() {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $alerts = Alert::getOpen($tenantId);
        $stats = Alert::getStats($tenantId);

        View::renderWithLayout('alerts/index', [
            'title' => 'Alerts',
            'user' => $this->user,
            'alerts' => $alerts,
            'stats' => $stats,
            'success' => Redirect::getFlash('success')
        ]);
    }

    public function show($id) {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $alert = Alert::findById($id, $tenantId);
        if (!$alert) {
            http_response_code(404);
            View::renderWithLayout('errors/404', ['title' => '404 - Not Found', 'user' => $this->user]);
            return;
        }

        // Get asset info
        $asset = null;
        if ($alert['asset_type'] === 'domain') {
            $asset = MonitoredDomain::findById($alert['asset_id'], $tenantId);
        } elseif ($alert['asset_type'] === 'ip') {
            $asset = MonitoredIp::findById($alert['asset_id'], $tenantId);
        }

        View::renderWithLayout('alerts/show', [
            'title' => 'Alert Details',
            'user' => $this->user,
            'alert' => $alert,
            'asset' => $asset,
            'csrf_token' => Security::generateCsrfToken()
        ]);
    }

    public function acknowledge($id) {
        $this->requireRole(['platform_admin', 'tenant_admin', 'security_analyst']);
        $this->validateCsrf();
        $tenantId = $this->getTenantId();

        $alert = Alert::findById($id, $tenantId);
        if (!$alert) {
            $this->json(['status' => 'error', 'message' => 'Alert not found'], 404);
        }

        Alert::acknowledge($id, $this->user['id'], $tenantId);

        Logger::activity($tenantId, $this->user['id'], 'alert_acknowledged', 'alert', $id, "Acknowledged alert: {$alert['title']}");

        Redirect::withSuccess('Alert acknowledged');
        Redirect::to('/alerts/' . $id);
    }

    public function close($id) {
        $this->requireRole(['platform_admin', 'tenant_admin', 'security_analyst']);
        $this->validateCsrf();
        $tenantId = $this->getTenantId();

        $alert = Alert::findById($id, $tenantId);
        if (!$alert) {
            $this->json(['status' => 'error', 'message' => 'Alert not found'], 404);
        }

        Alert::close($id, $this->user['id'], $tenantId);

        Logger::activity($tenantId, $this->user['id'], 'alert_closed', 'alert', $id, "Closed alert: {$alert['title']}");

        Redirect::withSuccess('Alert closed');
        Redirect::to('/alerts');
    }
}
