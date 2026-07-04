<section class="dash-panel" aria-labelledby="profile-page-title">
    <div class="dash-panel-header">
        <h2 class="dash-panel-title" id="profile-page-title">My Profile</h2>
    </div>
    <div class="dash-profile-grid">
        <div class="dash-info-item">
            <label>Full Name</label>
            <p><?= htmlspecialchars($user['name'] ?? '') ?></p>
        </div>
        <div class="dash-info-item">
            <label>Email Address</label>
            <p><?= htmlspecialchars($user['email'] ?? '') ?></p>
        </div>
        <div class="dash-info-item">
            <label>Phone Number</label>
            <p><?= $phone ? htmlspecialchars($phone) : 'Not set — add via Saved Addresses' ?></p>
        </div>
        <div class="dash-info-item">
            <label>Account Type</label>
            <p style="text-transform: capitalize;"><?= htmlspecialchars($user['role'] ?? 'user') ?></p>
        </div>
        <div class="dash-info-item">
            <label>Member Since</label>
            <p><?= $member_since ? date('F j, Y', strtotime($member_since)) : '—' ?></p>
        </div>
    </div>
    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #F0EFF5;">
        <p style="font-size: 13px; color: var(--color-charcoal-light); margin-bottom: 16px;">To update your phone number, edit your default shipping address.</p>
        <a href="<?= $base ?>/dashboard/addresses" class="dash-btn dash-btn-primary">Manage Addresses</a>
    </div>
</section>
