<div class="admin-page-header">
    <div>
        <a href="<?= $base ?>/admin/orders" class="admin-link" style="font-size: 13px;">← Back to Orders</a>
        <h2 style="margin-top: 8px;">Order <?= htmlspecialchars($order->order_number) ?></h2>
        <span class="badge badge-<?= htmlspecialchars($order->status) ?>" id="detail-status-badge"><?= ucfirst(htmlspecialchars($order->status)) ?></span>
    </div>
    <div class="admin-actions" id="detail-action-buttons">
        <?php if ($order->status === 'pending'): ?>
            <button type="button" class="btn-admin-primary btn-accept-order"
                    data-order-id="<?= $order->id ?>"
                    data-order-number="<?= htmlspecialchars($order->order_number) ?>">Accept Order</button>
            <button type="button" class="btn-admin-sm btn-admin-danger btn-reject-order"
                    data-order-id="<?= $order->id ?>"
                    data-order-number="<?= htmlspecialchars($order->order_number) ?>">Reject Order</button>
        <?php endif; ?>
    </div>
</div>

<div class="admin-grid-2">
    <section class="admin-panel">
        <h3 class="admin-panel-title">Customer Information</h3>
        <dl class="admin-dl">
            <dt>Name</dt><dd><?= htmlspecialchars($customer['name'] ?? '—') ?></dd>
            <dt>Email</dt><dd><?= htmlspecialchars($customer['email'] ?? '—') ?></dd>
        </dl>

        <h3 class="admin-panel-title" style="margin-top: 24px;">Shipping Address</h3>
        <?php if ($address): ?>
            <dl class="admin-dl">
                <dt>Recipient</dt><dd><?= htmlspecialchars($address['recipient_name']) ?></dd>
                <dt>Phone</dt><dd><?= htmlspecialchars($address['phone_number']) ?></dd>
                <dt>Governorate</dt><dd><?= htmlspecialchars($address['governorate']) ?></dd>
                <dt>City</dt><dd><?= htmlspecialchars($address['city']) ?></dd>
                <dt>Street</dt><dd><?= htmlspecialchars($address['street_address']) ?></dd>
                <?php if (!empty($address['building_details'])): ?>
                    <dt>Building</dt><dd><?= htmlspecialchars($address['building_details']) ?></dd>
                <?php endif; ?>
            </dl>
        <?php else: ?>
            <p class="admin-empty">Address not found.</p>
        <?php endif; ?>

        <?php if (!empty($order->notes) && trim(str_replace('[INV_DEDUCTED]', '', $order->notes)) !== ''): ?>
            <h3 class="admin-panel-title" style="margin-top: 24px;">Order Notes</h3>
            <p style="font-size: 14px; color: var(--color-charcoal-light);"><?= htmlspecialchars(trim(str_replace('[INV_DEDUCTED]', '', $order->notes))) ?></p>
        <?php endif; ?>
    </section>

    <section class="admin-panel">
        <h3 class="admin-panel-title">Update Order Status</h3>
        <form action="<?= $base ?>/admin/orders/<?= $order->id ?>" method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="status">Order Status</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="<?= htmlspecialchars($order->status) ?>" selected><?= ucfirst(htmlspecialchars($order->status)) ?> (current)</option>
                    <?php foreach ($allowed_transitions as $st): ?>
                        <option value="<?= htmlspecialchars($st) ?>"><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($allowed_transitions)): ?>
                    <small class="form-hint">This order is in a final state and cannot be changed.</small>
                <?php else: ?>
                    <small class="form-hint">Only valid status transitions are listed.</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="tracking_number">Tracking Number</label>
                <input type="text" id="tracking_number" name="tracking_number" class="form-control"
                       value="<?= htmlspecialchars($order->tracking_number ?? '') ?>" placeholder="Optional tracking ID">
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="btn-admin-primary" <?= empty($allowed_transitions) ? 'disabled' : '' ?>>Save Changes</button>
            </div>
        </form>

        <dl class="admin-dl" style="margin-top: 24px;">
            <dt>Payment Method</dt><dd><?= strtoupper(htmlspecialchars($order->payment_method)) ?></dd>
            <dt>Payment Status</dt><dd><span class="badge badge-pay-<?= htmlspecialchars($order->payment_status) ?>"><?= ucfirst(htmlspecialchars($order->payment_status)) ?></span></dd>
            <dt>Subtotal</dt><dd><?= number_format($order->subtotal, 2) ?> EGP</dd>
            <?php if ($order->discount_amount > 0): ?>
                <dt>Discount</dt><dd>-<?= number_format($order->discount_amount, 2) ?> EGP</dd>
            <?php endif; ?>
            <dt>Shipping</dt><dd><?= number_format($order->shipping_fee, 2) ?> EGP</dd>
            <dt>VAT (14%)</dt><dd><?= number_format($order->tax_amount, 2) ?> EGP</dd>
            <dt><strong>Grand Total</strong></dt><dd><strong><?= number_format($order->total_amount, 2) ?> EGP</strong></dd>
        </dl>
    </section>
</div>

<section class="admin-panel" style="margin-top: 24px;">
    <h3 class="admin-panel-title">Ordered Products</h3>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Size</th>
                    <th>Color</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars($item['size'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($item['color'] ?? '—') ?></td>
                        <td><?= (int) $item['quantity'] ?></td>
                        <td><?= number_format($item['unit_price'], 2) ?> EGP</td>
                        <td><?= number_format($item['total_price'], 2) ?> EGP</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="admin-modal-backdrop" id="order-action-modal" hidden>
    <div class="admin-modal-box" role="dialog" aria-modal="true" aria-labelledby="order-modal-title">
        <div class="admin-modal-header">
            <h3 id="order-modal-title">Confirm Action</h3>
            <button type="button" class="admin-modal-close" id="order-modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="admin-modal-body"><p id="order-modal-message"></p></div>
        <div class="admin-modal-footer">
            <button type="button" class="btn-admin-secondary" id="order-modal-cancel">Cancel</button>
            <button type="button" class="btn-admin-primary" id="order-modal-confirm">
                <span class="btn-text">Confirm</span>
                <span class="btn-loading" hidden>Processing...</span>
            </button>
        </div>
    </div>
</div>
<div class="admin-toast-container" id="admin-toast-container" aria-live="polite"></div>
<input type="hidden" id="admin-csrf-token" value="<?= htmlspecialchars($csrf_token) ?>">
<input type="hidden" id="admin-base-path" value="<?= htmlspecialchars($base) ?>">
<script src="<?= $base ?>/js/admin-orders.js"></script>
