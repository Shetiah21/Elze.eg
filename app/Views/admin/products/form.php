<div class="admin-page-header">
    <h2><?= $product ? 'Edit Product' : 'Add Product' ?></h2>
    <a href="<?= $base ?>/admin/products" class="btn-admin-secondary">Back</a>
</div>

<form action="<?= $base ?>/admin/products/<?= $product ? 'edit/' . $product->id : 'create' ?>" method="POST" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="form-group">
        <label for="name">Product Name</label>
        <input type="text" id="name" name="name" class="form-control" required
               value="<?= htmlspecialchars($product?->name ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id" class="form-control" required>
            <option value="">Select category</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($product && (int)$product->category_id === (int)$cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" class="form-control" rows="5" required><?= htmlspecialchars($product?->description ?? '') ?></textarea>
    </div>

    <div class="form-group-row">
        <div class="form-group">
            <label for="base_price">Base Price (EGP)</label>
            <input type="number" id="base_price" name="base_price" class="form-control" step="0.01" min="0" required
                   value="<?= htmlspecialchars($product?->base_price ?? '0') ?>">
        </div>
        <div class="form-group">
            <label for="image_path">Primary Image Path</label>
            <input type="text" id="image_path" name="image_path" class="form-control"
                   value="<?= htmlspecialchars($primary_image) ?>" placeholder="/images/products/tee-white.jpg">
            <small class="form-hint">Relative path from public/ (e.g. /images/products/tee.jpg)</small>
        </div>
    </div>

    <div class="form-group">
        <label for="size_chart_details">Size Chart Details (optional)</label>
        <textarea id="size_chart_details" name="size_chart_details" class="form-control" rows="3"><?= htmlspecialchars($product?->size_chart_details ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label class="form-checkbox-label">
            <input type="checkbox" name="is_active" <?= (!$product || $product->is_active) ? 'checked' : '' ?>>
            Product is active (visible in store)
        </label>
    </div>

    <div class="admin-form-actions">
        <button type="submit" class="btn-admin-primary"><?= $product ? 'Update' : 'Create' ?> Product</button>
    </div>
</form>
