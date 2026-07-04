<div class="admin-page-header">
    <h2>Products</h2>
    <a href="<?= $base ?>/admin/products/create" class="btn-admin-primary">Add Product</a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Variants</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="7" class="admin-empty">No products found.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if (!empty($product['primary_image'])): ?>
                                <img src="<?= $base . htmlspecialchars($product['primary_image']) ?>" alt="" class="admin-thumb">
                            <?php else: ?>
                                <span class="admin-no-img">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['category_name'] ?? '—') ?></td>
                        <td><?= number_format($product['base_price'], 2) ?> EGP</td>
                        <td><?= (int) $product['variant_count'] ?></td>
                        <td>
                            <span class="badge <?= $product['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="admin-actions">
                            <a href="<?= $base ?>/admin/products/edit/<?= $product['id'] ?>" class="btn-admin-sm">Edit</a>
                            <a href="<?= $base ?>/admin/products/<?= $product['id'] ?>/variants" class="btn-admin-sm">Variants</a>
                            <form action="<?= $base ?>/admin/products/delete/<?= $product['id'] ?>" method="POST" class="inline-form" onsubmit="return confirm('Delete this product and all variants?');">
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
