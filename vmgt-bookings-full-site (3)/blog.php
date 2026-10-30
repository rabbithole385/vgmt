<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'News - ' . get_setting('site_name');
$currentPage = 'blog';

[$page, $perPage, $offset] = paginate_params(9);

$total = (int)db()->query("SELECT COUNT(*) c FROM blog_posts WHERE status = 'published'")->fetch()['c'];

$stmt = db()->prepare(
    "SELECT * FROM blog_posts WHERE status = 'published'
     ORDER BY published_at DESC, created_at DESC LIMIT :limit OFFSET :offset"
);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

$totalPages = max(1, (int)ceil($total / $perPage));

require __DIR__ . '/includes/templates/header.php';
?>

<section class="page-title-band">
    <div class="container">
        <h1>Latest News</h1>
    </div>
</section>

<section class="content-section">
    <div class="container">
        <div class="blog-grid">
            <?php foreach ($posts as $post): ?>
                <a class="blog-card" href="/blog-post.php?slug=<?= e($post['slug']) ?>">
                    <?php if ($post['featured_image']): ?>
                        <div class="blog-card-image">
                            <img src="/uploads/blog/<?= e($post['featured_image']) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
                        </div>
                    <?php endif; ?>
                    <div class="blog-card-body">
                        <span class="blog-date"><?= e(format_date($post['published_at'] ?? $post['created_at'])) ?></span>
                        <h3><?= e($post['title']) ?></h3>
                        <p><?= e($post['excerpt']) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if (empty($posts)): ?>
                <p>No news posted yet. Check back soon.</p>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>
