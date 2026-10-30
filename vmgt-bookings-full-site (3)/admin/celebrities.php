<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Celebrities';
$activeNav = 'celebrities';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_verify()) {
        flash_set('error', 'Session expired, please try again.');
    } else {
        $id = (int)$_POST['id'];
        $stmt = db()->prepare('SELECT image FROM celebrities WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            db()->prepare('DELETE FROM celebrities WHERE id = :id')->execute(['id' => $id]);
            if ($row['image']) {
                @unlink(UPLOAD_DIR_CELEB . '/' . $row['image']);
            }
            flash_set('success', 'Celebrity deleted.');
        }
    }
    redirect('/admin/celebrities.php');
}

$celebrities = db()->query('SELECT * FROM celebrities ORDER BY sort_order ASC, name ASC')->fetchAll();

require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="margin:0;">All Celebrities (<?= count($celebrities) ?>)</h2>
        <a href="/admin/celebrity-form.php" class="btn btn-primary">+ Add Celebrity</a>
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr><th>Photo</th><th>Name</th><th>Category</th><th>Featured</th><th>Status</th><th>Order</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($celebrities as $c): ?>
                <tr>
                    <td><img src="/uploads/celebrities/<?= e($c['image']) ?>" alt="" style="width:44px;height:56px;object-fit:cover;border-radius:4px;"></td>
                    <td><?= e($c['name']) ?></td>
                    <td><?= e($c['category']) ?></td>
                    <td><?= $c['is_featured'] ? 'Yes' : 'No' ?></td>
                    <td><span class="status status-<?= e($c['status']) ?>"><?= e($c['status']) ?></span></td>
                    <td><?= (int)$c['sort_order'] ?></td>
                    <td class="table-actions">
                        <a href="/admin/celebrity-form.php?id=<?= (int)$c['id'] ?>">Edit</a>
                        <a href="/celebrity.php?slug=<?= e($c['slug']) ?>" target="_blank">View</a>
                        <form method="post" data-confirm="Delete <?= e($c['name']) ?>? This cannot be undone." style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button type="submit" class="btn-danger" style="background:none;border:none;cursor:pointer;font-size:.85rem;">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($celebrities)): ?>
                    <tr><td colspan="7" class="empty-state">No celebrities yet. Click "Add Celebrity" to create one.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
