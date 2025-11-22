<div class="login-container">
    <div class="login-header">
        <h1>🔐 SplashSecurity</h1>
        <p>Login to your account</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo Security::escape($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo APP_URL; ?>/login">
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
    </form>

    <div class="mt-20 text-center" style="color: #64748b; font-size: 13px;">
        <p><strong>Demo Credentials:</strong></p>
        <p>Platform Admin: admin@splashsecurity.com / admin123</p>
        <p>Tenant Admin: admin@democorp.com / demo123</p>
    </div>
</div>
