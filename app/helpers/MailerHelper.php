<?php
/**
 * Mailer Helper
 * Handles email sending (currently logs to file for simulation)
 */
class MailerHelper {

    public static function sendEmail($to, $subject, $body, $from = null) {
        $from = $from ?? env('MAIL_FROM_ADDRESS', 'noreply@splashsecurity.com');

        $emailData = [
            'to' => $to,
            'from' => $from,
            'subject' => $subject,
            'body' => $body,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Log email to file (in production, use real SMTP)
        self::logEmail($emailData);

        return true;
    }

    private static function logEmail($emailData) {
        $logFile = LOG_PATH . '/email_log.txt';

        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }

        $logEntry = str_repeat('=', 80) . PHP_EOL;
        $logEntry .= "Email sent at: {$emailData['timestamp']}" . PHP_EOL;
        $logEntry .= "To: {$emailData['to']}" . PHP_EOL;
        $logEntry .= "From: {$emailData['from']}" . PHP_EOL;
        $logEntry .= "Subject: {$emailData['subject']}" . PHP_EOL;
        $logEntry .= str_repeat('-', 80) . PHP_EOL;
        $logEntry .= $emailData['body'] . PHP_EOL;
        $logEntry .= str_repeat('=', 80) . PHP_EOL . PHP_EOL;

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public static function sendAlertEmail($user, $alert) {
        $subject = "[SplashSecurity] Alert: {$alert['title']}";

        $body = "Hello {$user['first_name']},\n\n";
        $body .= "A new {$alert['severity']} severity alert has been generated:\n\n";
        $body .= "Alert: {$alert['title']}\n";
        $body .= "Severity: " . strtoupper($alert['severity']) . "\n";
        $body .= "Description: {$alert['description']}\n\n";
        $body .= "Please log in to SplashSecurity to review and take action.\n\n";
        $body .= "View Alert: " . APP_URL . "/alerts/{$alert['id']}\n\n";
        $body .= "Best regards,\n";
        $body .= "SplashSecurity Team";

        return self::sendEmail($user['email'], $subject, $body);
    }

    public static function sendWeeklyReport($user, $reportData) {
        $subject = "[SplashSecurity] Weekly Security Report";

        $body = "Hello {$user['first_name']},\n\n";
        $body .= "Here is your weekly security report:\n\n";
        $body .= "=== Summary ===\n";
        $body .= "Monitored Domains: {$reportData['domains_count']}\n";
        $body .= "Monitored IPs: {$reportData['ips_count']}\n";
        $body .= "Average Security Score: {$reportData['avg_score']}\n\n";
        $body .= "=== This Week ===\n";
        $body .= "New Vulnerabilities: {$reportData['new_vulnerabilities']}\n";
        $body .= "Critical Alerts: {$reportData['critical_alerts']}\n";
        $body .= "Scans Performed: {$reportData['scans_performed']}\n\n";
        $body .= "View full report: " . APP_URL . "/reports\n\n";
        $body .= "Best regards,\n";
        $body .= "SplashSecurity Team";

        return self::sendEmail($user['email'], $subject, $body);
    }

    public static function sendVulnerabilityNotification($user, $vulnerability, $asset) {
        $subject = "[SplashSecurity] New Vulnerability Detected";

        $body = "Hello {$user['first_name']},\n\n";
        $body .= "A new {$vulnerability['severity']} severity vulnerability has been detected:\n\n";
        $body .= "Asset: {$asset}\n";
        $body .= "Vulnerability: {$vulnerability['title']}\n";
        $body .= "Severity: " . strtoupper($vulnerability['severity']) . "\n";
        $body .= "CVSS Score: {$vulnerability['cvss_score']}\n";
        $body .= "Description: {$vulnerability['description']}\n\n";
        $body .= "Remediation: {$vulnerability['remediation']}\n\n";
        $body .= "Please review and take appropriate action.\n\n";
        $body .= "View Details: " . APP_URL . "/vulnerabilities/{$vulnerability['id']}\n\n";
        $body .= "Best regards,\n";
        $body .= "SplashSecurity Team";

        return self::sendEmail($user['email'], $subject, $body);
    }

    public static function sendSslExpiryWarning($user, $domain, $daysRemaining) {
        $subject = "[SplashSecurity] SSL Certificate Expiring Soon";

        $body = "Hello {$user['first_name']},\n\n";
        $body .= "The SSL certificate for {$domain} is expiring in {$daysRemaining} days.\n\n";
        $body .= "Please renew the certificate as soon as possible to avoid service disruption.\n\n";
        $body .= "Domain: {$domain}\n";
        $body .= "Days Remaining: {$daysRemaining}\n\n";
        $body .= "View Domain Details: " . APP_URL . "/domains\n\n";
        $body .= "Best regards,\n";
        $body .= "SplashSecurity Team";

        return self::sendEmail($user['email'], $subject, $body);
    }
}
