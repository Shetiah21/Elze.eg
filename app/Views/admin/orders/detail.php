<div class="admin-page-header">
    <h2>Order <?= htmlspecialchars($order->order_number) ?></h2>
    <a href="<?= $base ?>/admin/orders" class="btn-admin-secondary">Back to Orders</a>
</div>

<div class="admin-grid-2">
    <section class="admin-panel">
        <h3 class="admin-panel-title">Customer</h3>
        <dl class="admin-dl">
            <dt>Name</dt><dd><?= htmlspecialchars($customer['name'] ?? '—') ?></dd>
            <dt>Email</dt><dd><?= htmlspecialchars($customer['email'] ?? '—') ?></dd>
        </dl>

        <h3 class="admin-panel-title" style="margin-top: 24px;">Shipping Address</h3>
        <?php if ($address): ?>
            <dl class="admin-dl">
                <dt>Recipient</dt><dd><?= htmlspecialchars($address['recipient_name']) ?></dd>
                <dt>Phone</dt><dd><?= htmlspecialchars($address['phone_number']) ?></dd>
                <dt>Location</dt><dd><?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['governorate']) ?></dd>
                <dt>Street</dt><dd><?= htmlspecialchars($address['street_address']) ?></dd>
                <?php if (!empty($address['building_details'])): ?>
                    <dt>Building</dt><dd><?= htmlspecialchars($address['building_details']) ?></dd>
                <?php endif; ?>
            </dl>
        <?php else: ?>
            <p class="admin-empty">Address not found.</p>
        <?php endif; ?>
    </section>

    <section class="admin-panel">
        <h3 class="admin-panel-title">Update Order</h3>
        <form action="<?= $base ?>/admin/orders/<?= $order->id ?>" method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="status">Order Status</label>
                <select id="status" name="status" class="form-control" required>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?= $st ?>" <?= $order->status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="tracking_number">Tracking Number</label>
                <input type="text" id="tracking_number" name="tracking_number" class="form-control"
                       value="<?= htmlspecialchars($order->tracking_number ?? '') ?>" placeholder="Optional tracking ID">
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="btn-admin-primary">Save Changes</button>
            </div>
        </form>

        <dl class="admin-dl" style="margin-top: 24px;">
            <dt>Payment Method</dt><dd><?= strtoupper(htmlspecialchars($order->payment_method)) ?></dd>
            <dt>Payment Status</dt><dd><?= ucfirst(htmlspecialchars($order->payment_status)) ?></dd>
            <dt>Subtotal</dt><dd><?= number_format($order->subtotal, 2) ?> EGP</dd>
            <dt>Shipping</dt><dd><?= number_format($order->shipping_fee, 2) ?> EGP</dd>
            <?php if ($order->discount_amount > 0): ?>
                <dt>Discount</dt><dd>-<?= number_format($order->discount_amount, 2) ?> EGP</dd>
            <?php endif; ?>
            <dt><strong>Grand Total</strong></dt><dd><strong><?= number_format($order->total_amount, 2) ?> EGP</strong></dd>
        </dl>
    </section>
</div>

<section class="admin-panel" style="margin-top: 24px;">
    <h3 class="admin-panel-title">Order Items</h3>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars(($item['size'] ?? '') . ' / ' . ($item['color'] ?? '')) ?></td>
                        <td><?= (int) $item['quantity'] ?></td>
                        <td><?= number_format($item['unit_price'], 2) ?> EGP</td>
                        <td><?= number_format($item['total_price'], 2) ?> EGP</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
