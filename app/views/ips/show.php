<div class="flex justify-between align-center mb-20">
    <h1><?php echo Security::escape($ip['ip_address']); ?>
        <?php if ($ip['name']): ?>
            <small style="color: #64748b;">(<?php echo Security::escape($ip['name']); ?>)</small>
        <?php endif; ?>
    </h1>
    <div class="flex gap-10">
        <form method="POST" action="<?php echo APP_URL; ?>/ips/<?php echo $ip['id']; ?>/scan" style="display: inline;">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">
            <button type="submit" class="btn btn-primary">Run Scan</button>
        </form>
        <a href="<?php echo APP_URL; ?>/ips" class="btn btn-secondary">Back to IPs</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Risk Score</div>
        <div class="stat-value"><?php echo $ip['risk_score'] ?? 'N/A'; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Rating</div>
        <?php if ($ip['risk_rating']): ?>
            <div class="stat-value"><span class="rating-badge rating-<?php echo strtolower($ip['risk_rating']); ?>"><?php echo $ip['risk_rating']; ?></span></div>
        <?php else: ?>
            <div class="stat-value">N/A</div>
        <?php endif; ?>
    </div>
    <div class="stat-card">
        <div class="stat-label">Status</div>
        <div class="stat-value" style="font-size: 18px;"><?php echo ucfirst($ip['status']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Last Scanned</div>
        <div class="stat-value" style="font-size: 14px;"><?php echo $ip['last_scanned_at'] ? date('M d, Y H:i', strtotime($ip['last_scanned_at'])) : 'Never'; ?></div>
    </div>
</div>

<?php if (!empty($scans)): ?>
    <div class="card">
        <div class="card-header">Recent Scans</div>
        <table>
            <thead>
                <tr>
                    <th>Scan Type</th>
                    <th>Severity</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($scans, 0, 10) as $scan): ?>
                    <tr>
                        <td><?php echo ucwords(str_replace('_', ' ', $scan['scan_type'])); ?></td>
                        <td><span class="badge badge-<?php echo $scan['severity']; ?>"><?php echo strtoupper($scan['severity']); ?></span></td>
                        <td><?php echo date('M d, Y H:i', strtotime($scan['created_at'])); ?></td>
                        <td><a href="<?php echo APP_URL; ?>/scans/<?php echo $scan['id']; ?>" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if (!empty($vulnerabilities)): ?>
    <div class="card">
        <div class="card-header">Vulnerabilities</div>
        <table>
            <thead>
                <tr>
                    <th>Severity</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Discovered</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vulnerabilities as $vuln): ?>
                    <tr>
                        <td><span class="badge badge-<?php echo $vuln['severity']; ?>"><?php echo strtoupper($vuln['severity']); ?></span></td>
                        <td><a href="<?php echo APP_URL; ?>/vulnerabilities/<?php echo $vuln['id']; ?>"><?php echo Security::escape($vuln['title']); ?></a></td>
                        <td><?php echo ucfirst($vuln['status']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($vuln['discovered_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="card">
        <p style="color: #10b981;">✓ No vulnerabilities detected</p>
    </div>
<?php endif; ?>
