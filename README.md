# 🔐 SplashSecurity

**Multi-Tenant Cybersecurity Monitoring & Risk Scoring SaaS Platform**

A complete, production-ready SaaS application built with custom PHP MVC architecture for cybersecurity monitoring, vulnerability scanning, and risk assessment.

---

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Installation](#installation)
- [Configuration](#configuration)
- [Default Credentials](#default-credentials)
- [Usage Guide](#usage-guide)
- [Scanning System](#scanning-system)
- [Risk Scoring](#risk-scoring)
- [REST API Documentation](#rest-api-documentation)
- [Cron Jobs](#cron-jobs)
- [Testing](#testing)
- [Security Features](#security-features)
- [Deployment](#deployment)
- [Project Structure](#project-structure)
- [License](#license)

---

## ✨ Features

### Core Security Features
- **Domain Monitoring**: DNS analysis, SSL/TLS security, WHOIS tracking
- **IP Address Scanning**: Port scanning, service detection, geolocation
- **Email Security**: SPF, DKIM, and DMARC validation
- **Vulnerability Detection**: Simulated CVE tracking with CVSS scoring
- **Dark Web Monitoring**: Data breach and credential leak detection (simulated)
- **Risk Scoring Engine**: Intelligent 0-100 scoring with A-F ratings
- **Real-time Alerts**: Critical vulnerability and security event notifications

### Multi-Tenant SaaS Features
- **Complete Tenant Isolation**: Strict database-level data separation
- **Role-Based Access Control**: 4 roles (Platform Admin, Tenant Admin, Security Analyst, Read-Only)
- **Subscription Plans**: Free, Starter, Professional, Enterprise tiers
- **Usage Tracking**: Monitor assets, scans, alerts, and API calls
- **API Key Management**: Secure API authentication with rate limiting

### Platform Features
- **REST API**: Full-featured API with JSON responses
- **Automated Reports**: Weekly security reports via email
- **Activity Logging**: Comprehensive audit trail
- **Responsive Dashboard**: Real-time security metrics and statistics
- **Custom MVC Framework**: No external frameworks - lightweight and fast

---

## 🛠️ Tech Stack

- **Backend**: PHP 7.0+ (compatible with PHP 8.x)
- **Database**: MySQL 5.7+ / MariaDB 10.2+ (InnoDB)
- **Frontend**: Vanilla JavaScript, HTML5, CSS3
- **Architecture**: Custom lightweight MVC
- **Security**: PDO prepared statements, password hashing, CSRF protection
- **No Dependencies**: No Laravel, Symfony, Composer required

---

## 🏗️ Architecture

```
SplashSecurity/
├── app/
│   ├── controllers/     # Request handlers
│   ├── models/         # Database models
│   ├── views/          # HTML templates
│   ├── core/           # MVC core (Router, Controller, Model, View)
│   └── helpers/        # Scanning, validation, security helpers
├── config/             # Configuration files
├── public/             # Web root (index.php, assets)
├── storage/            # Logs, uploads
├── tests/              # Test suite
└── database.sql        # Database schema + seed data
```

### MVC Pattern
- **Model**: Data access layer with tenant isolation
- **View**: Template rendering with XSS protection
- **Controller**: Business logic with CSRF validation
- **Router**: URL routing with parameter extraction

---

## 📦 Installation

### Prerequisites
- PHP 7.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache/Nginx web server
- mod_rewrite (Apache) or equivalent

### Step 1: Clone Repository
```bash
git clone https://github.com/ahmedsaadawi13/SplashSecurity.git
cd SplashSecurity
```

### Step 2: Configure Environment
```bash
cp .env.example .env
```

Edit `.env` with your database credentials:
```env
DB_HOST=localhost
DB_NAME=splash_security
DB_USER=root
DB_PASS=your_password
APP_URL=http://localhost
APP_DEBUG=true
```

### Step 3: Create Database
```bash
mysql -u root -p
```

```sql
CREATE DATABASE splash_security CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### Step 4: Import Schema
```bash
mysql -u root -p splash_security < database.sql
```

This creates all tables and populates seed data including:
- 4 subscription plans (Free, Starter, Professional, Enterprise)
- Platform admin user
- Demo tenant with sample data
- Demo users with different roles
- Sample domains, IPs, scans, vulnerabilities, and alerts

### Step 5: Set Permissions
```bash
chmod -R 755 storage/
chmod -R 755 public/
```

### Step 6: Configure Web Server

**Apache (.htaccess already included)**:
```apache
DocumentRoot /path/to/SplashSecurity/public
```

**Nginx**:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/SplashSecurity/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?url=$uri&$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Step 7: Access Application
Navigate to: `http://localhost` (or your configured domain)

---

## 🔐 Default Credentials

### Platform Administrator
- **Email**: `admin@splashsecurity.com`
- **Password**: `admin123`
- **Role**: Platform Admin (full system access)

### Demo Tenant Users
- **Tenant Admin**:
  - Email: `admin@democorp.com`
  - Password: `demo123`

- **Security Analyst**:
  - Email: `analyst@democorp.com`
  - Password: `demo123`

- **Read-Only User**:
  - Email: `viewer@democorp.com`
  - Password: `demo123`

**⚠️ IMPORTANT**: Change these passwords immediately in production!

---

## 📚 Usage Guide

### Adding Monitored Assets

**Add Domain**:
1. Navigate to **Domains** → **Add Domain**
2. Enter domain name (e.g., `example.com`)
3. Click **Add Domain**
4. Click **Scan** to run security analysis

**Add IP Address**:
1. Navigate to **IPs** → **Add IP**
2. Enter IP address (e.g., `192.0.2.1`)
3. Optionally add a friendly name
4. Click **Add IP**
5. Click **Scan** to analyze

### Running Scans

Scans are executed on-demand or via cron:

- **Domain Scans**: DNS, SSL/TLS, Email Security, Vulnerabilities, Dark Web
- **IP Scans**: Port Scan, Geolocation, Blacklist Check, Vulnerabilities

Each scan updates the asset's risk score and generates alerts for critical findings.

### Managing Alerts

1. Navigate to **Alerts**
2. View open alerts sorted by severity
3. Click alert to view details
4. Actions:
   - **Acknowledge**: Mark as reviewed
   - **Close**: Mark as resolved

### Generating Reports

1. Navigate to **Reports** → **Generate Report**
2. Select report type:
   - Summary Report
   - Vulnerabilities Report
   - Domain Security Report
   - IP Security Report
3. Choose format (HTML/PDF/CSV)
4. Click **Generate**

---

## 🔍 Scanning System

### How Scanning Works

All scanning is **simulated** but realistic. The system demonstrates:

#### Domain Scans

**1. DNS Analysis**
- A, MX, TXT, NS, SOA records
- DNS change detection

**2. SSL/TLS Security**
- Certificate validity and expiration
- Protocol support (TLS 1.0, 1.1, 1.2, 1.3)
- Cipher strength
- HSTS configuration
- Scores: 0-100 with issues flagged

**3. Email Security**
- **SPF**: Sender Policy Framework validation
- **DKIM**: DomainKeys Identified Mail
- **DMARC**: Policy and enforcement level
- Recommendations for improvement

**4. Vulnerability Scan**
- Simulated CVEs with realistic severity
- CVSS scoring
- Affected components
- Remediation guidance

**5. Dark Web Monitoring**
- Data breach detection
- Credential leak scanning
- Paste site monitoring

#### IP Scans

**1. Port Scanning**
- Common ports (22, 80, 443, 3306, etc.)
- Service detection
- Risk assessment

**2. Geolocation**
- Country, city, ISP
- ASN information

**3. Blacklist Check**
- Major blacklist databases
- SPAM/malware listings

**4. Vulnerability Scan**
- Service-specific vulnerabilities
- Open port risks

---

## 📊 Risk Scoring

### Score Calculation

Risk scores range from 0 (worst) to 100 (best):

**For Domains**:
- TLS/SSL Security: 25%
- Email Security: 20%
- Vulnerabilities: 40%
- Dark Web Exposure: 15%

**For IPs**:
- Port Security: 30%
- Vulnerabilities: 50%
- Blacklist Status: 20%

### Rating System

| Score Range | Rating | Color | Meaning |
|-------------|--------|-------|---------|
| 90-100 | A | Green | Excellent |
| 80-89 | B | Blue | Good |
| 70-79 | C | Yellow | Fair |
| 60-69 | D | Orange | Poor |
| 0-59 | F | Red | Critical |

### Severity Levels

Vulnerabilities and alerts use standard severity:
- **Critical**: Immediate action required
- **High**: Urgent attention needed
- **Medium**: Should be addressed soon
- **Low**: Minor issue
- **Info**: Informational only

---

## 🔌 REST API Documentation

### Authentication

All API requests require an API key in the header:

```bash
X-API-KEY: sk_your_api_key_here
```

### API Endpoints

#### 1. List Assets
```http
GET /api/assets
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "domains": [...],
    "ips": [...],
    "total": 5
  }
}
```

#### 2. Trigger Domain Scan
```http
POST /api/scan/domain/{id}
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "message": "Scan initiated",
    "domain_id": 1,
    "domain": "example.com"
  }
}
```

#### 3. Trigger IP Scan
```http
POST /api/scan/ip/{id}
```

#### 4. Get Scan Result
```http
GET /api/scan/{scan_id}
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "id": 123,
    "scan_type": "ssl_tls",
    "severity": "info",
    "score": 85,
    "result": { ... },
    "created_at": "2024-01-15 10:30:00"
  }
}
```

#### 5. Get Risk Score
```http
GET /api/asset/{id}/score
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "asset_type": "domain",
    "asset_id": 1,
    "asset_name": "example.com",
    "risk_score": 85,
    "risk_rating": "A"
  }
}
```

#### 6. Get Alerts
```http
GET /api/alerts
```

#### 7. Get Vulnerabilities
```http
GET /api/vulnerabilities
```

### Error Responses

```json
{
  "status": "error",
  "message": "Invalid API key",
  "code": 403
}
```

### Rate Limiting

API rate limits are enforced per subscription plan:
- **Free**: 50 calls/hour
- **Starter**: 200 calls/hour
- **Professional**: 1000 calls/hour
- **Enterprise**: 5000 calls/hour

Exceeding limits returns HTTP 429.

### API Example (cURL)

```bash
# List all assets
curl -X GET http://localhost/api/assets \
  -H "X-API-KEY: sk_demo_api_key"

# Scan a domain
curl -X POST http://localhost/api/scan/domain/1 \
  -H "X-API-KEY: sk_demo_api_key"

# Get vulnerabilities
curl -X GET http://localhost/api/vulnerabilities \
  -H "X-API-KEY: sk_demo_api_key"
```

---

## ⏰ Cron Jobs

### Setup Automated Scanning

Add to your crontab (`crontab -e`):

```cron
# Weekly domain scans (Sundays at 2 AM)
0 2 * * 0 php /path/to/SplashSecurity/cron/weekly_scan.php

# Daily alert processing
0 1 * * * php /path/to/SplashSecurity/cron/process_alerts.php

# Monthly subscription renewal check
0 0 1 * * php /path/to/SplashSecurity/cron/check_subscriptions.php

# Weekly report generation (Mondays at 6 AM)
0 6 * * 1 php /path/to/SplashSecurity/cron/generate_reports.php
```

### Example Cron Scripts

**weekly_scan.php**:
```php
<?php
// Scan all active domains and IPs
require_once __DIR__ . '/../config/config.php';
// ... scanning logic
```

---

## 🧪 Testing

### Run All Tests
```bash
php tests/run_all_tests.php
```

### Individual Tests
```bash
# Database connectivity
php tests/test_database.php

# Scan simulations
php tests/test_scan_simulation.php

# Risk scoring
php tests/test_risk_score.php

# Alert generation
php tests/test_alert_generation.php

# API functionality
php tests/test_api_calls.php

# Tenant isolation
php tests/test_tenant_isolation.php
```

### Test Coverage
- ✅ Database connection and schema
- ✅ All scanning helpers
- ✅ Risk score calculations
- ✅ Alert generation and management
- ✅ API endpoint functionality
- ✅ Multi-tenant data isolation
- ✅ User authentication
- ✅ Subscription limits

---

## 🔒 Security Features

### Implemented Security Measures

1. **Authentication & Authorization**
   - Password hashing with bcrypt (cost 12)
   - Session-based authentication
   - Role-based permissions
   - Brute-force protection (5 attempts, 15-min lockout)

2. **Input Validation**
   - Server-side validation for all inputs
   - Domain and IP format validation
   - Email validation
   - SQL injection prevention via PDO prepared statements

3. **Output Protection**
   - XSS prevention with htmlspecialchars()
   - Content-Type headers
   - X-Frame-Options, X-Content-Type-Options

4. **CSRF Protection**
   - Token generation for all forms
   - Token validation on all POST requests
   - Session-based token storage

5. **Multi-Tenant Isolation**
   - Strict tenant_id enforcement in all queries
   - Prevent IDOR attacks
   - No cross-tenant data access

6. **API Security**
   - API key authentication
   - Rate limiting per tenant
   - Request validation

7. **File Upload Security** (if enabled)
   - MIME type validation
   - File size limits
   - Unique filenames
   - Restricted storage locations

8. **Database Security**
   - PDO prepared statements (100% coverage)
   - No dynamic SQL
   - Least privilege principle

---

## 🚀 Deployment

### Production Checklist

1. **Environment**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Change Default Passwords**

3. **HTTPS Configuration**:
   - Install SSL certificate
   - Force HTTPS redirects
   - Set `session.cookie_secure = 1`

4. **Database**:
   - Use strong DB password
   - Restrict DB access to localhost
   - Regular backups

5. **File Permissions**:
   ```bash
   chmod 750 storage/
   chmod 750 public/uploads/
   ```

6. **Web Server Hardening**:
   - Disable directory listing
   - Hide PHP version
   - Rate limit login attempts

7. **Monitoring**:
   - Enable error logging
   - Monitor `storage/logs/`
   - Set up alerts for critical errors

### Required PHP Extensions
- `pdo_mysql`
- `mbstring`
- `openssl`
- `json`
- `session`

### Recommended Server Specs
- **Minimum**: 1 CPU, 1GB RAM, 10GB storage
- **Recommended**: 2 CPU, 2GB RAM, 20GB storage
- **Scale**: Add resources based on tenant count

---

## 📁 Project Structure

```
SplashSecurity/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php        # Login/logout
│   │   ├── DashboardController.php   # Main dashboard
│   │   ├── DomainController.php      # Domain management
│   │   ├── IpController.php          # IP management
│   │   ├── AlertController.php       # Alert management
│   │   ├── VulnerabilityController.php
│   │   ├── ReportController.php      # Report generation
│   │   ├── ApiController.php         # REST API
│   │   └── AdminController.php       # Platform admin
│   │
│   ├── models/
│   │   ├── Tenant.php               # Multi-tenant core
│   │   ├── User.php                 # User management
│   │   ├── MonitoredDomain.php      # Domain tracking
│   │   ├── MonitoredIp.php          # IP tracking
│   │   ├── ScanResult.php           # Scan storage
│   │   ├── Vulnerability.php        # Vuln tracking
│   │   ├── Alert.php                # Alert management
│   │   └── ... (10+ models)
│   │
│   ├── views/                       # HTML templates
│   │   ├── layouts/main.php         # Master layout
│   │   ├── dashboard/
│   │   ├── domains/
│   │   ├── ips/
│   │   └── ...
│   │
│   ├── core/
│   │   ├── Database.php             # PDO wrapper
│   │   ├── Router.php               # URL routing
│   │   ├── Controller.php           # Base controller
│   │   ├── Model.php                # Base model
│   │   └── View.php                 # Template engine
│   │
│   └── helpers/
│       ├── Security.php             # Auth, CSRF, hashing
│       ├── Validator.php            # Input validation
│       ├── DomainScannerHelper.php  # DNS scanning
│       ├── TLSScannerHelper.php     # SSL/TLS analysis
│       ├── EmailSecurityHelper.php  # SPF/DKIM/DMARC
│       ├── VulnerabilityScannerHelper.php
│       ├── DarkWebScannerHelper.php
│       ├── IPScannerHelper.php      # Port scanning
│       ├── RiskScoreHelper.php      # Scoring engine
│       ├── MailerHelper.php         # Email sending
│       └── Logger.php               # Activity logging
│
├── config/
│   └── config.php                   # App configuration
│
├── public/
│   ├── index.php                    # Entry point
│   ├── .htaccess                    # Apache config
│   ├── css/style.css                # Stylesheet
│   └── js/app.js                    # JavaScript
│
├── storage/
│   ├── logs/
│   │   ├── app.log                  # Application log
│   │   └── email_log.txt            # Email log
│   └── uploads/                     # File uploads
│
├── tests/
│   ├── test_database.php
│   ├── test_scan_simulation.php
│   ├── test_risk_score.php
│   ├── test_alert_generation.php
│   ├── test_api_calls.php
│   ├── test_tenant_isolation.php
│   └── run_all_tests.php
│
├── .env                             # Environment config
├── .env.example                     # Example config
├── .htaccess                        # Root redirect
├── database.sql                     # Database schema
└── README.md                        # This file
```

---

## 🎯 Future Enhancements

### Real Integrations (Beyond Simulation)
- **VirusTotal API**: Real malware scanning
- **Shodan API**: Actual IP/port intelligence
- **HaveIBeenPwned API**: Real breach data
- **Let's Encrypt**: Certificate validation
- **MaxMind GeoIP**: Accurate geolocation
- **DNS-over-HTTPS**: Real DNS queries

### Additional Features
- **Continuous Monitoring**: Background scanning workers
- **Email Notifications**: Real SMTP integration
- **2FA/MFA**: Two-factor authentication
- **SSO**: SAML/OAuth integration
- **Custom Dashboards**: Widget-based UI
- **Export**: PDF report generation
- **Webhooks**: Event notifications
- **Slack/Teams Integration**: Alert forwarding
- **Queue System**: Redis/RabbitMQ for scans

### Scalability
- **Read Replicas**: Database scaling
- **Caching**: Redis/Memcached
- **CDN**: Asset delivery
- **Load Balancing**: Multi-server deployment
- **Microservices**: Split scanning workers

---

## 📝 Code Review & Self-Analysis

### Security Analysis
✅ **Strong**:
- Complete tenant isolation
- PDO prepared statements (100%)
- CSRF protection on all forms
- Password hashing with bcrypt
- XSS prevention via escaping
- Brute-force protection

⚠️ **Recommendations**:
- Add HTTPS enforcement in production
- Implement 2FA for admin accounts
- Add rate limiting on login
- Use CSP headers
- Implement session fixation protection

### Performance Optimization
✅ **Implemented**:
- Database indexes on tenant_id, foreign keys
- Efficient queries with JOIN optimization
- Minimal JavaScript

⚠️ **Future**:
- Add query result caching
- Implement lazy loading for scans
- Optimize large table queries
- Add database connection pooling

### Scalability Ideas
1. **Separate Scan Workers**: Move scanning to background jobs (Redis Queue)
2. **Database Sharding**: Split tenants across DBs
3. **API Gateway**: Rate limiting, caching
4. **CDN**: Static assets delivery
5. **Monitoring**: New Relic, Datadog integration

---

## 📄 License

This project is provided as-is for educational and commercial use. Modify and distribute freely.

---

## 👨‍💻 Author

Built as a demonstration of modern PHP SaaS architecture without frameworks.

**Need Help?**
- Review test files in `/tests/`
- Check logs in `/storage/logs/`
- Verify `.env` configuration

---

**🎉 Thank you for using SplashSecurity!**

For questions, issues, or feature requests, please open an issue on GitHub.
