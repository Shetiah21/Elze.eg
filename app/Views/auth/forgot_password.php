<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Forgot Password?</h2>
        <p class="auth-subtitle">Enter your email and we'll dispatch a 6-digit recovery code to reset your password</p>
        
        <form action="<?= $base ?>/forgot-password" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="name@example.com">
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-submit">Request Recovery Code</button>
        </form>

        <div class="auth-footer">
            <p><a href="<?= $base ?>/login">Back to Sign In</a></p>
        </div>
    </div>
</div>
