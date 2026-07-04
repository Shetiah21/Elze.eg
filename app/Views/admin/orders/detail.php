<div class="admin-page-header">
    <div>
        <a href="<?= $base ?>/admin/orders" class="admin-link" style="font-size: 13px;">← Back to Orders</a>
        <h2 style="margin-top: 8px;">Order <?= htmlspecialchars($order->order_number) ?></h2>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-top: 6px;">
            <span class="badge badge-<?= htmlspecialchars($order->status) ?>" id="detail-status-badge"><?= ucfirst(htmlspecialchars($order->status)) ?></span>
            <span class="badge badge-pay-<?= htmlspecialchars($order->payment_status) ?>" id="detail-pay-status-badge">
                <?= ucfirst(str_replace('_', ' ', htmlspecialchars($order->payment_status))) ?>
            </span>
            <?php if ($order->payment_method === 'instapay'): ?>
                <span class="instapay-badge-sm">⚡ InstaPay</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="admin-actions" id="detail-action-buttons">
        <?php if ($order->payment_method === 'instapay' && $order->payment_status === 'pending_verification'): ?>
            <!-- InstaPay Payment Verification Buttons -->
            <button type="button"
                    class="btn-admin-primary btn-verify-payment"
                    data-order-id="<?= $order->id ?>"
                    data-order-number="<?= htmlspecialchars($order->order_number) ?>"
                    data-reference="<?= htmlspecialchars($order->payment_reference ?? '') ?>">
                ✓ Verify Payment
            </button>
            <button type="button"
                    class="btn-admin-sm btn-admin-danger btn-reject-payment"
                    data-order-id="<?= $order->id ?>"
                    data-order-number="<?= htmlspecialchars($order->order_number) ?>"
                    data-reference="<?= htmlspecialchars($order->payment_reference ?? '') ?>">
                ✕ Reject Payment
            </button>
        <?php elseif ($order->status === 'pending'): ?>
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
        <!-- ── InstaPay Payment Panel ─────────────────────────────── -->
        <?php if ($order->payment_method === 'instapay'): ?>
        <div class="instapay-admin-panel" style="background: linear-gradient(135deg, #f8f7ff 0%, #f0effe 100%); border: 1.5px solid rgba(15,12,59,0.12); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                <span style="font-size: 22px;">⚡</span>
                <h3 style="margin: 0; font-family: var(--font-headers); color: var(--color-brand-blue); font-size: 16px;">InstaPay Payment Details</h3>
            </div>
            <dl class="admin-dl">
                <dt>Payment Method</dt>
                <dd><span class="instapay-badge-sm" style="font-size: 12px;">⚡ InstaPay</span></dd>

                <dt>Payment Status</dt>
                <dd>
                    <span class="badge badge-pay-<?= htmlspecialchars($order->payment_status) ?>" id="detail-pay-badge-inner">
                        <?= ucfirst(str_replace('_', ' ', htmlspecialchars($order->payment_status))) ?>
                    </span>
                </dd>

                <dt>Transaction Reference</dt>
                <dd>
                    <?php if (!empty($order->payment_reference)): ?>
                        <code class="ref-code" style="font-size: 14px; letter-spacing: 0.05em;"><?= htmlspecialchars($order->payment_reference) ?></code>
                    <?php else: ?>
                        <span style="color: var(--color-charcoal-light); font-size: 12px; font-style: italic;">Not yet submitted</span>
                    <?php endif; ?>
                </dd>

                <?php if (!empty($order->payment_date)): ?>
                <dt>Payment Submitted</dt>
                <dd style="font-size: 13px;"><?= date('M d, Y H:i', strtotime($order->payment_date)) ?></dd>
                <?php endif; ?>

                <?php if (!empty($order->payment_verified_at)): ?>
                <dt>Verified At</dt>
                <dd style="font-size: 13px; color: var(--color-success);"><?= date('M d, Y H:i', strtotime($order->payment_verified_at)) ?></dd>
                <?php endif; ?>

                <?php if (!empty($order->verified_by)): ?>
                <dt>Verified By</dt>
                <dd style="font-size: 13px;">Admin ID #<?= (int)$order->verified_by ?></dd>
                <?php endif; ?>
            </dl>

            <?php if ($order->payment_status === 'pending_verification'): ?>
                <div style="margin-top: 14px; padding: 12px 14px; background: #fff8e1; border: 1px solid #ffd54f; border-radius: 8px; font-size: 12px; color: #7a6000;">
                    ⏳ <strong>Action Required:</strong> Verify the transaction reference <strong><?= htmlspecialchars($order->payment_reference ?? '') ?></strong>
                    against your InstaPay merchant dashboard before approving.
                </div>
                <div style="display: flex; gap: 10px; margin-top: 14px;" id="instapay-verify-actions">
                    <button type="button"
                            class="btn-admin-primary btn-verify-payment"
                            style="flex: 1; justify-content: center;"
                            data-order-id="<?= $order->id ?>"
                            data-order-number="<?= htmlspecialchars($order->order_number) ?>"
                            data-reference="<?= htmlspecialchars($order->payment_reference ?? '') ?>">
                        ✓ Verify Payment
                    </button>
                    <button type="button"
                            class="btn-admin-sm btn-admin-danger btn-reject-payment"
                            data-order-id="<?= $order->id ?>"
                            data-order-number="<?= htmlspecialchars($order->order_number) ?>"
                            data-reference="<?= htmlspecialchars($order->payment_reference ?? '') ?>">
                        ✕ Reject
                    </button>
                </div>
            <?php elseif ($order->payment_status === 'paid'): ?>
                <div style="margin-top: 14px; padding: 10px 14px; background: #e8f5e9; border-radius: 8px; font-size: 12px; color: #2e7d32; display: flex; align-items: center; gap: 8px;">
                    ✅ <strong>Payment verified and confirmed.</strong>
                </div>
            <?php elseif ($order->payment_status === 'failed'): ?>
                <div style="margin-top: 14px; padding: 10px 14px; background: #fce4ec; border-radius: 8px; font-size: 12px; color: #b71c1c; display: flex; align-items: center; gap: 8px;">
                    ❌ <strong>Payment reference was rejected.</strong>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ── Order Status Update ─────────────────────────────────── -->
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

        <?php if ($order->payment_method !== 'instapay'): ?>
        <dl class="admin-dl" style="margin-top: 24px;">
            <dt>Payment Method</dt><dd><?= strtoupper(htmlspecialchars($order->payment_method)) ?></dd>
            <dt>Payment Status</dt><dd><span class="badge badge-pay-<?= htmlspecialchars($order->payment_status) ?>"><?= ucfirst(htmlspecialchars($order->payment_status)) ?></span></dd>
        </dl>
        <?php endif; ?>

        <dl class="admin-dl" style="margin-top: 16px;">
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
                <span class="btn-loading" hidden>Processing…</span>
            </button>
        </div>
    </div>
</div>
<div class="admin-toast-container" id="admin-toast-container" aria-live="polite"></div>
<input type="hidden" id="admin-csrf-token" value="<?= htmlspecialchars($csrf_token) ?>">
<input type="hidden" id="admin-base-path" value="<?= htmlspecialchars($base) ?>">
<script src="<?= $base ?>/js/admin-orders.js"></script>
