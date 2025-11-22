<?php
/**
 * Base Controller Class
 * All controllers extend this class
 */
class Controller {
    protected $user = null;
    protected $tenant = null;

    public function __construct() {
        // Load user session if authenticated
        if (isset($_SESSION['user_id'])) {
            $this->user = User::findById($_SESSION['user_id']);

            if ($this->user && $this->user['tenant_id']) {
                $this->tenant = Tenant::findById($this->user['tenant_id']);
            }
        }
    }

    protected function requireAuth() {
        if (!$this->user) {
            Redirect::to('/login');
            exit;
        }
    }

    protected function requireRole($roles) {
        $this->requireAuth();

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!in_array($this->user['role'], $roles)) {
            http_response_code(403);
            View::render('errors/403', ['title' => '403 - Forbidden']);
            exit;
        }
    }

    protected function requirePlatformAdmin() {
        $this->requireRole('platform_admin');
    }

    protected function requireTenantAdmin() {
        $this->requireRole(['platform_admin', 'tenant_admin']);
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function validateCsrf() {
        $token = $_POST[CSRF_TOKEN_NAME] ?? '';

        if (!Security::validateCsrfToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }

    protected function getTenantId() {
        if ($this->user['role'] === 'platform_admin') {
            // Platform admin can override tenant context
            return $_SESSION['viewing_tenant_id'] ?? $this->user['tenant_id'];
        }

        return $this->user['tenant_id'];
    }
}
