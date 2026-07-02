<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Verify Your Email</h2>
        <p class="auth-subtitle">We have logged a 6-digit verification code for: <br><strong><?= htmlspecialchars($email) ?></strong></p>
        
        <form action="<?= $base ?>/verify-otp" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- OTP Code -->
            <div class="form-group">
                <label for="otp">Enter 6-Digit Code</label>
                <input type="text" id="otp" name="otp" required maxlength="6" pattern="\d{6}" placeholder="123456" style="text-align: center; font-size: 24px; letter-spacing: 8px;">
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-submit">Verify Account</button>
        </form>

        <div class="auth-footer" style="margin-top: 32px;">
            <p>Didn't receive the code? <br>
                <a href="<?= $base ?>/resend-otp" class="forgot-link">Resend Code</a> or 
                <a href="<?= $base ?>/login" style="margin-left: 5px;">Back to login</a>
            </p>
            
            <!-- Tip explaining mock environment -->
            <div style="background-color: var(--color-alabaster); padding: 12px; border-radius: var(--border-radius-sm); font-size: 12px; margin-top: 24px; text-align: left; border-left: 3px solid var(--color-brand-blue-light);">
                <strong>Local Development Note:</strong><br>
                Since we are in a testing phase, find the generated OTP printed in <code>storage/logs/mail.log</code>.
            </div>
        </div>
    </div>
</div>
