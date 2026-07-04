<?php
use App\Core\Session;
$session = Session::getInstance();
$cartService = new \App\Services\CartService();
$cartCount = $cartService->getCartCount();
$layoutUser = $session->get('user');
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '/Elze.eg/public';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'My Account | Elze.eg') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/css/main.css">
    <link rel="stylesheet" href="<?= $base ?>/css/dashboard.css">
</head>
<body class="dash-body">

    <header class="main-header">
        <div class="header-container">
            <a href="<?= $base ?>/" class="logo-link">
                <svg viewBox="0 0 120 45" class="brand-logo" width="120" height="45">
                    <text x="0" y="32" font-family="'Outfit', sans-serif" font-weight="900" font-style="italic" font-size="34" fill="#FFFFFF" letter-spacing="-1.5">elze</text>
                </svg>
            </a>
            <nav class="main-nav">
                <a href="<?= $base ?>/" class="nav-item">Home</a>
                <a href="<?= $base ?>/products" class="nav-item">Shop</a>
            </nav>
            <div class="header-actions">
                <a href="<?= $base ?>/cart" class="action-item cart-action" aria-label="Shopping cart">
                    <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2" fill="none"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <span class="cart-badge"><?= $cartCount ?></span>
                </a>
                <a href="<?= $base ?>/dashboard" class="action-item dashboard-btn">My Account</a>
                <a href="<?= $base ?>/logout" class="action-item logout-link">Logout</a>
            </div>
        </div>
    </header>

    <?php if ($flashSuccess = $session->getFlash('success')): ?>
        <div class="alert alert-success dash-flash"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError = $session->getFlash('error')): ?>
        <div class="alert alert-danger dash-flash"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div class="dash-app">
        <?php include dirname(__DIR__) . '/dashboard/_sidebar.php'; ?>
        <div class="dash-main">
            <?php include dirname(__DIR__) . '/dashboard/_header.php'; ?>
            <div class="dash-content dash-animate-in">
                <?= $content ?>
            </div>
        </div>
    </div>

    <footer class="main-footer dash-footer-compact">
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Elze.eg. Made in Egypt. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?= $base ?>/js/app.js"></script>
</body>
</html>
