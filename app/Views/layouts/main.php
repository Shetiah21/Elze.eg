<?php
use App\Core\Session;
$session = Session::getInstance();
$cartCount = 0; // Placeholder for cart item counts (will bind in later phases)
$user = $session->get('user');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Elze.eg | Premium Egyptian Apparel') ?></title>
    
    <!-- Google Fonts: Outfit (Brand/Headers) and Inter (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="/Elze.eg/public/css/main.css">
</head>
<body>

    <!-- Header / Navigation -->
    <header class="main-header">
        <div class="header-container">
            <!-- Brand Logo -->
            <a href="/Elze.eg/public/" class="logo-link">
                <!-- SVG mimicking the elze.eg italicized, bold, lowercase logo -->
                <svg viewBox="0 0 120 45" class="brand-logo" width="120" height="45">
                    <text x="0" y="32" font-family="'Outfit', sans-serif" font-weight="900" font-style="italic" font-size="34" fill="#FFFFFF" letter-spacing="-1.5">elze</text>
                </svg>
            </a>

            <!-- Nav Links -->
            <nav class="main-nav">
                <a href="/Elze.eg/public/" class="nav-item">Home</a>
                <a href="/Elze.eg/public/products" class="nav-item">Shop</a>
                <div class="nav-dropdown">
                    <span class="nav-item dropdown-trigger">Categories</span>
                    <div class="dropdown-content">
                        <a href="/Elze.eg/public/products?category=t-shirts">T-Shirts</a>
                        <a href="/Elze.eg/public/products?category=ringer-t-shirts">Ringer T-Shirts</a>
                        <a href="/Elze.eg/public/products?category=knitted-polos">Knitted Polos</a>
                        <a href="/Elze.eg/public/products?category=tops">Tops</a>
                    </div>
                </div>
            </nav>

            <!-- Actions (Cart / Auth / Admin) -->
            <div class="header-actions">
                <a href="/Elze.eg/public/cart" class="action-item cart-action">
                    <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span class="cart-badge" id="cart-badge-count"><?= $cartCount ?></span>
                </a>
                
                <?php if ($user): ?>
                    <a href="/Elze.eg/public/dashboard" class="action-item dashboard-btn">My Account</a>
                    <?php if (($user['role'] ?? '') === 'admin'): ?>
                        <a href="/Elze.eg/public/admin" class="admin-pill">Admin</a>
                    <?php endif; ?>
                    <a href="/Elze.eg/public/logout" class="action-item logout-link">Logout</a>
                <?php else: ?>
                    <a href="/Elze.eg/public/login" class="btn btn-outline login-btn">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content Injection -->
    <main class="main-content">
        <!-- Render Flash Notifications if any -->
        <?php if ($flashSuccess = $session->getFlash('success')): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($flashSuccess) ?>
            </div>
        <?php endif; ?>
        <?php if ($flashError = $session->getFlash('error')): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($flashError) ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <svg viewBox="0 0 120 45" class="brand-logo" width="100" height="38">
                    <text x="0" y="32" font-family="'Outfit', sans-serif" font-weight="900" font-style="italic" font-size="34" fill="#FFFFFF" letter-spacing="-1.5">elze</text>
                </svg>
                <p>Egyptian local clothing brand delivering premium ringer tees, polos, and casual basics designed with modern fits and finest cottons.</p>
            </div>
            
            <div class="footer-links">
                <h4>Shop</h4>
                <a href="/Elze.eg/public/products?category=t-shirts">T-Shirts</a>
                <a href="/Elze.eg/public/products?category=ringer-t-shirts">Ringer T-Shirts</a>
                <a href="/Elze.eg/public/products?category=knitted-polos">Knitted Polos</a>
                <a href="/Elze.eg/public/products?category=tops">Tops</a>
            </div>

            <div class="footer-links">
                <h4>Support</h4>
                <a href="#">Size Guides</a>
                <a href="#">Shipping & Returns</a>
                <a href="#">FAQ</a>
                <a href="#">Contact Us</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Elze.eg. Made in Egypt. All rights reserved.</p>
        </div>
    </footer>

    <!-- Global JS -->
    <script src="/Elze.eg/public/js/app.js"></script>
</body>
</html>
