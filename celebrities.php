<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'Celebrity List - ' . get_setting('site_name');
$currentPage = 'celebrities';
$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $stmt = db()->prepare(
        "SELECT * FROM celebrities WHERE status = 'published'
         AND (name LIKE :q1 OR category LIKE :q2)
         ORDER BY sort_order ASC, name ASC"
    );
    $like = '%' . $search . '%';
    $stmt->execute(['q1' => $like, 'q2' => $like]);
} else {
    $stmt = db()->query(
        "SELECT * FROM celebrities WHERE status = 'published' ORDER BY sort_order ASC, name ASC"
    );
}
$celebrities = $stmt->fetchAll();

require __DIR__ . '/includes/templates/header.php';
?>

<section class="page-title-band">
    <div class="container">
        <h1>Find A Celebrity</h1>
    </div>
</section>

<section class="content-section">
    <div class="container">
        <form method="get" class="celeb-search">
            <input type="text" name="q" placeholder="Search by name or category (e.g. Actor, Singer)"
                   value="<?= e($search) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <div class="celebrity-grid">
            <?php foreach ($celebrities as $celeb): ?>
                <a class="celebrity-card" href="/celebrity.php?slug=<?= e($celeb['slug']) ?>">
                    <div class="celebrity-photo">
                        <img src="/uploads/celebrities/<?= e($celeb['image']) ?>" alt="<?= e($celeb['name']) ?>" loading="lazy">
                    </div>
                    <div class="celebrity-info">
                        <h3><?= e($celeb['name']) ?></h3>
                        <span><?= e($celeb['category']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if (empty($celebrities)): ?>
                <p>No celebrities found<?= $search !== '' ? ' for "' . e($search) . '"' : '' ?>.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>
