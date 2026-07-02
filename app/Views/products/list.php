<?php
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '/Elze.eg/public';

// Helper: build a filter URL keeping all active params except the one being changed
function filterUrl(string $base, array $current, string $key, string $value): string {
    $params = $current;
    if ($params[$key] === $value) {
        unset($params[$key]); // toggle off
    } else {
        $params[$key] = $value;
    }
    $params = array_filter($params);
    return $base . '/products' . (!empty($params) ? '?' . http_build_query($params) : '');
}

$currentFilters = [
    'category' => $activeCategory,
    'size'     => $activeSize,
    'color'    => $activeColor,
    'search'   => $searchQuery,
];
?>

<div class="shop-page">

    <!-- Page Header -->
    <div class="shop-header">
        <div class="container">
            <h1 class="shop-page-title">
                <?= $activeCategory
                    ? htmlspecialchars(ucwords(str_replace('-', ' ', $activeCategory)))
                    : 'All Products' ?>
            </h1>
            <p class="shop-subtitle"><?= count($products) ?> item<?= count($products) !== 1 ? 's' : '' ?> available</p>
        </div>
    </div>

    <div class="shop-body container">

        <!-- ── Sidebar ── -->
        <aside class="shop-sidebar">

            <!-- Search -->
            <form method="GET" action="<?= $base ?>/products" class="sidebar-search-form">
                <?php if ($activeCategory): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($activeCategory) ?>">
                <?php endif; ?>
                <div class="search-input-wrap">
                    <input type="text" name="search" placeholder="Search products…"
                           value="<?= htmlspecialchars($searchQuery) ?>" class="sidebar-search-input">
                    <button type="submit" class="search-btn" aria-label="Search">
                        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Category Filter -->
            <div class="filter-group">
                <h4 class="filter-title">Category</h4>
                <a href="<?= $base ?>/products"
                   class="filter-chip <?= !$activeCategory ? 'active' : '' ?>">All</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= filterUrl($base, $currentFilters, 'category', $cat['slug']) ?>"
                       class="filter-chip <?= $activeCategory === $cat['slug'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Size Filter -->
            <div class="filter-group">
                <h4 class="filter-title">Size</h4>
                <div class="size-filter-grid">
                    <?php foreach ($allSizes as $sz): ?>
                        <a href="<?= filterUrl($base, $currentFilters, 'size', $sz) ?>"
                           class="size-chip <?= $activeSize === $sz ? 'active' : '' ?>">
                            <?= $sz ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Color Filter -->
            <div class="filter-group">
                <h4 class="filter-title">Color</h4>
                <?php
                $simpleColors = ['White', 'Black', 'Navy', 'Burgundy', 'Blue', 'Olive', 'Grey', 'Brown', 'Pink', 'Yellow'];
                $colorHex = [
                    'White'    => '#FAFAFA', 'Black' => '#1C1C1E',
                    'Navy'     => '#0A0933', 'Burgundy' => '#800020',
                    'Blue'     => '#3A7BD5', 'Olive' => '#6B7C3B',
                    'Grey'     => '#8E8E93', 'Brown' => '#795548',
                    'Pink'     => '#FF69B4', 'Yellow' => '#FFD60A',
                ];
                foreach ($simpleColors as $col): ?>
                    <a href="<?= filterUrl($base, $currentFilters, 'color', $col) ?>"
                       class="color-chip-filter <?= $activeColor === $col ? 'active' : '' ?>"
                       title="<?= $col ?>">
                        <span class="color-swatch-sm"
                              style="background:<?= $colorHex[$col] ?? '#ccc' ?>;
                                     <?= $col === 'White' ? 'border:1px solid #ddd;' : '' ?>">
                        </span>
                        <?= $col ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Clear Filters -->
            <?php if ($activeCategory || $activeSize || $activeColor || $searchQuery): ?>
                <a href="<?= $base ?>/products" class="clear-filters-btn">✕ Clear all filters</a>
            <?php endif; ?>

        </aside>

        <!-- ── Product Grid ── -->
        <div class="shop-main">
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <div class="no-products-icon">
                        <svg viewBox="0 0 24 24" width="64" height="64" stroke="#CBD5E0" stroke-width="1" fill="none">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                    <h3>No products found</h3>
                    <p>Try adjusting your filters or search term.</p>
                    <a href="<?= $base ?>/products" class="btn btn-primary-dark">View All Products</a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <a href="<?= $base ?>/products/<?= htmlspecialchars($product['slug']) ?>"
                           class="product-card">

                            <!-- Product Image -->
                            <div class="product-card-image">
                                <?php if (!empty($product['primary_image'])): ?>
                                    <img src="<?= $base . htmlspecialchars($product['primary_image']) ?>"
                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <!-- Styled placeholder when no image is uploaded yet -->
                                    <div class="product-img-placeholder">
                                        <span><?= strtoupper(substr($product['name'], 0, 2)) ?></span>
                                        <small><?= htmlspecialchars($product['category_name']) ?></small>
                                    </div>
                                <?php endif; ?>
                                <div class="product-card-overlay">
                                    <span class="quick-view-btn">View Product</span>
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div class="product-card-info">
                                <span class="product-card-cat"><?= htmlspecialchars($product['category_name']) ?></span>
                                <h3 class="product-card-name"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-card-price">
                                    <?= number_format($product['base_price'], 0) ?> EGP
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
