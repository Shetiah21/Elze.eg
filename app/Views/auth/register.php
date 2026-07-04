<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Create Account</h2>
        <p class="auth-subtitle">Join Elze.eg to explore local premium fits and shop</p>
        
        <form action="<?= $base ?>/register" method="POST" class="auth-form" id="register-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="auth-input" required placeholder="Hatem Mohamed" autocomplete="name" minlength="2">
                <span class="field-error" data-for="name"></span>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="auth-input" required placeholder="name@example.com" autocomplete="email">
                <span class="field-error" data-for="email"></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="auth-input" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" minlength="6" autocomplete="new-password">
                <span class="field-error" data-for="password"></span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="auth-input" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" minlength="6" autocomplete="new-password">
                <span class="field-error" data-for="confirm_password"></span>
            </div>

            <button type="submit" class="btn-submit">Register</button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="<?= $base ?>/login" class="auth-link">Sign in instead</a></p>
        </div>
    </div>
</div>
