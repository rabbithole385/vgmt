<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Blog / News';
$activeNav = 'blog';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_verify()) {
        flash_set('error', 'Session expired, please try again.');
    } else {
        $id = (int)$_POST['id'];
        $stmt = db()->prepare('SELECT featured_image FROM blog_posts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            db()->prepare('DELETE FROM blog_posts WHERE id = :id')->execute(['id' => $id]);
            if ($row['featured_image']) {
                @unlink(UPLOAD_DIR_BLOG . '/' . $row['featured_image']);
            }
            flash_set('success', 'Post deleted.');
        }
    }
    redirect('/admin/blog.php');
}

$posts = db()->query('SELECT * FROM blog_posts ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="margin:0;">All Posts (<?= count($posts) ?>)</h2>
        <a href="/admin/blog-form.php" class="btn btn-primary">+ New Post</a>
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Status</th><th>Published</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($posts as $p): ?>
                <tr>
                    <td><?= e($p['title']) ?></td>
                    <td><span class="status status-<?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
                    <td><?= $p['published_at'] ? e(format_date($p['published_at'])) : '—' ?></td>
                    <td class="table-actions">
                        <a href="/admin/blog-form.php?id=<?= (int)$p['id'] ?>">Edit</a>
                        <?php if ($p['status'] === 'published'): ?>
                            <a href="/blog-post.php?slug=<?= e($p['slug']) ?>" target="_blank">View</a>
                        <?php endif; ?>
                        <form method="post" data-confirm="Delete this post?" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button type="submit" class="btn-danger" style="background:none;border:none;cursor:pointer;font-size:.85rem;">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($posts)): ?>
                    <tr><td colspan="4" class="empty-state">No posts yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
