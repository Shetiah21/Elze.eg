<div class="admin-page-header">
    <h2>Orders</h2>
    <div class="admin-filters">
        <a href="<?= $base ?>/admin/orders" class="filter-pill <?= $current_status === '' ? 'active' : '' ?>">All</a>
        <?php foreach ($statuses as $st): ?>
            <a href="<?= $base ?>/admin/orders?status=<?= $st ?>" class="filter-pill <?= $current_status === $st ? 'active' : '' ?>">
                <?= ucfirst($st) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Payment</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="7" class="admin-empty">No orders found.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><a href="<?= $base ?>/admin/orders/<?= $order['id'] ?>" class="admin-link"><?= htmlspecialchars($order['order_number']) ?></a></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?><br><small><?= htmlspecialchars($order['customer_email']) ?></small></td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td><?= strtoupper(htmlspecialchars($order['payment_method'])) ?></td>
                        <td><?= number_format($order['total_amount'], 2) ?> EGP</td>
                        <td><span class="badge badge-<?= htmlspecialchars($order['status']) ?>"><?= ucfirst(htmlspecialchars($order['status'])) ?></span></td>
                        <td><a href="<?= $base ?>/admin/orders/<?= $order['id'] ?>" class="btn-admin-sm">View</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
