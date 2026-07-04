<div class="admin-page-header">
    <h2>Variants: <?= htmlspecialchars($product->name) ?></h2>
    <div>
        <a href="<?= $base ?>/admin/products/edit/<?= $product->id ?>" class="btn-admin-secondary">Edit Product</a>
        <a href="<?= $base ?>/admin/products" class="btn-admin-secondary">All Products</a>
    </div>
</div>

<section class="admin-panel">
    <h3 class="admin-panel-title">Add Variant</h3>
    <form action="<?= $base ?>/admin/products/<?= $product->id ?>/variants/create" method="POST" class="admin-form admin-form-inline">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="form-group">
            <label for="size">Size</label>
            <select id="size" name="size" class="form-control" required>
                <?php foreach (['S', 'M', 'L', 'XL', 'XXL'] as $sz): ?>
                    <option value="<?= $sz ?>"><?= $sz ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="color">Color</label>
            <input type="text" id="color" name="color" class="form-control" required placeholder="White">
        </div>
        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" class="form-control" min="0" value="0" required>
        </div>
        <div class="form-group">
            <label for="price_modifier">Price Modifier (EGP)</label>
            <input type="number" id="price_modifier" name="price_modifier" class="form-control" step="0.01" value="0">
        </div>
        <div class="form-group form-group-btn">
            <button type="submit" class="btn-admin-primary">Add Variant</button>
        </div>
    </form>
</section>

<div class="admin-table-wrap" style="margin-top: 24px;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Size</th>
                <th>Color</th>
                <th>Stock</th>
                <th>Price Modifier</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($variants)): ?>
                <tr><td colspan="5" class="admin-empty">No variants yet. Add one above.</td></tr>
            <?php else: ?>
                <?php foreach ($variants as $v): ?>
                    <tr>
                        <td>
                            <form action="<?= $base ?>/admin/products/<?= $product->id ?>/variants/edit/<?= $v['id'] ?>" method="POST" id="variant-form-<?= $v['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <select name="size" class="form-control" form="variant-form-<?= $v['id'] ?>">
                                    <?php foreach (['S', 'M', 'L', 'XL', 'XXL'] as $sz): ?>
                                        <option value="<?= $sz ?>" <?= $v['size'] === $sz ? 'selected' : '' ?>><?= $sz ?></option>
                                    <?php endforeach; ?>
                                </select>
                        </td>
                        <td>
                                <input type="text" name="color" class="form-control" value="<?= htmlspecialchars($v['color']) ?>" required form="variant-form-<?= $v['id'] ?>">
                        </td>
                        <td>
                                <input type="number" name="stock" class="form-control" min="0" value="<?= (int) $v['stock'] ?>" required form="variant-form-<?= $v['id'] ?>">
                        </td>
                        <td>
                                <input type="number" name="price_modifier" class="form-control" step="0.01" value="<?= htmlspecialchars($v['price_modifier']) ?>" form="variant-form-<?= $v['id'] ?>">
                            </form>
                        </td>
                        <td class="admin-actions">
                            <button type="submit" form="variant-form-<?= $v['id'] ?>" class="btn-admin-sm">Save</button>
                            <form action="<?= $base ?>/admin/products/<?= $product->id ?>/variants/delete/<?= $v['id'] ?>" method="POST" class="inline-form" onsubmit="return confirm('Delete this variant?');">
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
