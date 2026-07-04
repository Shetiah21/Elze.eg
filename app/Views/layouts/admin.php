<?php
use App\Core\Session;
$session = Session::getInstance();
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '/Elze.eg/public';
$active = $active_section ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin | Elze.eg') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/css/main.css">
    <link rel="stylesheet" href="<?= $base ?>/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <a href="<?= $base ?>/admin">
                    <span class="admin-brand-text">elze</span>
                    <span class="admin-brand-badge">Admin</span>
                </a>
            </div>
            <nav class="admin-nav">
                <a href="<?= $base ?>/admin" class="admin-nav-link <?= $active === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="<?= $base ?>/admin/categories" class="admin-nav-link <?= $active === 'categories' ? 'active' : '' ?>">Categories</a>
                <a href="<?= $base ?>/admin/products" class="admin-nav-link <?= $active === 'products' ? 'active' : '' ?>">Products</a>
                <a href="<?= $base ?>/admin/orders" class="admin-nav-link <?= $active === 'orders' ? 'active' : '' ?>">Orders</a>
                <a href="<?= $base ?>/admin/coupons" class="admin-nav-link <?= $active === 'coupons' ? 'active' : '' ?>">Coupons</a>
                <a href="<?= $base ?>/admin/users" class="admin-nav-link <?= $active === 'users' ? 'active' : '' ?>">Users</a>
            </nav>
            <div class="admin-sidebar-footer">
                <a href="<?= $base ?>/" class="admin-nav-link">View Store</a>
                <a href="<?= $base ?>/logout" class="admin-nav-link admin-nav-danger">Logout</a>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <h1 class="admin-page-title"><?= htmlspecialchars($title ?? 'Admin') ?></h1>
                <?php if (!empty($admin_user)): ?>
                    <span class="admin-user-pill"><?= htmlspecialchars($admin_user['name']) ?></span>
                <?php endif; ?>
            </header>

            <?php if ($flashSuccess = $session->getFlash('success')): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div>
            <?php endif; ?>
            <?php if ($flashError = $session->getFlash('error')): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
            <?php endif; ?>

            <div class="admin-content">
                <?= $content ?>
            </div>
        </div>
    </div>
    <script src="<?= $base ?>/js/app.js"></script>
</body>
</html>
