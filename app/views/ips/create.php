<h1>Add IP Address</h1>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="<?php echo APP_URL; ?>/ips/create">
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

        <div class="form-group">
            <label for="ip_address">IP Address</label>
            <input type="text" id="ip_address" name="ip_address" placeholder="192.0.2.1" required>
        </div>

        <div class="form-group">
            <label for="name">Name (Optional)</label>
            <input type="text" id="name" name="name" placeholder="Web Server Primary">
        </div>

        <div class="flex gap-10">
            <button type="submit" class="btn btn-primary">Add IP</button>
            <a href="<?php echo APP_URL; ?>/ips" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
