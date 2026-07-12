<?php
$oldOtp = \App\Core\Session::getInstance()->getFlash('old_otp') ?? '';
?>
<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Reset Password</h2>
        <p class="auth-subtitle">Verify your recovery code and specify a new secure password for: <br><strong><?= htmlspecialchars($email) ?></strong></p>
        
        <form action="<?= $base ?>/reset-password" method="POST" class="auth-form" id="reset-password-form" novalidate>
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- OTP Code -->
            <div class="form-group">
                <label for="otp">Enter 6-Digit Recovery Code</label>
                <input type="text" id="otp" name="otp" class="auth-input" required maxlength="6" pattern="\d{6}" placeholder="123456" style="text-align: center; font-size: 20px; letter-spacing: 4px;" value="<?= htmlspecialchars($oldOtp) ?>">
                <span class="field-error" data-for="otp"></span>
            </div>

            <!-- New Password -->
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" class="auth-input" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" minlength="8">
                <small class="form-hint" style="display: block; margin-top: 4px; font-size: 11px; color: #777;">
                    Min 8 chars, including uppercase, lowercase, a number, and a symbol.
                </small>
                <span class="field-error" data-for="password"></span>
            </div>

            <!-- Confirm New Password -->
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="auth-input" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
                <span class="field-error" data-for="confirm_password"></span>
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
