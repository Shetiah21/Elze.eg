<?php
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '/Elze.eg/public';

// Build color-to-hex mapping for swatch rendering
$colorHex = [
    'White'    => '#FAFAFA', 'Black' => '#1C1C1E',
    'Navy'     => '#0A0933', 'Burgundy' => '#800020',
    'Blue'     => '#3A7BD5', 'Olive' => '#6B7C3B',
    'Grey'     => '#8E8E93', 'Brown' => '#795548',
    'Pink'     => '#FF69B4', 'Yellow' => '#FFD60A',
    'White ra2aba black'    => 'linear-gradient(to right, #FAFAFA 50%, #1C1C1E 50%)',
    'Black ra2aba white'    => 'linear-gradient(to right, #1C1C1E 50%, #FAFAFA 50%)',
    'Navy ra2aba white'     => 'linear-gradient(to right, #0A0933 50%, #FAFAFA 50%)',
    'Burgundy ra2aba white' => 'linear-gradient(to right, #800020 50%, #FAFAFA 50%)',
];

$isGradient = fn($c) => str_contains($c, 'ra2aba');
$stockMapJson = json_encode($stockMap ?? []);
?>

<div class="product-detail-page">
    <div class="container">

        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="<?= $base ?>/">Home</a>
            <span>›</span>
            <a href="<?= $base ?>/products?category=<?= htmlspecialchars($product['category_slug']) ?>">
                <?= htmlspecialchars($product['category_name']) ?>
            </a>
            <span>›</span>
            <span><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <!-- Main Product Section -->
        <div class="product-detail-grid">

            <!-- ── Left: Image Gallery ── -->
            <div class="product-gallery">
                <div class="gallery-main" id="gallery-main">
                    <?php if (!empty($images)): ?>
                        <img src="<?= $base . htmlspecialchars($images[0]['image_path']) ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             id="main-image" class="gallery-main-img">
                    <?php else: ?>
                        <!-- Premium placeholder if no images uploaded yet -->
                        <div class="gallery-placeholder">
                            <div class="placeholder-inner">
                                <svg viewBox="0 0 24 24" width="56" height="56" stroke="rgba(255,255,255,0.4)" stroke-width="1" fill="none">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                                <p>Product image coming soon</p>
                                <small>Client images will appear here</small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Thumbnails -->
                <?php if (count($images) > 1): ?>
                    <div class="gallery-thumbs">
                        <?php foreach ($images as $i => $img): ?>
                            <div class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>"
                                 onclick="switchImage('<?= $base . htmlspecialchars($img['image_path']) ?>', this)">
                                <img src="<?= $base . htmlspecialchars($img['image_path']) ?>"
                                     alt="View <?= $i + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ── Right: Product Info ── -->
            <div class="product-detail-info">
                <span class="detail-category-tag"><?= htmlspecialchars($product['category_name']) ?></span>
                <h1 class="detail-product-name"><?= htmlspecialchars($product['name']) ?></h1>
                <p class="detail-price" id="detail-price">
                    <?= number_format($product['base_price'], 0) ?> <span>EGP</span>
                </p>

                <!-- Color Selector -->
                <div class="detail-option-group">
                    <label class="option-label">
                        Color: <strong id="selected-color-label">Select a color</strong>
                    </label>
                    <div class="color-swatches">
                        <?php foreach ($availableColors as $color): ?>
                            <?php
                            $bg = $colorHex[$color] ?? '#ccc';
                            $isGrad = $isGradient($color);
                            $style = $isGrad
                                ? "background: {$bg};"
                                : "background-color: {$bg};";
                            $border = ($color === 'White' || $color === 'White ra2aba black')
                                ? 'border: 2px solid #ddd;' : '';
                            ?>
                            <button type="button"
                                    class="color-swatch"
                                    data-color="<?= htmlspecialchars($color) ?>"
                                    title="<?= htmlspecialchars($color) ?>"
                                    style="<?= $style . $border ?>"
                                    onclick="selectColor('<?= htmlspecialchars($color) ?>', this)">
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Size Selector -->
                <div class="detail-option-group">
                    <label class="option-label">
                        Size: <strong id="selected-size-label">Select a size</strong>
                    </label>
                    <div class="size-buttons" id="size-buttons">
                        <?php foreach ($availableSizes as $size): ?>
                            <button type="button"
                                    class="size-btn"
                                    data-size="<?= $size ?>"
                                    onclick="selectSize('<?= $size ?>', this)">
                                <?= $size ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <a href="#" class="size-guide-link" onclick="openSizeChart(event)">
                        📐 Size Chart
                    </a>
                </div>

                <!-- Add to Cart -->
                <div class="detail-add-cart">
                    <div class="qty-selector">
                        <button type="button" onclick="changeQty(-1)">−</button>
                        <span id="qty-display">1</span>
                        <button type="button" onclick="changeQty(1)">+</button>
                    </div>
                    <button type="button" class="add-to-cart-btn" onclick="addToCart(<?= $product['id'] ?>)" id="add-cart-btn">
                        Add to Cart
                    </button>
                </div>
                <div id="variant-error" class="variant-error" style="display:none;">
                    Please select a color and size before adding to cart.
                </div>

                <!-- Product Accordion -->
                <div class="product-accordion">
                    <div class="accordion-item">
                        <button class="accordion-trigger" onclick="toggleAccordion(this)">
                            Product Details <span>+</span>
                        </button>
                        <div class="accordion-content">
                            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-trigger" onclick="toggleAccordion(this)">
                            Shipping & Delivery <span>+</span>
                        </button>
                        <div class="accordion-content">
                            <ul>
                                <li>Cairo & Giza: 2–3 business days</li>
                                <li>Alexandria & Delta: 3–5 business days</li>
                                <li>Upper Egypt & Sinai: 4–6 business days</li>
                                <li>Shipping fees calculated at checkout</li>
                            </ul>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-trigger" onclick="toggleAccordion(this)">
                            Return Policy <span>+</span>
                        </button>
                        <div class="accordion-content">
                            <p>We accept returns within 7 days of delivery for unworn, unwashed items with original packaging. Contact us to initiate a return.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Related Products ── -->
        <?php if (!empty($related)): ?>
            <div class="related-section">
                <h2 class="related-title">You Might Also Like</h2>
                <div class="related-grid">
                    <?php foreach ($related as $r): ?>
                        <a href="<?= $base ?>/products/<?= htmlspecialchars($r['slug']) ?>" class="product-card">
                            <div class="product-card-image">
                                <?php if (!empty($r['primary_image'])): ?>
                                    <img src="<?= $base . htmlspecialchars($r['primary_image']) ?>"
                                         alt="<?= htmlspecialchars($r['name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="product-img-placeholder">
                                        <span><?= strtoupper(substr($r['name'], 0, 2)) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="product-card-overlay">
                                    <span class="quick-view-btn">View Product</span>
                                </div>
                            </div>
                            <div class="product-card-info">
                                <h3 class="product-card-name"><?= htmlspecialchars($r['name']) ?></h3>
                                <p class="product-card-price"><?= number_format($r['base_price'], 0) ?> EGP</p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div><!-- end .container -->
