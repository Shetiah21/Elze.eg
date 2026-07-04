<?php
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '/Elze.eg/public';

// Progress mapping
$status = strtolower($order['status']);
$steps = ['pending' => 1, 'processing' => 2, 'shipped' => 3, 'delivered' => 4];
$currentStep = $steps[$status] ?? 1;

if ($status === 'cancelled') {
    $currentStep = -1; // Cancelled state
}
?>

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
            
            <!-- Order Title and Receipt Action -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <a href="<?= $base ?>/dashboard/orders" style="font-size: 13px; color: var(--color-brand-blue); display: inline-block; margin-bottom: 8px;">← Back to Order History</a>
                    <h3 class="dashboard-section-title" style="margin-bottom: 0;">Order details #<?= htmlspecialchars($order['order_number']) ?></h3>
                </div>
                <a href="<?= $base ?>/orders/receipt/<?= $order['id'] ?>" target="_blank" class="btn btn-outline" style="border-color: var(--color-brand-blue); color: var(--color-brand-blue); padding: 8px 16px; font-size: 13px;">
                    Download Invoice Receipt 📄
                </a>
            </div>

            <!-- Order Milestone Progress Tracker -->
            <div class="summary-card" style="margin-bottom: 32px; padding: 32px;">
                <h4 style="font-family: var(--font-headers); color: var(--color-brand-blue); font-size: 15px; margin-bottom: 24px; text-align: center;">Delivery Tracking Timeline</h4>
                
                <?php if ($currentStep === -1): ?>
                    <!-- Cancelled State -->
                    <div style="background-color: var(--color-danger-bg); border: 1px solid rgba(255, 59, 48, 0.15); border-radius: var(--border-radius-sm); padding: 16px; text-align: center; color: var(--color-danger); font-weight: 600;">
                        ❌ This order was cancelled.
                    </div>
                <?php else: ?>
                    <!-- Tracking Timeline Grid -->
                    <div style="display: flex; justify-content: space-between; align-items: center; position: relative; max-width: 600px; margin: 0 auto; padding-bottom: 8px;">
                        
                        <!-- Progress Connector Line Behind circles -->
                        <div style="position: absolute; top: 18px; left: 5%; right: 5%; height: 4px; background-color: var(--color-grey-border); z-index: 1;">
                            <!-- Active part of line -->
                            <?php
                            $lineWidths = [1 => '0%', 2 => '33.3%', 3 => '66.6%', 4 => '100%'];
                            $activeWidth = $lineWidths[$currentStep] ?? '0%';
                            ?>
                            <div style="height: 100%; width: <?= $activeWidth ?>; background-color: var(--color-brand-blue); transition: width 0.6s ease;"></div>
                        </div>

                        <!-- Step 1: Pending -->
                        <div style="text-align: center; z-index: 2; width: 80px;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: 700; font-size: 14px;
                                <?= $currentStep >= 1 ? 'background-color: var(--color-brand-blue); color: var(--color-white);' : 'background-color: var(--color-grey-border); color: var(--color-charcoal-light);' ?>">
                                1
                            </div>
                            <span style="font-size: 11px; font-weight: 600; display: block; color: <?= $currentStep >= 1 ? 'var(--color-brand-blue)' : 'var(--color-charcoal-light)' ?>;">Pending</span>
                        </div>

                        <!-- Step 2: Processing -->
                        <div style="text-align: center; z-index: 2; width: 80px;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: 700; font-size: 14px;
                                <?= $currentStep >= 2 ? 'background-color: var(--color-brand-blue); color: var(--color-white);' : 'background-color: var(--color-grey-border); color: var(--color-charcoal-light);' ?>">
                                2
                            </div>
                            <span style="font-size: 11px; font-weight: 600; display: block; color: <?= $currentStep >= 2 ? 'var(--color-brand-blue)' : 'var(--color-charcoal-light)' ?>;">Processing</span>
                        </div>

                        <!-- Step 3: Shipped -->
                        <div style="text-align: center; z-index: 2; width: 80px;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: 700; font-size: 14px;
                                <?= $currentStep >= 3 ? 'background-color: var(--color-brand-blue); color: var(--color-white);' : 'background-color: var(--color-grey-border); color: var(--color-charcoal-light);' ?>">
                                3
                            </div>
                            <span style="font-size: 11px; font-weight: 600; display: block; color: <?= $currentStep >= 3 ? 'var(--color-brand-blue)' : 'var(--color-charcoal-light)' ?>;">Shipped</span>
                        </div>

                        <!-- Step 4: Delivered -->
                        <div style="text-align: center; z-index: 2; width: 80px;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; font-weight: 700; font-size: 14px;
                                <?= $currentStep >= 4 ? 'background-color: var(--color-brand-blue); color: var(--color-white);' : 'background-color: var(--color-grey-border); color: var(--color-charcoal-light);' ?>">
                                4
                            </div>
                            <span style="font-size: 11px; font-weight: 600; display: block; color: <?= $currentStep >= 4 ? 'var(--color-brand-blue)' : 'var(--color-charcoal-light)' ?>;">Delivered</span>
                        </div>

                    </div>
                <?php endif; ?>
            </div>

            <!-- Two-Column Order Meta Details -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
                
                <!-- Shipping Address Card -->
                <div class="summary-card" style="padding: 20px;">
                    <h4 style="font-family: var(--font-headers); color: var(--color-brand-blue); font-size: 15px; border-bottom: 1px solid var(--color-grey-border); padding-bottom: 8px; margin-bottom: 12px;">Shipping Address Details</h4>
                    <?php if ($address): ?>
                        <div style="font-size: 14px; line-height: 1.6;">
                            <strong style="font-size: 15px; color: var(--color-charcoal);"><?= htmlspecialchars($address['recipient_name']) ?></strong><br>
                            📞 <?= htmlspecialchars($address['phone_number']) ?><br>
                            <span style="color: var(--color-charcoal-light);">
                                <?= htmlspecialchars($address['street_address']) ?><?= !empty($address['building_details']) ? ', ' . htmlspecialchars($address['building_details']) : '' ?><br>
                                <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['governorate']) ?><br>
                                Egypt
                            </span>
                        </div>
                    <?php else: ?>
                        <p style="font-size: 13px; color: var(--color-charcoal-light); font-style: italic;">Shipping details unavailable.</p>
                    <?php endif; ?>
                </div>

                <!-- Payment Details Card -->
                <div class="summary-card" style="padding: 20px;">
                    <h4 style="font-family: var(--font-headers); color: var(--color-brand-blue); font-size: 15px; border-bottom: 1px solid var(--color-grey-border); padding-bottom: 8px; margin-bottom: 12px;">Payment Status Details</h4>
                    <div style="font-size: 14px; line-height: 1.8;">
                        <span>Payment Method: <strong style="text-transform: uppercase;"><?= htmlspecialchars($order['payment_method']) ?></strong></span><br>
                        <span>Payment Status: <strong style="text-transform: uppercase;"><?= htmlspecialchars($order['payment_status']) ?></strong></span><br>
                        
                        <?php if ($order['payment_method'] === 'instapay'): ?>
                            <div style="margin-top: 8px; border-top: 1px dashed var(--color-grey-border); padding-top: 8px;">
                                <?php if (!empty($order['payment_reference'])): ?>
                                    <span>InstaPay Reference: <strong style="color: var(--color-brand-blue);"><?= htmlspecialchars($order['payment_reference']) ?></strong></span>
                                <?php else: ?>
                                    <span style="color: var(--color-danger); font-weight: 600;">⚠️ Reference number missing.</span><br>
                                    <a href="<?= $base ?>/checkout/instapay/<?= $order['id'] ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 11px; display: inline-block; margin-top: 6px; background-color: var(--color-brand-blue); color: var(--color-white); border: none;">Submit Code Now</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Itemized Products List -->
            <div class="summary-card" style="padding: 24px;">
                <h4 style="font-family: var(--font-headers); color: var(--color-brand-blue); font-size: 15px; border-bottom: 1px solid var(--color-grey-border); padding-bottom: 8px; margin-bottom: 16px;">Ordered Items</h4>
                
                <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
                    <?php foreach ($items as $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--color-alabaster);">
                            <div style="display: flex; gap: 12px; align-items: center;">
                                <div style="font-size: 14px;">
                                    <strong style="font-size: 15px; color: var(--color-brand-blue);"><?= htmlspecialchars($item['product_name']) ?></strong><br>
                                    <span style="font-size: 12px; color: var(--color-charcoal-light);">
                                        Size: <strong><?= htmlspecialchars($item['size']) ?></strong> | Color: <strong><?= htmlspecialchars($item['color']) ?></strong>
                                    </span>
                                </div>
                            </div>
                            <div style="text-align: right; font-size: 14px;">
                                <span style="color: var(--color-charcoal-light);"><?= number_format($item['unit_price'], 0) ?> EGP x <?= $item['quantity'] ?></span><br>
                                <strong style="color: var(--color-charcoal); font-size: 15px;"><?= number_format($item['total_price'], 0) ?> EGP</strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Totals Breakdown -->
                <div style="max-width: 320px; margin-left: auto; display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-charcoal-light);">Items Subtotal:</span>
                        <strong><?= number_format($order['subtotal'], 2) ?> EGP</strong>
                    </div>

                    <?php if ($order['discount_amount'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; color: var(--color-success);">
                            <span>Discount Amount:</span>
                            <strong>-<?= number_format($order['discount_amount'], 2) ?> EGP</strong>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-charcoal-light);">Shipping Rate:</span>
                        <strong><?= number_format($order['shipping_fee'], 2) ?> EGP</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--color-charcoal-light); border-top: 1px dashed var(--color-grey-border); padding-top: 8px;">
                        <span>Included 14% VAT:</span>
                        <span><?= number_format($order['tax_amount'], 2) ?> EGP</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 700; border-top: 1px solid var(--color-grey-border); padding-top: 12px; color: var(--color-brand-blue);">
                        <span>Grand Total:</span>
                        <strong><?= number_format($order['total_amount'], 2) ?> EGP</strong>
                    </div>
                </div>

            </div>

        </main>
    </div>
</div>

<style>
/* Optional: extra responsive alignment styling for timeline dots */
@media (max-width: 480px) {
    div[style*="display: flex; justify-content: space-between"] {
        flex-direction: column !important;
        gap: 16px !important;
    }
    div[style*="position: absolute; top: 18px"] {
        display: none !important;
    }
}
</style>
