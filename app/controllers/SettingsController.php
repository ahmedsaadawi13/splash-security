<?php
/**
 * Settings Controller
 */
class SettingsController extends Controller {

    public function index() {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $subscription = null;
        $usage = null;
        $apiKeys = [];

        if ($tenantId) {
            $subscription = Tenant::getActiveSubscription($tenantId);
            $usage = Tenant::getCurrentUsage($tenantId);
            $apiKeys = ApiKey::getByTenant($tenantId);
        }

        View::renderWithLayout('settings/index', [
            'title' => 'Settings',
            'user' => $this->user,
            'tenant' => $this->tenant,
            'subscription' => $subscription,
            'usage' => $usage,
            'api_keys' => $apiKeys,
            'csrf_token' => Security::generateCsrfToken(),
            'success' => Redirect::getFlash('success'),
            'error' => Redirect::getFlash('error')
        ]);
    }

    public function update() {
        $this->requireTenantAdmin();
        $this->validateCsrf();
        $tenantId = $this->getTenantId();

        // Update tenant settings
        $timezone = $_POST['timezone'] ?? 'UTC';

        Tenant::update($tenantId, ['timezone' => $timezone]);

        Logger::activity($tenantId, $this->user['id'], 'settings_updated', null, null, 'Updated tenant settings');

        Redirect::withSuccess('Settings updated successfully');
        Redirect::to('/settings');
    }
}
