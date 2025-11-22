<?php
/**
 * Admin Controller
 * For platform administrators
 */
class AdminController extends Controller {

    public function index() {
        $this->requirePlatformAdmin();

        $stats = [
            'total_tenants' => Tenant::count(),
            'total_users' => User::count(),
            'total_domains' => MonitoredDomain::count(),
            'total_ips' => MonitoredIp::count()
        ];

        View::renderWithLayout('admin/index', [
            'title' => 'Platform Administration',
            'user' => $this->user,
            'stats' => $stats
        ]);
    }

    public function tenants() {
        $this->requirePlatformAdmin();

        $tenants = Tenant::all();

        View::renderWithLayout('admin/tenants', [
            'title' => 'Manage Tenants',
            'user' => $this->user,
            'tenants' => $tenants
        ]);
    }

    public function showTenant($id) {
        $this->requirePlatformAdmin();

        $tenant = Tenant::findById($id);
        if (!$tenant) {
            http_response_code(404);
            View::renderWithLayout('errors/404', ['title' => '404 - Not Found', 'user' => $this->user]);
            return;
        }

        $users = User::getByTenant($id);
        $subscription = Tenant::getActiveSubscription($id);
        $usage = Tenant::getCurrentUsage($id);
        $domains = MonitoredDomain::all($id);
        $ips = MonitoredIp::all($id);

        View::renderWithLayout('admin/tenant_details', [
            'title' => 'Tenant: ' . $tenant['name'],
            'user' => $this->user,
            'tenant' => $tenant,
            'users' => $users,
            'subscription' => $subscription,
            'usage' => $usage,
            'domains' => $domains,
            'ips' => $ips
        ]);
    }

    public function users() {
        $this->requirePlatformAdmin();

        $users = User::all();

        View::renderWithLayout('admin/users', [
            'title' => 'Manage Users',
            'user' => $this->user,
            'users' => $users
        ]);
    }

    public function plans() {
        $this->requirePlatformAdmin();

        $plans = Plan::getActive();

        View::renderWithLayout('admin/plans', [
            'title' => 'Subscription Plans',
            'user' => $this->user,
            'plans' => $plans
        ]);
    }
}
