<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'Celebrity Roster - ' . get_setting('site_name');
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
        <span class="badge badge-gold" style="margin-bottom: 10px;">OFFICIAL ROSTER</span>
        <h1 class="text-gold">Find A Celebrity</h1>
        <p>Book globally renowned actors, award-winning musical artists, and inspirational keynote speakers.</p>
    </div>
</section>

<section class="content-section">
    <div class="container">
        <!-- App Style Search Form -->
        <form method="get" class="celeb-search" style="display: flex; gap: 10px; max-width: 580px; margin: 0 auto 28px;">
            <input type="text" name="q" placeholder="Search by name, talent or category (e.g. Actor, Singer)..."
                   value="<?= e($search) ?>" style="border-radius: var(--radius-full); padding: 14px 22px;">
            <button type="submit" class="btn btn-primary" style="padding: 0 24px;">Search</button>
        </form>

        <!-- Category Filter Pills -->
        <div class="filter-pills" style="justify-content: center; margin-bottom: 30px;">
            <button type="button" class="filter-pill active" data-filter-pill="all">All Stars (<?= count($celebrities) ?>)</button>
            <button type="button" class="filter-pill" data-filter-pill="actor">Actors</button>
            <button type="button" class="filter-pill" data-filter-pill="singer">Singers & Music</button>
            <button type="button" class="filter-pill" data-filter-pill="speaker">Keynotes</button>
        </div>

        <div class="celebrity-grid">
            <?php foreach ($celebrities as $celeb): ?>
                <div class="celebrity-card" data-category="<?= e($celeb['category']) ?>">
                    <a href="/celebrity.php?slug=<?= e($celeb['slug']) ?>">
                        <div class="celebrity-photo">
                            <span class="celebrity-verified-badge">✓ Verified</span>
                            <img src="/uploads/celebrities/<?= e($celeb['image']) ?>" alt="<?= e($celeb['name']) ?>" loading="lazy">
                        </div>
                    </a>
                    <div class="celebrity-info">
                        <h3><?= e($celeb['name']) ?></h3>
                        <span class="category"><?= e($celeb['category']) ?></span>
                        <div class="celebrity-card-actions">
                            <a href="/book.php?celebrity=<?= urlencode($celeb['slug']) ?>" class="btn btn-primary btn-card-book">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($celebrities)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px 0;">
                    <p>No celebrities found<?= $search !== '' ? ' matching "' . e($search) . '"' : '' ?>.</p>
                    <a href="/celebrities.php" class="btn btn-outline">Clear Search</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>
