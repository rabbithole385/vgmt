<?php
require_once __DIR__ . '/config.php';

$pageTitle = get_setting('site_name') . ' - ' . get_setting('tagline');
$currentPage = 'home';

$stmt = db()->prepare(
    "SELECT * FROM celebrities WHERE status = 'published' AND is_featured = 1
     ORDER BY sort_order ASC, name ASC LIMIT 12"
);
$stmt->execute();
$featured = $stmt->fetchAll();

require __DIR__ . '/includes/templates/header.php';
?>

<section class="hero">
    <div class="container hero-inner">
        <p class="eyebrow">Celebrity Booking Agency</p>
        <h1><?= e(get_setting('tagline', 'Book Your Favourite Celebrity')) ?></h1>
        <p class="hero-subtitle"><?= e(get_setting('hero_subtitle')) ?></p>
        <a href="/book.php" class="btn btn-primary btn-lg">Book Now!</a>
    </div>
</section>

<section class="about-section">
    <div class="container about-inner">
        <h2><?= e(get_setting('about_title', 'About Us')) ?></h2>
        <p><?= nl2br(e(get_setting('about_text'))) ?></p>
        <a href="/about.php" class="btn btn-outline">Learn More</a>
    </div>
</section>

<section class="celebrities-section">
    <div class="container">
        <h2>Featured Celebrities</h2>
        <p class="section-subtitle">This is a list of featured celebrities and public figures available for booking</p>

        <div class="celebrity-grid">
            <?php foreach ($featured as $celeb): ?>
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
            <?php if (empty($featured)): ?>
                <p>No celebrities have been added yet. Check back soon.</p>
            <?php endif; ?>
        </div>

        <div class="section-cta">
            <a href="/celebrities.php" class="btn btn-outline">View Celebrity List</a>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container cta-inner">
        <h2><?= e(get_setting('cta_title', 'Book A Celeb Now!')) ?></h2>
        <p><?= e(get_setting('cta_text')) ?></p>
        <a href="/book.php" class="btn btn-primary btn-lg">Start Booking</a>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>
