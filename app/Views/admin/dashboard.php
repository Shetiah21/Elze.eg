<div class="metrics-grid">
    <div class="metric-card">
        <span class="metric-label">Total Sales</span>
        <span class="metric-value"><?= number_format($metrics['total_sales'], 2) ?> <small>EGP</small></span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Completed Orders</span>
        <span class="metric-value"><?= (int) $metrics['completed_orders'] ?></span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Total Users</span>
        <span class="metric-value"><?= (int) $metrics['total_users'] ?></span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Active Coupons</span>
        <span class="metric-value"><?= (int) $metrics['active_coupons'] ?></span>
    </div>
</div>

<div class="admin-grid-2">
    <section class="admin-panel">
        <h2 class="admin-panel-title">Orders by Status</h2>
        <?php if (empty($order_breakdown)): ?>
            <p class="admin-empty">No orders yet.</p>
        <?php else: ?>
            <?php
            $maxCount = max(array_column($order_breakdown, 'count')) ?: 1;
            foreach ($order_breakdown as $row):
            ?>
                <div class="status-bar-row">
                    <span class="status-bar-label"><?= htmlspecialchars(ucfirst($row['status'])) ?></span>
                    <div class="status-bar-track">
                        <div class="status-bar-fill status-<?= htmlspecialchars($row['status']) ?>"
                             style="width: <?= round(($row['count'] / $maxCount) * 100) ?>%"></div>
                    </div>
                    <span class="status-bar-count"><?= (int) $row['count'] ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="admin-panel">
        <h2 class="admin-panel-title">Recent Orders</h2>
        <?php if (empty($recent_orders)): ?>
            <p class="admin-empty">No recent orders.</p>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>
                                    <a href="<?= $base ?>/admin/orders/<?= $order['id'] ?>" class="admin-link">
                                        <?= htmlspecialchars($order['order_number']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= number_format($order['total_amount'], 2) ?> EGP</td>
                                <td><span class="badge badge-<?= htmlspecialchars($order['status']) ?>"><?= htmlspecialchars(ucfirst($order['status'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