</div>

<!-- ── Size Chart Modal ── -->
<div class="modal-backdrop" id="size-chart-modal" onclick="closeSizeChartOutside(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Size Chart</h3>
            <button class="modal-close" onclick="closeSizeChart()">✕</button>
        </div>
        <div class="modal-body">
            <?php if ($sizeChart): ?>
                <p class="modal-note">All measurements are in <strong>centimeters (cm)</strong>.</p>
                <div class="size-chart-table-wrap">
                    <table class="size-chart-table">
                        <thead>
                            <tr>
                                <th>Size</th>
                                <?php
                                $firstSize = reset($sizeChart);
                                foreach (array_keys($firstSize) as $col):
                                ?>
                                    <th><?= ucfirst($col) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sizeChart as $sizeName => $measurements): ?>
                                <tr>
                                    <td><strong><?= $sizeName ?></strong></td>
                                    <?php foreach ($measurements as $val): ?>
                                        <td><?= $val ?> cm</td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="modal-tip">Tip: Measure your chest at its widest point for the best fit.</p>
            <?php else: ?>
                <p>Size chart will be added soon. Contact us for sizing assistance.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Variant stock map for JS -->
<script>
const STOCK_MAP    = <?= $stockMapJson ?>;
const BASE_PRICE   = <?= (float)$product['base_price'] ?>;
let selectedColor  = null;
let selectedSize   = null;
let currentQty     = 1;

function selectColor(color, el) {
    selectedColor = color;
    document.getElementById('selected-color-label').textContent = color;
    document.querySelectorAll('.color-swatch').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    updateSizeAvailability();
}

function selectSize(size, el) {
    selectedSize = size;
    document.getElementById('selected-size-label').textContent = size;
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
}

function updateSizeAvailability() {
    document.querySelectorAll('.size-btn').forEach(btn => {
        const sz = btn.dataset.size;
        const inStock = selectedColor
            && STOCK_MAP[selectedColor]
            && STOCK_MAP[selectedColor][sz] > 0;
        btn.classList.toggle('out-of-stock', !inStock && selectedColor !== null);
    });
}

function changeQty(delta) {
    currentQty = Math.max(1, currentQty + delta);
    document.getElementById('qty-display').textContent = currentQty;
}

function addToCart(productId) {
    const err = document.getElementById('variant-error');
    if (!selectedColor || !selectedSize) {
        err.style.display = 'block';
        setTimeout(() => err.style.display = 'none', 3000);
        return;
    }
    err.style.display = 'none';
    // Cart AJAX request will be wired in Phase 4
    const btn = document.getElementById('add-cart-btn');
    btn.textContent = 'Added ✓';
    btn.classList.add('added');
    setTimeout(() => { btn.textContent = 'Add to Cart'; btn.classList.remove('added'); }, 2000);
}

function switchImage(src, thumb) {
    document.getElementById('main-image').src = src;
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}

function toggleAccordion(btn) {
    const content = btn.nextElementSibling;
    const isOpen  = content.style.maxHeight;
    document.querySelectorAll('.accordion-content').forEach(c => c.style.maxHeight = '');
    document.querySelectorAll('.accordion-trigger span').forEach(s => s.textContent = '+');
    if (!isOpen) {
        content.style.maxHeight = content.scrollHeight + 'px';
        btn.querySelector('span').textContent = '−';
    }
}

function openSizeChart(e) {
    e.preventDefault();
    document.getElementById('size-chart-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeSizeChart() {
    document.getElementById('size-chart-modal').classList.remove('open');
    document.body.style.overflow = '';
}

function closeSizeChartOutside(e) {
    if (e.target === document.getElementById('size-chart-modal')) closeSizeChart();
}
</script>
