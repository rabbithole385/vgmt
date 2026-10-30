<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$post = [
    'id' => 0, 'title' => '', 'excerpt' => '', 'content' => '', 'featured_image' => '', 'status' => 'draft',
];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM blog_posts WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash_set('error', 'Post not found.');
        redirect('/admin/blog.php');
    }
    $post = $found;
}

$pageTitle = $id ? 'Edit Post' : 'New Post';
$activeNav = 'blog';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $title   = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status  = in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'draft';

        if ($title === '') {
            $errors[] = 'Title is required.';
        }

        $imageResult = handle_image_upload($_FILES['featured_image'] ?? [], UPLOAD_DIR_BLOG, $post['featured_image'] ?: null);
        if (!$imageResult['ok']) {
            $errors[] = $imageResult['error'];
        }

        if (empty($errors)) {
            $slugBase = slugify($title);
            $slug = unique_slug($slugBase, 'blog_posts', $id ?: null);
            $publishedAt = ($status === 'published') ? ($post['published_at'] ?? date('Y-m-d H:i:s')) : null;
            if ($status === 'published' && empty($post['published_at'])) {
                $publishedAt = date('Y-m-d H:i:s');
            }

            if ($id) {
                $stmt = db()->prepare(
                    'UPDATE blog_posts SET title=:title, slug=:slug, excerpt=:excerpt, content=:content,
                     featured_image=:image, status=:status, published_at=:pub WHERE id=:id'
                );
                $stmt->execute([
                    'title' => $title, 'slug' => $slug, 'excerpt' => $excerpt, 'content' => $content,
                    'image' => $imageResult['filename'], 'status' => $status, 'pub' => $publishedAt, 'id' => $id,
                ]);
                flash_set('success', 'Post updated.');
            } else {
                $stmt = db()->prepare(
                    'INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, status, published_at)
                     VALUES (:title, :slug, :excerpt, :content, :image, :status, :pub)'
                );
                $stmt->execute([
                    'title' => $title, 'slug' => $slug, 'excerpt' => $excerpt, 'content' => $content,
                    'image' => $imageResult['filename'], 'status' => $status, 'pub' => $publishedAt,
                ]);
                flash_set('success', 'Post created.');
            }
            redirect('/admin/blog.php');
        }

        $post = array_merge($post, compact('title', 'excerpt', 'content', 'status'));
    }
}

require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
    <?php if ($errors): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-row">
            <label>Title *</label>
            <input type="text" name="title" required value="<?= e($post['title']) ?>">
        </div>
        <div class="form-row">
            <label>Excerpt (short summary shown in listings)</label>
            <input type="text" name="excerpt" maxlength="400" value="<?= e($post['excerpt']) ?>">
        </div>
        <div class="form-row">
            <label>Content</label>
            <textarea name="content" rows="12"><?= e($post['content']) ?></textarea>
        </div>
        <div class="form-row">
            <label>Featured Image</label>
            <?php if ($post['featured_image']): ?>
                <img src="/uploads/blog/<?= e($post['featured_image']) ?>" class="current-image">
            <?php endif; ?>
            <input type="file" name="featured_image" accept="image/*">
        </div>
        <div class="form-row">
            <label>Status</label>
            <select name="status">
                <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><?= $id ? 'Save Changes' : 'Create Post' ?></button>
        <a href="/admin/blog.php" class="btn btn-outline">Cancel</a>
    </form>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
