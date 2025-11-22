<h1>Security Dashboard</h1>

<?php if ($tenant): ?>
    <p style="color: #64748b; margin-bottom: 30px;">
        Organization: <strong><?php echo Security::escape($tenant['name']); ?></strong> |
        Role: <strong><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></strong>
    </p>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Monitored Domains</div>
        <div class="stat-value"><?php echo (int)$domain_stats['total']; ?></div>
        <div class="stat-label">Avg Score: <?php echo round($domain_stats['avg_score'] ?? 0); ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Monitored IPs</div>
        <div class="stat-value"><?php echo (int)$ip_stats['total']; ?></div>
        <div class="stat-label">Avg Score: <?php echo round($ip_stats['avg_score'] ?? 0); ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Open Vulnerabilities</div>
        <div class="stat-value" style="color: <?php echo $vuln_stats['critical'] > 0 ? '#dc2626' : '#10b981'; ?>">
            <?php echo (int)$vuln_stats['open']; ?>
        </div>
        <div class="stat-label">Critical: <?php echo (int)$vuln_stats['critical']; ?> | High: <?php echo (int)$vuln_stats['high']; ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Open Alerts</div>
        <div class="stat-value" style="color: <?php echo $alert_stats['critical'] > 0 ? '#dc2626' : '#3b82f6'; ?>">
            <?php echo (int)$alert_stats['open']; ?>
        </div>
        <div class="stat-label">Critical: <?php echo (int)$alert_stats['critical']; ?> | High: <?php echo (int)$alert_stats['high']; ?></div>
    </div>
</div>

<?php if ($subscription): ?>
    <div class="card">
        <div class="card-header">Subscription & Usage</div>
        <p><strong>Plan:</strong> <?php echo Security::escape($subscription['plan_name']); ?></p>
        <p><strong>Assets:</strong> <?php echo (int)$usage['monitored_assets_count']; ?> / <?php echo (int)$subscription['max_monitored_assets']; ?></p>
        <p><strong>Monthly Scans:</strong> <?php echo (int)$usage['scans_count']; ?> / <?php echo (int)$subscription['max_monthly_scans']; ?></p>
        <p><strong>API Calls:</strong> <?php echo (int)$usage['api_calls_count']; ?> (Rate Limit: <?php echo (int)$subscription['api_rate_limit']; ?>/hour)</p>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Recent Alerts</div>
    <?php if (!empty($recent_alerts)): ?>
        <table>
            <thead>
                <tr>
                    <th>Severity</th>
                    <th>Title</th>
                    <th>Asset</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_alerts as $alert): ?>
                    <tr>
                        <td><span class="badge badge-<?php echo $alert['severity']; ?>"><?php echo strtoupper($alert['severity']); ?></span></td>
                        <td><a href="<?php echo APP_URL; ?>/alerts/<?php echo $alert['id']; ?>"><?php echo Security::escape($alert['title']); ?></a></td>
                        <td><?php echo Security::escape($alert['asset_name'] ?? 'System'); ?></td>
                        <td><?php echo ucfirst($alert['status']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($alert['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: #64748b;">No recent alerts</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">Critical Vulnerabilities</div>
    <?php if (!empty($critical_vulns)): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Asset</th>
                    <th>CVE</th>
                    <th>CVSS</th>
                    <th>Discovered</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($critical_vulns, 0, 5) as $vuln): ?>
                    <tr>
                        <td><a href="<?php echo APP_URL; ?>/vulnerabilities/<?php echo $vuln['id']; ?>"><?php echo Security::escape($vuln['title']); ?></a></td>
                        <td><?php echo Security::escape($vuln['asset_name']); ?></td>
                        <td><?php echo Security::escape($vuln['cve_id'] ?? 'N/A'); ?></td>
                        <td><?php echo number_format($vuln['cvss_score'], 1); ?></td>
                        <td><?php echo date('M d, Y', strtotime($vuln['discovered_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: #10b981;">✓ No critical vulnerabilities detected</p>
    <?php endif; ?>
</div>
