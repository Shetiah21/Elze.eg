<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="auth-subtitle">Sign in to your Elze.eg account to manage orders</p>
        
        <form action="<?= $base ?>/login" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="name@example.com">
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
            </div>

            <!-- Remember Me / Forgot Pass -->
            <div class="form-row-checkbox">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember_me"> Remember me
                </label>
                <a href="<?= $base ?>/forgot-password" class="forgot-link">Forgot password?</a>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-submit">Sign In</button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="<?= $base ?>/register">Register now</a></p>
        </div>
    </div>
</div>
