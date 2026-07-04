<?php
$active = $active_tab ?? 'dashboard';
$navItems = [
    'dashboard' => ['label' => 'Dashboard', 'href' => '/dashboard', 'icon' => 'grid'],
    'profile'   => ['label' => 'My Profile', 'href' => '/dashboard/profile', 'icon' => 'user'],
    'orders'    => ['label' => 'My Orders', 'href' => '/dashboard/orders', 'icon' => 'bag'],
    'addresses' => ['label' => 'Saved Addresses', 'href' => '/dashboard/addresses', 'icon' => 'pin'],
    'wishlist'  => ['label' => 'Wishlist', 'href' => '/dashboard/wishlist', 'icon' => 'heart'],
    'settings'  => ['label' => 'Settings', 'href' => '/dashboard/settings', 'icon' => 'gear'],
];
?>
<aside class="dash-sidebar" id="dash-sidebar" aria-label="Account navigation">
    <div class="dash-sidebar-header">
        <a href="<?= $base ?>/" class="dash-sidebar-brand">elze</a>
        <button type="button" class="dash-sidebar-close" id="dash-sidebar-close" aria-label="Close menu">
            <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
    </div>
    <nav class="dash-nav">
        <?php foreach ($navItems as $key => $item): ?>
            <a href="<?= $base . $item['href'] ?>"
               class="dash-nav-link <?= $active === $key ? 'active' : '' ?>"
               <?= $active === $key ? 'aria-current="page"' : '' ?>>
                <span class="dash-nav-icon dash-icon-<?= $item['icon'] ?>" aria-hidden="true"></span>
                <?= htmlspecialchars($item['label']) ?>
            </a>
        <?php endforeach; ?>
        <a href="<?= $base ?>/logout" class="dash-nav-link dash-nav-logout">
            <span class="dash-nav-icon dash-icon-logout" aria-hidden="true"></span>
            Logout
        </a>
    </nav>
</aside>
<div class="dash-sidebar-overlay" id="dash-sidebar-overlay" hidden></div>
