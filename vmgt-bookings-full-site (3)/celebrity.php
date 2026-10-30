<?php
require_once __DIR__ . '/config.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    redirect('/celebrities.php');
}

$stmt = db()->prepare("SELECT * FROM celebrities WHERE slug = :slug AND status = 'published' LIMIT 1");
$stmt->execute(['slug' => $slug]);
$celeb = $stmt->fetch();

if (!$celeb) {
    http_response_code(404);
    $pageTitle = 'Celebrity Not Found - ' . get_setting('site_name');
    require __DIR__ . '/includes/templates/header.php';
    echo '<section class="content-section"><div class="container narrow"><h1>Celebrity Not Found</h1>'
       . '<p><a href="/celebrities.php">Back to celebrity list</a></p></div></section>';
    require __DIR__ . '/includes/templates/footer.php';
    exit;
}

$pageTitle = $celeb['name'] . ' - ' . get_setting('site_name');
$currentPage = 'celebrities';

require __DIR__ . '/includes/templates/header.php';
?>

<section class="celeb-profile">
    <div class="container profile-grid">
        <div class="profile-photo">
            <img src="/uploads/celebrities/<?= e($celeb['image']) ?>" alt="<?= e($celeb['name']) ?>">
        </div>
        <div class="profile-details">
            <h1><?= e($celeb['name']) ?></h1>
            <p class="profile-category"><?= e($celeb['category']) ?></p>
            <?php if ($celeb['short_bio']): ?>
                <p class="profile-short-bio"><?= e($celeb['short_bio']) ?></p>
            <?php endif; ?>
            <?php if ($celeb['bio']): ?>
                <div class="profile-bio"><?= nl2br(e($celeb['bio'])) ?></div>
            <?php endif; ?>
            <?php if ($celeb['base_fee_note']): ?>
                <p class="profile-fee"><strong>Booking Note:</strong> <?= e($celeb['base_fee_note']) ?></p>
            <?php endif; ?>
            <a href="/book.php?celebrity=<?= e($celeb['slug']) ?>" class="btn btn-primary btn-lg">
                Book <?= e($celeb['name']) ?>
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>
