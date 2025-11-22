<div class="flex justify-between align-center mb-20">
    <h1>Monitored IP Addresses</h1>
    <a href="<?php echo APP_URL; ?>/ips/create" class="btn btn-primary">+ Add IP</a>
</div>

<?php if (!empty($ips)): ?>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>IP Address</th>
                    <th>Name</th>
                    <th>Risk Score</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Last Scanned</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ips as $ip): ?>
                    <tr>
                        <td><strong><a href="<?php echo APP_URL; ?>/ips/<?php echo $ip['id']; ?>"><?php echo Security::escape($ip['ip_address']); ?></a></strong></td>
                        <td><?php echo Security::escape($ip['name'] ?? '-'); ?></td>
                        <td><?php echo $ip['risk_score'] ?? 'N/A'; ?></td>
                        <td>
                            <?php if ($ip['risk_rating']): ?>
                                <span class="rating-badge rating-<?php echo strtolower($ip['risk_rating']); ?>"><?php echo $ip['risk_rating']; ?></span>
                            <?php else: ?>
                                <span class="badge badge-info">Not Scanned</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-<?php echo $ip['status'] === 'active' ? 'success' : 'info'; ?>"><?php echo ucfirst($ip['status']); ?></span></td>
                        <td><?php echo $ip['last_scanned_at'] ? date('M d, Y H:i', strtotime($ip['last_scanned_at'])) : 'Never'; ?></td>
                        <td>
                            <form method="POST" action="<?php echo APP_URL; ?>/ips/<?php echo $ip['id']; ?>/scan" style="display: inline;">
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo Security::generateCsrfToken(); ?>">
                                <button type="submit" class="btn btn-primary btn-sm">Scan</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="card">
        <p style="color: #64748b; text-align: center; padding: 40px;">
            No IP addresses monitored yet. <a href="<?php echo APP_URL; ?>/ips/create">Add your first IP</a>
        </p>
    </div>
<?php endif; ?>
