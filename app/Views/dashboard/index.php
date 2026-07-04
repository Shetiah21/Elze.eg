<!-- Overview Stat Cards -->
<div class="dash-stats-grid">
    <div class="dash-stat-card">
        <div class="dash-stat-icon orders" aria-hidden="true">📦</div>
        <div class="dash-stat-value"><?= (int) $stats['total_orders'] ?></div>
        <div class="dash-stat-label">Total Orders</div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon pending" aria-hidden="true">⏳</div>
        <div class="dash-stat-value"><?= (int) $stats['pending_orders'] ?></div>
        <div class="dash-stat-label">Pending Orders</div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon completed" aria-hidden="true">✓</div>
        <div class="dash-stat-value"><?= (int) $stats['completed_orders'] ?></div>
        <div class="dash-stat-label">Completed Orders</div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon addresses" aria-hidden="true">📍</div>
        <div class="dash-stat-value"><?= (int) $stats['saved_addresses'] ?></div>
        <div class="dash-stat-label">Saved Addresses</div>
    </div>
</div>

<!-- Recent Orders -->
<section class="dash-panel" aria-labelledby="recent-orders-title">
    <div class="dash-panel-header">
        <h2 class="dash-panel-title" id="recent-orders-title">Recent Orders</h2>
        <a href="<?= $base ?>/dashboard/orders" class="dash-btn dash-btn-ghost dash-btn-sm">View All</a>
    </div>

    <?php if (empty($recent_orders)): ?>
        <div class="dash-empty">
            <div class="dash-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="1.5" fill="none"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <h3>No orders yet</h3>
            <p>Start exploring our premium collection and place your first order.</p>
            <a href="<?= $base ?>/products" class="dash-btn dash-btn-primary">Shop Collection</a>
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
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td><a href="<?= $base ?>/dashboard/orders/<?= $order['id'] ?>" class="dash-table-link"><?= htmlspecialchars($order['order_number']) ?></a></td>
                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                            <td><?php $status = $order['status']; include __DIR__ . '/_status_badge.php'; ?></td>
                            <td><strong><?= number_format($order['total_amount'], 2) ?> EGP</strong></td>
                            <td><?= strtoupper(htmlspecialchars($order['payment_method'])) ?></td>
                            <td><a href="<?= $base ?>/dashboard/orders/<?= $order['id'] ?>" class="dash-btn dash-btn-sm dash-btn-outline">Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<div class="dash-grid-2">
    <!-- Profile Card -->
    <section class="dash-panel" aria-labelledby="profile-card-title">
        <div class="dash-panel-header">
            <h2 class="dash-panel-title" id="profile-card-title">Profile Information</h2>
            <a href="<?= $base ?>/dashboard/profile" class="dash-btn dash-btn-sm dash-btn-outline">Edit Profile</a>
        </div>
        <div class="dash-profile-grid">
            <div class="dash-info-item">
                <label>Full Name</label>
                <p><?= htmlspecialchars($user['name'] ?? '') ?></p>
            </div>
            <div class="dash-info-item">
                <label>Email</label>
                <p><?= htmlspecialchars($user['email'] ?? '') ?></p>
            </div>
            <div class="dash-info-item">
                <label>Phone Number</label>
                <p><?= $phone ? htmlspecialchars($phone) : '—' ?></p>
            </div>
            <div class="dash-info-item">
                <label>Member Since</label>
                <p><?= $member_since ? date('F Y', strtotime($member_since)) : '—' ?></p>
            </div>
        </div>
    </section>

    <!-- Default Address Preview -->
    <section class="dash-panel" aria-labelledby="address-preview-title">
        <div class="dash-panel-header">
            <h2 class="dash-panel-title" id="address-preview-title">Default Address</h2>
            <a href="<?= $base ?>/dashboard/addresses" class="dash-btn dash-btn-sm dash-btn-outline">Manage</a>
        </div>

        <?php if (!$default_address): ?>
            <div class="dash-empty" style="padding: 32px 16px;">
                <div class="dash-empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="1.5" fill="none"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <h3>No saved address</h3>
                <p>Add a shipping address to speed up checkout.</p>
                <a href="<?= $base ?>/dashboard/addresses" class="dash-btn dash-btn-primary">Add Address</a>
            </div>
        <?php else: ?>
            <div class="dash-address-preview">
                <h4><?= htmlspecialchars($default_address['recipient_name']) ?></h4>
                <p>
                    <?= htmlspecialchars($default_address['street_address']) ?><?= !empty($default_address['building_details']) ? ', ' . htmlspecialchars($default_address['building_details']) : '' ?><br>
                    <?= htmlspecialchars($default_address['city']) ?>, <?= htmlspecialchars($default_address['governorate']) ?><br>
                    📞 <?= htmlspecialchars($default_address['phone_number']) ?>
                </p>
            </div>
            <div style="margin-top: 16px; display: flex; gap: 10px;">
                <a href="<?= $base ?>/dashboard/addresses" class="dash-btn dash-btn-sm dash-btn-ghost">Edit</a>
                <a href="<?= $base ?>/dashboard/addresses" class="dash-btn dash-btn-sm dash-btn-primary">Add New Address</a>
            </div>
        <?php endif; ?>
    </section>
</div>
