<div class="admin-page-header">
    <h2><?= $category ? 'Edit Category' : 'Add Category' ?></h2>
    <a href="<?= $base ?>/admin/categories" class="btn-admin-secondary">Back</a>
</div>

<form action="<?= $base ?>/admin/categories/<?= $category ? 'edit/' . $category->id : 'create' ?>" method="POST" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="form-group">
        <label for="name">Category Name</label>
        <input type="text" id="name" name="name" class="form-control" required
               value="<?= htmlspecialchars($category?->name ?? '') ?>" placeholder="T-Shirts">
    </div>

    <div class="admin-form-actions">
        <button type="submit" class="btn-admin-primary"><?= $category ? 'Update' : 'Create' ?> Category</button>
    </div>
</form>
