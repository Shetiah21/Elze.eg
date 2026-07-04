<?php
$status = strtolower($order['status']);
$steps = ['pending' => 1, 'processing' => 2, 'shipped' => 3, 'delivered' => 4];
$currentStep = $steps[$status] ?? 1;
if ($status === 'cancelled') { $currentStep = -1; }
$lineWidths = [1 => '0%', 2 => '33.3%', 3 => '66.6%', 4 => '100%'];
$activeWidth = $lineWidths[$currentStep] ?? '0%';
$stepLabels = ['Pending', 'Processing', 'Shipped', 'Delivered'];
?>

<div class="dash-panel-header" style="margin-bottom: 20px;">
    <div>
        <a href="<?= $base ?>/dashboard/orders" class="dash-table-link" style="font-size: 13px; font-weight: 500;">← Back to Orders</a>
        <h2 class="dash-panel-title" style="margin-top: 8px;">Order #<?= htmlspecialchars($order['order_number']) ?></h2>
    </div>
    <a href="<?= $base ?>/orders/receipt/<?= $order['id'] ?>" target="_blank" class="dash-btn dash-btn-outline dash-btn-sm">Download Receipt</a>
</div>

<!-- Tracking Timeline -->
<section class="dash-panel" aria-label="Delivery tracking">
    <h3 class="dash-panel-title" style="text-align: center; margin-bottom: 24px;">Delivery Tracking</h3>

    <?php if ($currentStep === -1): ?>
        <div class="dash-cancelled-banner">This order was cancelled.</div>
    <?php else: ?>
        <div class="dash-timeline">
            <div class="dash-timeline-track"><div class="dash-timeline-fill" style="width: <?= $activeWidth ?>"></div></div>
            <?php foreach ($stepLabels as $i => $label): ?>
                <?php $stepNum = $i + 1; $done = $currentStep >= $stepNum; ?>
                <div class="dash-timeline-step">
                    <div class="dash-timeline-dot <?= $done ? 'done' : 'todo' ?>"><?= $stepNum ?></div>
                    <span class="dash-timeline-label <?= $done ? 'done' : 'todo' ?>"><?= $label ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<div class="dash-grid-2">
    <section class="dash-panel">
        <h3 class="dash-panel-title">Shipping Address</h3>
        <?php if ($address): ?>
            <div class="dash-address-preview" style="border: none; padding: 0; background: none;">
                <h4><?= htmlspecialchars($address['recipient_name']) ?></h4>
                <p>
                    📞 <?= htmlspecialchars($address['phone_number']) ?><br>
                    <?= htmlspecialchars($address['street_address']) ?><?= !empty($address['building_details']) ? ', ' . htmlspecialchars($address['building_details']) : '' ?><br>
                    <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['governorate']) ?>
                </p>
            </div>
        <?php else: ?>
            <p style="color: var(--color-charcoal-light); font-size: 14px;">Shipping details unavailable.</p>
        <?php endif; ?>
    </section>

    <section class="dash-panel">
        <h3 class="dash-panel-title">Payment Details</h3>
        <div class="dash-profile-grid">
            <div class="dash-info-item">
                <label>Method</label>
                <p><?= strtoupper(htmlspecialchars($order['payment_method'])) ?></p>
            </div>
            <div class="dash-info-item">
                <label>Status</label>
                <p><?= ucfirst(htmlspecialchars($order['payment_status'])) ?></p>
            </div>
            <?php if ($order['payment_method'] === 'instapay' && empty($order['payment_reference'])): ?>
                <div class="dash-info-item" style="grid-column: 1 / -1;">
                    <a href="<?= $base ?>/checkout/instapay/<?= $order['id'] ?>" class="dash-btn dash-btn-sm dash-btn-primary">Submit Payment Reference</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<section class="dash-panel">
    <h3 class="dash-panel-title">Ordered Items</h3>
    <div class="dash-order-items">
        <?php foreach ($items as $item): ?>
            <div class="dash-order-item">
                <div>
                    <strong style="color: var(--dash-navy);"><?= htmlspecialchars($item['product_name']) ?></strong><br>
                    <span style="font-size: 12px; color: var(--color-charcoal-light);">
                        Size: <?= htmlspecialchars($item['size'] ?? '—') ?> · Color: <?= htmlspecialchars($item['color'] ?? '—') ?>
                    </span>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 13px; color: var(--color-charcoal-light);"><?= number_format($item['unit_price'], 0) ?> EGP × <?= (int) $item['quantity'] ?></span><br>
                    <strong><?= number_format($item['total_price'], 0) ?> EGP</strong>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="dash-order-totals">
        <div style="display: flex; justify-content: space-between;"><span>Subtotal</span><strong><?= number_format($order['subtotal'], 2) ?> EGP</strong></div>
        <?php if ($order['discount_amount'] > 0): ?>
            <div style="display: flex; justify-content: space-between; color: var(--color-success);"><span>Discount</span><strong>-<?= number_format($order['discount_amount'], 2) ?> EGP</strong></div>
        <?php endif; ?>
        <div style="display: flex; justify-content: space-between;"><span>Shipping</span><strong><?= number_format($order['shipping_fee'], 2) ?> EGP</strong></div>
        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--color-charcoal-light);"><span>Included VAT (14%)</span><span><?= number_format($order['tax_amount'], 2) ?> EGP</span></div>
        <div class="grand"><span>Grand Total</span><strong><?= number_format($order['total_amount'], 2) ?> EGP</strong></div>
    </div>
</section>
