<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'About Us - ' . get_setting('site_name');
$currentPage = 'about';

require __DIR__ . '/includes/templates/header.php';
?>

<section class="page-title-band">
    <div class="container">
        <h1><?= e(get_setting('about_title', 'About Us')) ?></h1>
    </div>
</section>

<section class="content-section">
    <div class="container narrow">
        <p><?= nl2br(e(get_setting('about_text'))) ?></p>
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
