<div class="flex justify-between align-center mb-20">
    <h1>Monitored Domains</h1>
    <a href="<?php echo APP_URL; ?>/domains/create" class="btn btn-primary">+ Add Domain</a>
</div>

<?php if (!empty($domains)): ?>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Domain</th>
                    <th>Risk Score</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Last Scanned</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($domains as $domain): ?>
                    <tr>
                        <td>
                            <strong><a href="<?php echo APP_URL; ?>/domains/<?php echo $domain['id']; ?>"><?php echo Security::escape($domain['domain']); ?></a></strong>
                        </td>
                        <td><?php echo $domain['risk_score'] ?? 'N/A'; ?></td>
                        <td>
                            <?php if ($domain['risk_rating']): ?>
                                <span class="rating-badge rating-<?php echo strtolower($domain['risk_rating']); ?>">
                                    <?php echo $domain['risk_rating']; ?>
                                </span>
                            <?php else: ?>
                                <span class="badge badge-info">Not Scanned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $domain['status'] === 'active' ? 'success' : 'info'; ?>">
                                <?php echo ucfirst($domain['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $domain['last_scanned_at'] ? date('M d, Y H:i', strtotime($domain['last_scanned_at'])) : 'Never'; ?></td>
                        <td>
                            <form method="POST" action="<?php echo APP_URL; ?>/domains/<?php echo $domain['id']; ?>/scan" style="display: inline;">
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
            No domains monitored yet. <a href="<?php echo APP_URL; ?>/domains/create">Add your first domain</a>
        </p>
    </div>
<?php endif; ?>
