-- SplashSecurity Database Schema
-- Multi-tenant Cybersecurity Monitoring & Risk Scoring SaaS
-- Compatible with MySQL 5.7+ and MariaDB 10.2+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS splash_security CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE splash_security;

-- ============================================================
-- MULTI-TENANT CORE TABLES
-- ============================================================

-- Tenants (Organizations)
CREATE TABLE tenants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    timezone VARCHAR(50) DEFAULT 'UTC',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscription Plans
CREATE TABLE plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    billing_period ENUM('monthly', 'yearly') DEFAULT 'monthly',
    max_monitored_assets INT DEFAULT 10,
    max_monthly_scans INT DEFAULT 100,
    max_alerts INT DEFAULT 50,
    api_rate_limit INT DEFAULT 100,
    features_json TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tenant Subscriptions
CREATE TABLE tenant_subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    status ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
    starts_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_plan_id (plan_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tenant Usage Tracking
CREATE TABLE tenant_usage (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    monitored_assets_count INT DEFAULT 0,
    scans_count INT DEFAULT 0,
    alerts_count INT DEFAULT 0,
    api_calls_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_period (tenant_id, period_start),
    INDEX idx_tenant_id (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('platform_admin', 'tenant_admin', 'security_analyst', 'read_only') DEFAULT 'read_only',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    timezone VARCHAR(50) DEFAULT 'UTC',
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Keys
CREATE TABLE api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    key_hash VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    status ENUM('active', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_key_hash (key_hash),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- MONITORED ASSETS TABLES
-- ============================================================

-- Monitored Domains
CREATE TABLE monitored_domains (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    domain VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'paused') DEFAULT 'active',
    risk_score INT DEFAULT NULL,
    risk_rating VARCHAR(10) DEFAULT NULL,
    registrar VARCHAR(255) DEFAULT NULL,
    expires_at TIMESTAMP NULL,
    dns_records_json TEXT,
    ssl_info_json TEXT,
    last_scanned_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_domain (domain),
    INDEX idx_status (status),
    INDEX idx_risk_score (risk_score),
    UNIQUE KEY unique_tenant_domain (tenant_id, domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Monitored IPs
CREATE TABLE monitored_ips (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive', 'paused') DEFAULT 'active',
    risk_score INT DEFAULT NULL,
    risk_rating VARCHAR(10) DEFAULT NULL,
    geo_info_json TEXT,
    open_ports_json TEXT,
    service_fingerprint_json TEXT,
    last_scanned_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_ip_address (ip_address),
    INDEX idx_status (status),
    INDEX idx_risk_score (risk_score),
    UNIQUE KEY unique_tenant_ip (tenant_id, ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SCANNING & RESULTS TABLES
-- ============================================================

-- Scan Results
CREATE TABLE scan_results (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    asset_type ENUM('domain', 'ip') NOT NULL,
    asset_id INT UNSIGNED NOT NULL,
    scan_type VARCHAR(50) NOT NULL,
    result_json LONGTEXT,
    severity ENUM('critical', 'high', 'medium', 'low', 'info') DEFAULT 'info',
    score INT DEFAULT NULL,
    status ENUM('completed', 'failed', 'in_progress') DEFAULT 'completed',
    error_message TEXT,
    scan_duration INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_asset (asset_type, asset_id),
    INDEX idx_scan_type (scan_type),
    INDEX idx_severity (severity),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vulnerabilities
CREATE TABLE vulnerabilities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    scan_result_id INT UNSIGNED NULL,
    asset_type ENUM('domain', 'ip') NOT NULL,
    asset_id INT UNSIGNED NOT NULL,
    cve_id VARCHAR(50) DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    severity ENUM('critical', 'high', 'medium', 'low', 'info') NOT NULL,
    cvss_score DECIMAL(3, 1) DEFAULT NULL,
    affected_component VARCHAR(255) DEFAULT NULL,
    remediation TEXT,
    status ENUM('open', 'acknowledged', 'fixed', 'false_positive') DEFAULT 'open',
    discovered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (scan_result_id) REFERENCES scan_results(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_asset (asset_type, asset_id),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_cve_id (cve_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ALERTS & NOTIFICATIONS TABLES
-- ============================================================

-- Alerts
CREATE TABLE alerts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    asset_type ENUM('domain', 'ip', 'system') NOT NULL,
    asset_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    severity ENUM('critical', 'high', 'medium', 'low', 'info') NOT NULL,
    category VARCHAR(50) DEFAULT NULL,
    status ENUM('open', 'acknowledged', 'closed') DEFAULT 'open',
    assigned_to INT UNSIGNED NULL,
    acknowledged_at TIMESTAMP NULL,
    acknowledged_by INT UNSIGNED NULL,
    closed_at TIMESTAMP NULL,
    closed_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_asset (asset_type, asset_id),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- REPORTS & ACTIVITY TABLES
-- ============================================================

-- Reports
CREATE TABLE reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    report_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    format ENUM('pdf', 'html', 'csv') DEFAULT 'html',
    file_path VARCHAR(500) DEFAULT NULL,
    status ENUM('generating', 'completed', 'failed') DEFAULT 'completed',
    parameters_json TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_report_type (report_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs
CREATE TABLE activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NULL,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    entity_id INT UNSIGNED DEFAULT NULL,
    description TEXT,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Insert default plans
INSERT INTO plans (name, slug, price, billing_period, max_monitored_assets, max_monthly_scans, max_alerts, api_rate_limit, features_json, status) VALUES
('Free', 'free', 0.00, 'monthly', 5, 50, 10, 50, '{"darkweb_scan": false, "email_security": true, "tls_scan": true, "continuous_monitoring": false}', 'active'),
('Starter', 'starter', 49.00, 'monthly', 25, 500, 50, 200, '{"darkweb_scan": true, "email_security": true, "tls_scan": true, "continuous_monitoring": false}', 'active'),
('Professional', 'professional', 149.00, 'monthly', 100, 2000, 200, 1000, '{"darkweb_scan": true, "email_security": true, "tls_scan": true, "continuous_monitoring": true}', 'active'),
('Enterprise', 'enterprise', 499.00, 'monthly', 1000, 10000, 1000, 5000, '{"darkweb_scan": true, "email_security": true, "tls_scan": true, "continuous_monitoring": true}', 'active');

-- Insert platform admin user (password: admin123)
INSERT INTO users (tenant_id, email, password, first_name, last_name, role, status, timezone) VALUES
(NULL, 'admin@splashsecurity.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LwIgdCExo6VaqNyl.', 'Platform', 'Admin', 'platform_admin', 'active', 'UTC');

-- Insert demo tenant
INSERT INTO tenants (name, slug, status, timezone) VALUES
('Demo Corporation', 'demo-corp', 'active', 'America/New_York');

-- Insert tenant subscription (Demo on Professional plan)
INSERT INTO tenant_subscriptions (tenant_id, plan_id, status, starts_at, expires_at) VALUES
(1, 3, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR));

-- Insert demo tenant admin (password: demo123)
INSERT INTO users (tenant_id, email, password, first_name, last_name, role, status, timezone) VALUES
(1, 'admin@democorp.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LwIgdCExo6VaqNyl.', 'John', 'Smith', 'tenant_admin', 'active', 'America/New_York'),
(1, 'analyst@democorp.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LwIgdCExo6VaqNyl.', 'Sarah', 'Johnson', 'security_analyst', 'active', 'America/New_York'),
(1, 'viewer@democorp.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LwIgdCExo6VaqNyl.', 'Mike', 'Williams', 'read_only', 'active', 'America/New_York');

-- Insert demo API key for tenant (key: sk_demo_12345...)
INSERT INTO api_keys (tenant_id, user_id, key_hash, name, status, expires_at) VALUES
(1, 2, '$2y$12$demo_api_key_hash_placeholder', 'Demo API Key', 'active', DATE_ADD(NOW(), INTERVAL 1 YEAR));

-- Insert sample monitored domains
INSERT INTO monitored_domains (tenant_id, domain, status, risk_score, risk_rating, registrar, expires_at, last_scanned_at) VALUES
(1, 'democorp.com', 'active', 85, 'A', 'GoDaddy', DATE_ADD(NOW(), INTERVAL 365 DAY), NOW()),
(1, 'staging.democorp.com', 'active', 72, 'B', 'GoDaddy', DATE_ADD(NOW(), INTERVAL 365 DAY), NOW());

-- Insert sample monitored IPs
INSERT INTO monitored_ips (tenant_id, ip_address, name, status, risk_score, risk_rating, last_scanned_at) VALUES
(1, '192.0.2.100', 'Web Server Primary', 'active', 78, 'B', NOW());

-- Insert sample scan results
INSERT INTO scan_results (tenant_id, asset_type, asset_id, scan_type, severity, score, status, created_at) VALUES
(1, 'domain', 1, 'ssl_tls', 'info', 90, 'completed', NOW()),
(1, 'domain', 1, 'email_security', 'medium', 75, 'completed', NOW()),
(1, 'domain', 1, 'vulnerability_scan', 'high', 65, 'completed', NOW()),
(1, 'ip', 1, 'port_scan', 'medium', 70, 'completed', NOW());

-- Insert sample vulnerabilities
INSERT INTO vulnerabilities (tenant_id, scan_result_id, asset_type, asset_id, cve_id, title, description, severity, cvss_score, affected_component, status, discovered_at) VALUES
(1, 3, 'domain', 1, 'CVE-2024-1234', 'Outdated SSL/TLS Configuration', 'Server supports TLS 1.0 which is deprecated and vulnerable', 'high', 7.5, 'SSL/TLS Configuration', 'open', NOW()),
(1, 3, 'domain', 1, 'CVE-2024-5678', 'Missing Security Headers', 'Server does not implement HSTS header', 'medium', 5.3, 'HTTP Headers', 'open', NOW()),
(1, 4, 'ip', 1, NULL, 'Open SSH Port', 'SSH port 22 is publicly accessible', 'medium', 5.0, 'Port 22/TCP', 'acknowledged', NOW());

-- Insert sample alerts
INSERT INTO alerts (tenant_id, asset_type, asset_id, title, description, severity, category, status, created_at) VALUES
(1, 'domain', 1, 'High Severity Vulnerability Detected', 'Outdated SSL/TLS configuration detected on democorp.com', 'high', 'vulnerability', 'open', NOW()),
(1, 'domain', 1, 'SSL Certificate Expiring Soon', 'SSL certificate for democorp.com expires in 30 days', 'medium', 'ssl_expiry', 'open', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'ip', 1, 'Suspicious Port Activity', 'Unusual port scan activity detected on 192.0.2.100', 'medium', 'network', 'acknowledged', DATE_SUB(NOW(), INTERVAL 5 DAY));

-- Insert initial usage tracking
INSERT INTO tenant_usage (tenant_id, period_start, period_end, monitored_assets_count, scans_count, alerts_count, api_calls_count) VALUES
(1, DATE_FORMAT(NOW(), '%Y-%m-01'), LAST_DAY(NOW()), 3, 15, 3, 42);

-- ============================================================
-- COMPLETED SCHEMA
-- ============================================================

-- Display success message
SELECT 'SplashSecurity database schema created successfully!' AS message;
SELECT 'Default credentials:' AS '';
SELECT '  Platform Admin: admin@splashsecurity.com / admin123' AS '';
SELECT '  Tenant Admin: admin@democorp.com / demo123' AS '';
SELECT '  Security Analyst: analyst@democorp.com / demo123' AS '';
SELECT '  Read Only: viewer@democorp.com / demo123' AS '';
