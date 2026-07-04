<section class="dash-panel" aria-labelledby="orders-page-title">
    <div class="dash-panel-header">
        <h2 class="dash-panel-title" id="orders-page-title">My Orders</h2>
    </div>

    <?php if (empty($orders)): ?>
        <div class="dash-empty">
            <div class="dash-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="1.5" fill="none"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <h3>No orders found</h3>
            <p>You haven't placed any orders yet. Discover our premium Egyptian cotton collection.</p>
            <a href="<?= $base ?>/products" class="dash-btn dash-btn-primary">Shop Catalog</a>
        </div>
    <?php else: ?>
        <div class="dash-table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th scope="col">Order Number</th>
                        <th scope="col">Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Total</th>
                        <th scope="col">Payment</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><a href="<?= $base ?>/dashboard/orders/<?= $order['id'] ?>" class="dash-table-link"><?= htmlspecialchars($order['order_number']) ?></a></td>
                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                            <td><?php $status = $order['status']; include __DIR__ . '/_status_badge.php'; ?></td>
                            <td><strong><?= number_format($order['total_amount'], 2) ?> EGP</strong></td>
                            <td><?= strtoupper(htmlspecialchars($order['payment_method'])) ?></td>
                            <td>
                                <a href="<?= $base ?>/dashboard/orders/<?= $order['id'] ?>" class="dash-btn dash-btn-sm dash-btn-outline">Details</a>
                                <a href="<?= $base ?>/orders/receipt/<?= $order['id'] ?>" target="_blank" class="dash-btn dash-btn-sm dash-btn-ghost" style="margin-left: 6px;">Receipt</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
