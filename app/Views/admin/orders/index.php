<div class="admin-page-header">
    <div>
        <h2>Order Management</h2>
        <?php if ($pending_count > 0): ?>
            <p class="admin-pending-alert">
                <span class="badge badge-pending"><?= (int) $pending_count ?> Pending</span>
                awaiting approval
            </p>
        <?php endif; ?>
    </div>
    <div class="admin-filters">
        <a href="<?= $base ?>/admin/orders" class="filter-pill <?= $current_status === '' ? 'active' : '' ?>">All</a>
        <?php foreach ($statuses as $st): ?>
            <a href="<?= $base ?>/admin/orders?status=<?= $st ?>" class="filter-pill <?= $current_status === $st ? 'active' : '' ?> <?= $st === 'pending' ? 'filter-pill-pending' : '' ?>">
                <?= ucfirst($st) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="admin-table-wrap">
    <table class="admin-table" id="admin-orders-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Pay Status</th>
                <th>Order Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="8" class="admin-empty">No orders found.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr class="order-row <?= $order['status'] === 'pending' ? 'row-pending' : '' ?>" data-order-id="<?= $order['id'] ?>">
                        <td>
                            <a href="<?= $base ?>/admin/orders/<?= $order['id'] ?>" class="admin-link"><?= htmlspecialchars($order['order_number']) ?></a>
                        </td>
                        <td>
                            <?= htmlspecialchars($order['customer_name']) ?><br>
                            <small><?= htmlspecialchars($order['customer_email']) ?></small>
                        </td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td><strong><?= number_format($order['total_amount'], 2) ?> EGP</strong></td>
                        <td><?= strtoupper(htmlspecialchars($order['payment_method'])) ?></td>
                        <td><span class="badge badge-pay-<?= htmlspecialchars($order['payment_status']) ?>"><?= ucfirst(htmlspecialchars($order['payment_status'])) ?></span></td>
                        <td class="order-status-cell">
                            <span class="badge badge-<?= htmlspecialchars($order['status']) ?>"><?= ucfirst(htmlspecialchars($order['status'])) ?></span>
                        </td>
                        <td class="admin-actions order-actions-cell">
                            <?php if ($order['status'] === 'pending'): ?>
                                <button type="button" class="btn-admin-sm btn-admin-success btn-accept-order"
                                        data-order-id="<?= $order['id'] ?>"
                                        data-order-number="<?= htmlspecialchars($order['order_number']) ?>">
                                    Accept
                                </button>
                                <button type="button" class="btn-admin-sm btn-admin-danger btn-reject-order"
                                        data-order-id="<?= $order['id'] ?>"
                                        data-order-number="<?= htmlspecialchars($order['order_number']) ?>">
                                    Reject
                                </button>
                            <?php endif; ?>
                            <a href="<?= $base ?>/admin/orders/<?= $order['id'] ?>" class="btn-admin-sm">Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Confirmation Modal -->
<div class="admin-modal-backdrop" id="order-action-modal" hidden>
    <div class="admin-modal-box" role="dialog" aria-modal="true" aria-labelledby="order-modal-title">
        <div class="admin-modal-header">
            <h3 id="order-modal-title">Confirm Action</h3>
            <button type="button" class="admin-modal-close" id="order-modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="admin-modal-body">
            <p id="order-modal-message"></p>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn-admin-secondary" id="order-modal-cancel">Cancel</button>
            <button type="button" class="btn-admin-primary" id="order-modal-confirm">
                <span class="btn-text">Confirm</span>
                <span class="btn-loading" hidden>Processing...</span>
            </button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="admin-toast-container" id="admin-toast-container" aria-live="polite"></div>

<input type="hidden" id="admin-csrf-token" value="<?= htmlspecialchars($csrf_token) ?>">
<input type="hidden" id="admin-base-path" value="<?= htmlspecialchars($base) ?>">

<script src="<?= $base ?>/js/admin-orders.js"></script>
