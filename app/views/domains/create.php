<h1>Add Domain</h1>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="<?php echo APP_URL; ?>/domains/create">
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

        <div class="form-group">
            <label for="domain">Domain Name</label>
            <input type="text" id="domain" name="domain" placeholder="example.com" required>
            <small style="color: #64748b;">Enter the domain name without http:// or https://</small>
        </div>

        <div class="flex gap-10">
            <button type="submit" class="btn btn-primary">Add Domain</button>
            <a href="<?php echo APP_URL; ?>/domains" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
