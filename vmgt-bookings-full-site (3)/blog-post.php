<?php
require_once __DIR__ . '/config.php';

$slug = trim($_GET['slug'] ?? '');
$stmt = db()->prepare("SELECT * FROM blog_posts WHERE slug = :slug AND status = 'published' LIMIT 1");
$stmt->execute(['slug' => $slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Post Not Found - ' . get_setting('site_name');
    require __DIR__ . '/includes/templates/header.php';
    echo '<section class="content-section"><div class="container narrow"><h1>Post Not Found</h1>'
       . '<p><a href="/blog.php">Back to news</a></p></div></section>';
    require __DIR__ . '/includes/templates/footer.php';
    exit;
}

$pageTitle = $post['title'] . ' - ' . get_setting('site_name');
$currentPage = 'blog';

require __DIR__ . '/includes/templates/header.php';
?>

<section class="page-title-band">
    <div class="container narrow">
        <span class="blog-date"><?= e(format_date($post['published_at'] ?? $post['created_at'])) ?></span>
        <h1><?= e($post['title']) ?></h1>
    </div>
</section>

<section class="content-section">
    <div class="container narrow">
        <?php if ($post['featured_image']): ?>
            <img class="blog-post-image" src="/uploads/blog/<?= e($post['featured_image']) ?>" alt="<?= e($post['title']) ?>">
        <?php endif; ?>
        <div class="blog-post-content">
            <?= nl2br(e($post['content'])) ?>
        </div>
        <p><a href="/blog.php">&larr; Back to News</a></p>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>
