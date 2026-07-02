<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Reset Password</h2>
        <p class="auth-subtitle">Verify your recovery code and specify a new secure password for: <br><strong><?= htmlspecialchars($email) ?></strong></p>
        
        <form action="<?= $base ?>/reset-password" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- OTP Code -->
            <div class="form-group">
                <label for="otp">Enter 6-Digit Recovery Code</label>
                <input type="text" id="otp" name="otp" required maxlength="6" pattern="\d{6}" placeholder="123456" style="text-align: center; font-size: 20px; letter-spacing: 4px;">
            </div>

            <!-- New Password -->
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" minlength="6">
            </div>

            <!-- Confirm New Password -->
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-submit">Reset Password</button>
        </form>

        <div class="auth-footer" style="margin-top: 32px;">
            <p><a href="<?= $base ?>/forgot-password">Resend code</a> or <a href="<?= $base ?>/login" style="margin-left: 5px;">Back to login</a></p>
            
            <div style="background-color: var(--color-alabaster); padding: 12px; border-radius: var(--border-radius-sm); font-size: 12px; margin-top: 24px; text-align: left; border-left: 3px solid var(--color-brand-blue-light);">
                <strong>Local Development Note:</strong><br>
                Find the generated recovery OTP inside <code>storage/logs/mail.log</code>.
            </div>
        </div>
    </div>
</div>
