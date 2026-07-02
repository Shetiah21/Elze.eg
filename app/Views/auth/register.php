<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Create Account</h2>
        <p class="auth-subtitle">Join Elze.eg to explore local premium fits and shop</p>
        
        <form action="<?= $base ?>/register" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- Full Name -->
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required placeholder="Hatem Mohamed">
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="name@example.com">
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" minlength="6">
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-submit">Register</button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="<?= $base ?>/login">Sign in instead</a></p>
        </div>
    </div>
</div>
