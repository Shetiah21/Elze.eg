<?php
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '/Elze.eg/public';
?>

<div class="cart-page-wrapper">
    <div class="container">
        
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="<?= $base ?>/">Home</a>
            <span>›</span>
            <span>Shopping Cart</span>
        </nav>

        <h1 class="cart-title">Your Shopping Cart</h1>

        <div id="cart-container" class="cart-layout" style="<?= empty($cartItems) ? 'display: none;' : '' ?>">
            
            <!-- Cart Items List (Left Side) -->
            <div class="cart-items-column">
                <div class="cart-card">
                    <div class="cart-items-list" id="cart-items-list">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item-row" data-variant-id="<?= $item['variant_id'] ?>" id="cart-item-<?= $item['variant_id'] ?>">
                                
                                <!-- Product Image -->
                                <div class="cart-item-image">
                                    <?php if ($item['primary_image']): ?>
                                        <img src="<?= $base . htmlspecialchars($item['primary_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                    <?php else: ?>
                                        <div class="cart-item-image-placeholder">
                                            <span><?= strtoupper(substr($item['product_name'], 0, 2)) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Product Info -->
                                <div class="cart-item-details">
                                    <h3 class="cart-item-name">
                                        <a href="<?= $base ?>/products/<?= htmlspecialchars($item['product_slug']) ?>">
                                            <?= htmlspecialchars($item['product_name']) ?>
                                        </a>
                                    </h3>
                                    
                                    <div class="cart-item-variants">
                                        <span class="variant-badge">Color: <strong><?= htmlspecialchars($item['color']) ?></strong></span>
                                        <span class="variant-badge">Size: <strong><?= htmlspecialchars($item['size']) ?></strong></span>
                                    </div>
                                    
                                    <div class="cart-item-price-mobile">
                                        <span>Unit Price: <strong><?= number_format($item['price'], 0) ?> EGP</strong></span>
                                    </div>
                                </div>

                                <!-- Quantity Selector -->
                                <div class="cart-item-quantity">
                                    <div class="qty-selector">
                                        <button type="button" class="qty-btn" onclick="updateCartQty(<?= $item['variant_id'] ?>, -1)">−</button>
                                        <span class="qty-value" id="qty-val-<?= $item['variant_id'] ?>"><?= $item['quantity'] ?></span>
                                        <button type="button" class="qty-btn" onclick="updateCartQty(<?= $item['variant_id'] ?>, 1)">+</button>
                                    </div>
                                    <span class="stock-warning" id="stock-warning-<?= $item['variant_id'] ?>" style="display:none; color: var(--color-danger); font-size: 11px; margin-top: 4px; display: block; font-weight: 500;">
                                        Max stock reached
                                    </span>
                                </div>

                                <!-- Subtotal & Delete -->
                                <div class="cart-item-totals">
                                    <div class="item-subtotal-price" id="item-sub-<?= $item['variant_id'] ?>">
                                        <?= number_format($item['subtotal'], 0) ?> EGP
                                    </div>
                                    
                                    <button type="button" class="cart-item-remove-btn" onclick="removeCartItem(<?= $item['variant_id'] ?>)" title="Remove item">
                                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        <span>Remove</span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Order Summary (Right Side) -->
            <div class="cart-summary-column">
                <div class="summary-card">
                    <h3 class="summary-title">Order Summary</h3>
                    
                    <div class="summary-rows">
                        <div class="summary-row">
                            <span class="row-label">Subtotal</span>
                            <span class="row-value" id="cart-subtotal"><?= number_format($subtotal, 0) ?> EGP</span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="row-label">Shipping</span>
                            <span class="row-value shipping-value">Calculated at checkout</span>
                        </div>

                        <div class="summary-row tax-row">
                            <span class="row-label">Estimated 14% VAT <small style="display: block; font-size: 10px; color: var(--color-charcoal-light); font-weight: normal;">(Included in subtotal)</small></span>
                            <span class="row-value" id="cart-tax"><?= number_format($tax, 0) ?> EGP</span>
                        </div>
                        
                        <hr class="summary-divider">

                        <div class="summary-row total-row">
                            <span class="row-label">Grand Total</span>
                            <span class="row-value" id="cart-total"><?= number_format($total, 0) ?> EGP</span>
                        </div>
                    </div>

                    <div class="summary-actions">
                        <a href="<?= $base ?>/checkout" class="btn btn-primary btn-block checkout-btn">
                            Proceed to Checkout
                        </a>
                        
                        <a href="<?= $base ?>/products" class="btn-continue-shopping">
                            ← Continue Shopping
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Empty Cart State -->
        <div id="cart-empty-state" class="cart-empty-state" style="<?= !empty($cartItems) ? 'display: none;' : '' ?>">
            <div class="empty-state-card">
                <div class="empty-icon-wrap">
                    <svg viewBox="0 0 24 24" width="72" height="72" stroke="currentColor" stroke-width="1.5" fill="none">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <h2>Your Shopping Cart is Empty</h2>
                <p>Looks like you haven't added any products to your cart yet. Explore our catalog of high-quality local Egyptian apparel.</p>
                <a href="<?= $base ?>/products" class="btn btn-brand-blue">Shop Our Collection</a>
            </div>
        </div>

    </div>
