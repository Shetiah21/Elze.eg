<div class="admin-page-header">
    <h2><?= $coupon ? 'Edit Coupon' : 'Create Coupon' ?></h2>
    <a href="<?= $base ?>/admin/coupons" class="btn-admin-secondary">Back</a>
</div>

<form action="<?= $base ?>/admin/coupons/<?= $coupon ? 'edit/' . $coupon->id : 'create' ?>" method="POST" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <?php if (!$coupon): ?>
    <div class="form-group">
        <label for="code">Coupon Code</label>
        <input type="text" id="code" name="code" class="form-control" required placeholder="SUMMER20">
    </div>
    <?php else: ?>
        <div class="form-group">
            <label>Coupon Code</label>
            <p><code><?= htmlspecialchars($coupon->code) ?></code></p>
        </div>
    <?php endif; ?>

    <div class="form-group-row">
        <div class="form-group">
            <label for="discount_type">Discount Type</label>
            <select id="discount_type" name="discount_type" class="form-control">
                <option value="fixed" <?= (($coupon->discount_type ?? 'fixed') === 'fixed') ? 'selected' : '' ?>>Fixed Amount (EGP)</option>
                <option value="percent" <?= (($coupon->discount_type ?? '') === 'percent') ? 'selected' : '' ?>>Percentage (%)</option>
            </select>
        </div>
        <div class="form-group">
            <label for="discount_value">Discount Value</label>
            <input type="number" id="discount_value" name="discount_value" class="form-control" step="0.01" min="0" required
                   value="<?= htmlspecialchars($coupon->discount_value ?? '0') ?>">
        </div>
    </div>

    <div class="form-group-row">
        <div class="form-group">
            <label for="min_order_amount">Minimum Order (EGP)</label>
            <input type="number" id="min_order_amount" name="min_order_amount" class="form-control" step="0.01" min="0"
                   value="<?= htmlspecialchars($coupon->min_order_amount ?? '0') ?>">
        </div>
        <div class="form-group">
            <label for="max_uses">Max Uses</label>
            <input type="number" id="max_uses" name="max_uses" class="form-control" min="1"
                   value="<?= htmlspecialchars($coupon->max_uses ?? '100') ?>">
        </div>
    </div>

    <div class="form-group-row">
        <div class="form-group">
            <label for="starts_at">Starts At (optional)</label>
            <input type="datetime-local" id="starts_at" name="starts_at" class="form-control"
                   value="<?= $coupon && $coupon->starts_at ? date('Y-m-d\TH:i', strtotime($coupon->starts_at)) : '' ?>">
        </div>
        <div class="form-group">
            <label for="expires_at">Expires At (optional)</label>
            <input type="datetime-local" id="expires_at" name="expires_at" class="form-control"
                   value="<?= $coupon && $coupon->expires_at ? date('Y-m-d\TH:i', strtotime($coupon->expires_at)) : '' ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="form-checkbox-label">
            <input type="checkbox" name="is_active" <?= (!$coupon || $coupon->is_active) ? 'checked' : '' ?>>
            Coupon is active
        </label>
    </div>

    <div class="admin-form-actions">
        <button type="submit" class="btn-admin-primary"><?= $coupon ? 'Update' : 'Create' ?> Coupon</button>
    </div>
</form>
