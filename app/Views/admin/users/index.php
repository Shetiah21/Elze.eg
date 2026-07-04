<div class="admin-page-header">
    <h2>Users</h2>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Verified</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="7" class="admin-empty">No users found.</td></tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge badge-<?= $u['role'] === 'admin' ? 'processing' : 'pending' ?>"><?= ucfirst(htmlspecialchars($u['role'])) ?></span></td>
                        <td>
                            <span class="badge <?= $u['status'] === 'active' ? 'badge-active' : 'badge-inactive' ?>">
                                <?= ucfirst(htmlspecialchars($u['status'])) ?>
                            </span>
                        </td>
                        <td><?= $u['email_verified_at'] ? date('M d, Y', strtotime($u['email_verified_at'])) : 'No' ?></td>
                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['role'] !== 'admin'): ?>
                                <form action="<?= $base ?>/admin/users/toggle/<?= $u['id'] ?>" method="POST" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                    <button type="submit" class="btn-admin-sm <?= $u['status'] === 'active' ? 'btn-admin-danger' : '' ?>"
                                            onclick="return confirm('<?= $u['status'] === 'active' ? 'Block' : 'Unblock' ?> this user?');">
                                        <?= $u['status'] === 'active' ? 'Block' : 'Unblock' ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="admin-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
