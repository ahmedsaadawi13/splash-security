<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo Security::escape($title ?? 'SplashSecurity'); ?> - SplashSecurity</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="<?php echo APP_URL; ?>/dashboard" class="logo">🔐 SplashSecurity</a>
            <nav class="nav">
                <?php if (isset($user)): ?>
                    <a href="<?php echo APP_URL; ?>/dashboard">Dashboard</a>
                    <a href="<?php echo APP_URL; ?>/domains">Domains</a>
                    <a href="<?php echo APP_URL; ?>/ips">IPs</a>
                    <a href="<?php echo APP_URL; ?>/vulnerabilities">Vulnerabilities</a>
                    <a href="<?php echo APP_URL; ?>/alerts">Alerts</a>
                    <a href="<?php echo APP_URL; ?>/reports">Reports</a>
                    <?php if ($user['role'] === 'platform_admin'): ?>
                        <a href="<?php echo APP_URL; ?>/admin">Admin</a>
                    <?php endif; ?>
                    <a href="<?php echo APP_URL; ?>/settings">Settings</a>
                    <a href="<?php echo APP_URL; ?>/logout">Logout (<?php echo Security::escape($user['first_name']); ?>)</a>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/login">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo Security::escape($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo Security::escape($error); ?></div>
        <?php endif; ?>

        <?php echo $content; ?>
    </div>

    <script src="<?php echo APP_URL; ?>/js/app.js"></script>
</body>
</html>
