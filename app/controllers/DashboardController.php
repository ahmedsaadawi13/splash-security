<?php
/**
 * Dashboard Controller
 */
class DashboardController extends Controller {

    public function index() {
        $this->requireAuth();

        $tenantId = $this->getTenantId();

        // Get statistics
        $domainStats = MonitoredDomain::getStats($tenantId);
        $ipStats = MonitoredIp::getStats($tenantId);
        $vulnStats = Vulnerability::getStats($tenantId);
        $alertStats = Alert::getStats($tenantId);

        // Get recent items
        $recentAlerts = Alert::getRecent($tenantId, 5);
        $recentScans = ScanResult::getRecent($tenantId, 5);
        $criticalVulns = Vulnerability::getCritical($tenantId);

        // Get subscription info
        $subscription = null;
        $usage = null;
        if ($tenantId) {
            $subscription = Tenant::getActiveSubscription($tenantId);
            $usage = Tenant::getCurrentUsage($tenantId);
        }

        View::renderWithLayout('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $this->user,
            'tenant' => $this->tenant,
            'domain_stats' => $domainStats,
            'ip_stats' => $ipStats,
            'vuln_stats' => $vulnStats,
            'alert_stats' => $alertStats,
            'recent_alerts' => $recentAlerts,
            'recent_scans' => $recentScans,
            'critical_vulns' => $criticalVulns,
            'subscription' => $subscription,
            'usage' => $usage,
            'success' => Redirect::getFlash('success'),
            'error' => Redirect::getFlash('error')
        ]);
    }
}