</div>

<script>
/**
 * Update quantity using AJAX
 */
function updateCartQty(variantId, delta) {
    const qtyElement = document.getElementById('qty-val-' + variantId);
    if (!qtyElement) return;

    let currentQty = parseInt(qtyElement.textContent);
    let targetQty = currentQty + delta;
    if (targetQty <= 0) {
        removeCartItem(variantId);
        return;
    }

    // Call update API endpoint
    fetch('<?= $base ?>/cart/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            variant_id: variantId,
            quantity: targetQty
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update quantity display
            qtyElement.textContent = data.item_quantity;
            
            // Update line item subtotal
            document.getElementById('item-sub-' + variantId).textContent = data.item_subtotal;
            
            // Update summary details
            document.getElementById('cart-subtotal').textContent = data.subtotal;
            document.getElementById('cart-tax').textContent = data.tax;
            document.getElementById('cart-total').textContent = data.total;
            
            // Update global header cart count badge
            const badge = document.getElementById('cart-badge-count');
            if (badge) {
                badge.textContent = data.cart_count;
                if (data.cart_count > 0) {
                    badge.style.display = 'block';
                }
            }

            // Hide max stock warning if it was shown and update worked
            document.getElementById('stock-warning-' + variantId).style.display = 'none';
        } else {
            // Show error/warning (e.g. stock limitation)
            if (data.error && data.error.includes('exceeds available stock')) {
                const warn = document.getElementById('stock-warning-' + variantId);
                warn.style.display = 'block';
                warn.textContent = 'Only ' + (targetQty - 1) + ' items in stock';
                setTimeout(() => { warn.style.display = 'none'; }, 5000);
            } else {
                alert(data.error || 'Failed to update quantity.');
            }
        }
    })
    .catch(err => {
        console.error('AJAX quantity update error:', err);
    });
}

/**
 * Remove item from cart using AJAX
 */
function removeCartItem(variantId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    fetch('<?= $base ?>/cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            variant_id: variantId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Remove item row from DOM with a nice fade out
            const row = document.getElementById('cart-item-' + variantId);
            if (row) {
                row.style.transition = 'all 0.4s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    row.remove();
                    
                    // If cart is empty, show empty state
                    if (data.is_empty) {
                        document.getElementById('cart-container').style.display = 'none';
                        document.getElementById('cart-empty-state').style.display = 'block';
                    }
                }, 400);
            }

            // Update summary details
            document.getElementById('cart-subtotal').textContent = data.subtotal;
            document.getElementById('cart-tax').textContent = data.tax;
            document.getElementById('cart-total').textContent = data.total;
            
            // Update global header cart count badge
            const badge = document.getElementById('cart-badge-count');
            if (badge) {
                badge.textContent = data.cart_count;
                if (data.cart_count === 0) {
                    badge.textContent = '0';
                }
            }
        } else {
            alert(data.error || 'Failed to remove item.');
        }
    })
    .catch(err => {
        console.error('AJAX remove item error:', err);
    });
}
</script>
