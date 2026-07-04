<div class="admin-page-header">
    <h2>Categories</h2>
    <a href="<?= $base ?>/admin/categories/create" class="btn-admin-primary">Add Category</a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Products</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
                <tr><td colspan="4" class="admin-empty">No categories found.</td></tr>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['name']) ?></td>
                        <td><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                        <td><?= (int) $cat['product_count'] ?></td>
                        <td class="admin-actions">
                            <a href="<?= $base ?>/admin/categories/edit/<?= $cat['id'] ?>" class="btn-admin-sm">Edit</a>
                            <form action="<?= $base ?>/admin/categories/delete/<?= $cat['id'] ?>" method="POST" class="inline-form" onsubmit="return confirm('Delete this category?');">
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
