<div class="admin-page-header">
    <h2>Coupons</h2>
    <a href="<?= $base ?>/admin/coupons/create" class="btn-admin-primary">Create Coupon</a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Discount</th>
                <th>Min Order</th>
                <th>Uses</th>
                <th>Expires</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($coupons)): ?>
                <tr><td colspan="7" class="admin-empty">No coupons yet.</td></tr>
            <?php else: ?>
                <?php foreach ($coupons as $coupon): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($coupon['code']) ?></code></td>
                        <td>
                            <?php if ($coupon['discount_type'] === 'percent'): ?>
                                <?= number_format($coupon['discount_value'], 0) ?>%
                            <?php else: ?>
                                <?= number_format($coupon['discount_value'], 2) ?> EGP
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($coupon['min_order_amount'], 2) ?> EGP</td>
                        <td><?= (int) $coupon['uses_count'] ?> / <?= (int) $coupon['max_uses'] ?></td>
                        <td><?= $coupon['expires_at'] ? date('M d, Y', strtotime($coupon['expires_at'])) : '—' ?></td>
                        <td>
                            <span class="badge <?= $coupon['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                <?= $coupon['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="admin-actions">
                            <a href="<?= $base ?>/admin/coupons/edit/<?= $coupon['id'] ?>" class="btn-admin-sm">Edit</a>
                            <form action="<?= $base ?>/admin/coupons/toggle/<?= $coupon['id'] ?>" method="POST" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <button type="submit" class="btn-admin-sm"><?= $coupon['is_active'] ? 'Disable' : 'Enable' ?></button>
                            </form>
                            <form action="<?= $base ?>/admin/coupons/delete/<?= $coupon['id'] ?>" method="POST" class="inline-form" onsubmit="return confirm('Delete this coupon?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <button type="submit" class="btn-admin-sm btn-admin-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
