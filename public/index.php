<?php
/**
 * SplashSecurity Entry Point
 * All requests are routed through this file
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Autoloader for classes
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/core/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
        APP_PATH . '/helpers/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Initialize router
$router = new Router();

// ============================================================
// PUBLIC ROUTES
// ============================================================
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// ============================================================
// DASHBOARD ROUTES
// ============================================================
$router->get('/dashboard', 'DashboardController@index');

// ============================================================
// DOMAIN ROUTES
// ============================================================
$router->get('/domains', 'DomainController@index');
$router->get('/domains/create', 'DomainController@create');
$router->post('/domains/create', 'DomainController@store');
$router->get('/domains/{id}', 'DomainController@show');
$router->get('/domains/{id}/edit', 'DomainController@edit');
$router->post('/domains/{id}/edit', 'DomainController@update');
$router->post('/domains/{id}/delete', 'DomainController@delete');
$router->post('/domains/{id}/scan', 'DomainController@scan');

// ============================================================
// IP ROUTES
// ============================================================
$router->get('/ips', 'IpController@index');
$router->get('/ips/create', 'IpController@create');
$router->post('/ips/create', 'IpController@store');
$router->get('/ips/{id}', 'IpController@show');
$router->get('/ips/{id}/edit', 'IpController@edit');
$router->post('/ips/{id}/edit', 'IpController@update');
$router->post('/ips/{id}/delete', 'IpController@delete');
$router->post('/ips/{id}/scan', 'IpController@scan');

// ============================================================
// SCAN ROUTES
// ============================================================
$router->get('/scans', 'ScanController@index');
$router->get('/scans/{id}', 'ScanController@show');

// ============================================================
// VULNERABILITY ROUTES
// ============================================================
$router->get('/vulnerabilities', 'VulnerabilityController@index');
$router->get('/vulnerabilities/{id}', 'VulnerabilityController@show');
$router->post('/vulnerabilities/{id}/status', 'VulnerabilityController@updateStatus');

// ============================================================
// ALERT ROUTES
// ============================================================
$router->get('/alerts', 'AlertController@index');
$router->get('/alerts/{id}', 'AlertController@show');
$router->post('/alerts/{id}/acknowledge', 'AlertController@acknowledge');
$router->post('/alerts/{id}/close', 'AlertController@close');

// ============================================================
// REPORT ROUTES
// ============================================================
$router->get('/reports', 'ReportController@index');
$router->get('/reports/generate', 'ReportController@generate');
$router->post('/reports/generate', 'ReportController@create');
$router->get('/reports/{id}', 'ReportController@show');

// ============================================================
// SETTINGS & PROFILE
// ============================================================
$router->get('/settings', 'SettingsController@index');
$router->post('/settings', 'SettingsController@update');
$router->get('/profile', 'ProfileController@index');
$router->post('/profile', 'ProfileController@update');

// ============================================================
// ADMIN ROUTES
// ============================================================
$router->get('/admin', 'AdminController@index');
$router->get('/admin/tenants', 'AdminController@tenants');
$router->get('/admin/tenants/{id}', 'AdminController@showTenant');
$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/plans', 'AdminController@plans');

// ============================================================
// API ROUTES
// ============================================================
$router->get('/api/assets', 'ApiController@assets');
$router->post('/api/scan/domain/{id}', 'ApiController@scanDomain');
$router->post('/api/scan/ip/{id}', 'ApiController@scanIp');
$router->get('/api/scan/{id}', 'ApiController@getScanResult');
$router->get('/api/asset/{id}/score', 'ApiController@getAssetScore');
$router->get('/api/alerts', 'ApiController@alerts');
$router->get('/api/vulnerabilities', 'ApiController@vulnerabilities');

// Dispatch router
try {
    $router->dispatch();
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo "<h1>Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "An error occurred. Please try again later.";
    }
    Logger::error('Application error: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
