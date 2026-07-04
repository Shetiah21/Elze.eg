<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="auth-subtitle">Sign in to your Elze.eg account to manage orders</p>
        
        <form action="<?= $base ?>/login" method="POST" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="auth-input" required placeholder="name@example.com" autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="auth-input" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" autocomplete="current-password">
            </div>

            <div class="form-row-checkbox">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember_me"> Remember me
                </label>
                <a href="<?= $base ?>/forgot-password" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-submit">Sign In</button>
        </form>

        <div class="auth-divider">
            <span>or</span>
        </div>

        <div class="auth-footer">
            <p>Don't have an account?</p>
            <a href="<?= $base ?>/register" class="btn-signup">Sign Up</a>
        </div>
    </div>
</div>
