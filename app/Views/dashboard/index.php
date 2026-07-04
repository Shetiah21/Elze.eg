<div class="dashboard-wrapper">
    <h1 class="dashboard-title">My Account</h1>
    
    <div class="dashboard-layout">
        <!-- Sidebar Navigation -->
        <aside class="dashboard-sidebar">
            <nav class="sidebar-nav">
                <a href="<?= $base ?>/dashboard" class="sidebar-link <?= ($active_tab === 'profile') ? 'active' : '' ?>">Profile Management</a>
                <a href="<?= $base ?>/dashboard/orders" class="sidebar-link <?= ($active_tab === 'orders') ? 'active' : '' ?>">Order History</a>
                <a href="<?= $base ?>/dashboard/addresses" class="sidebar-link <?= ($active_tab === 'addresses') ? 'active' : '' ?>">Saved Addresses</a>
                <a href="<?= $base ?>/logout" class="sidebar-link" style="color: var(--color-danger); border-top: 1px solid var(--color-grey-border); margin-top: 16px; padding-top: 16px;">Logout</a>
            </nav>
        </aside>

        <!-- Content Area -->
        <main class="dashboard-content">
            <h3 class="dashboard-section-title">Profile Information</h3>
            
            <div class="user-profile-info">
                <div class="info-item">
                    <h5>Full Name</h5>
                    <p><?= htmlspecialchars($user['name'] ?? 'Elze Customer') ?></p>
                </div>
                
                <div class="info-item">
                    <h5>Email Address</h5>
                    <p><?= htmlspecialchars($user['email'] ?? '') ?></p>
                </div>

                <div class="info-item">
                    <h5>Account Level</h5>
                    <p style="text-transform: capitalize; font-weight: 700; color: var(--color-brand-blue-light);"><?= htmlspecialchars($user['role'] ?? 'user') ?></p>
                </div>
            </div>

            <!-- Visual placeholder for upcoming orders -->
            <div style="background-color: var(--color-alabaster); padding: 24px; border-radius: var(--border-radius-sm); border: 1px dashed var(--color-grey-border); margin-top: 32px;">
                <h4 style="font-family: var(--font-headers); font-size: 16px; margin-bottom: 8px; color: var(--color-brand-blue);">Looking to make a purchase?</h4>
                <p style="font-size: 13px; color: var(--color-charcoal-light); margin-bottom: 16px;">Add premium t-shirts, ringers, and polos to your cart and experience local quality.</p>
                <a href="<?= $base ?>/products" class="btn btn-outline" style="color: var(--color-brand-blue); border-color: var(--color-brand-blue); padding: 8px 16px; display: inline-block;">Shop Catalog</a>
            </div>
        </main>
    </div>
</div>
