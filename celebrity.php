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
    echo '<section class="content-section"><div class="container narrow" style="text-align: center; padding: 60px 20px;">'
       . '<h1 class="text-gold">Celebrity Not Found</h1>'
       . '<p>The talent profile you requested could not be located.</p>'
       . '<a href="/celebrities.php" class="btn btn-primary">Back to Celebrity Roster</a></div></section>';
    require __DIR__ . '/includes/templates/footer.php';
    exit;
}

$pageTitle = $celeb['name'] . ' - ' . get_setting('site_name');
$currentPage = 'celebrities';

// Check if this celebrity is hosting any VIP Vacations
$celebVacations = [];
foreach (get_vacations() as $vac) {
    if (strtolower($vac['host_slug']) === strtolower($celeb['slug']) || stripos($celeb['name'], $vac['celebrity_host']) !== false) {
        $celebVacations[] = $vac;
    }
}

require __DIR__ . '/includes/templates/header.php';
?>

<section class="celeb-profile" style="padding: 120px 0 60px;">
    <div class="container profile-grid">
        <div class="profile-photo" style="box-shadow: 0 20px 40px rgba(0,0,0,0.5); border: 1px solid var(--color-border-gold); position: relative;">
            <img src="/uploads/celebrities/<?= e($celeb['image']) ?>" alt="<?= e($celeb['name']) ?>">
            <div style="position: absolute; top: 14px; left: 14px;">
                <span class="badge badge-gold">✓ Verified Talent</span>
            </div>
        </div>

        <div class="profile-details">
            <span class="badge badge-cyan" style="margin-bottom: 10px;"><?= e($celeb['category']) ?></span>
            <h1 class="text-gold" style="margin: 0 0 10px;"><?= e($celeb['name']) ?></h1>

            <?php if (!empty($celebVacations)): ?>
                <!-- Featured VIP Vacation Host Callout -->
                <?php foreach ($celebVacations as $cV): ?>
                    <div style="background: var(--gold-gradient-subtle); border: 1px solid var(--color-border-gold); border-radius: var(--radius-md); padding: 18px 20px; margin: 20px 0;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <span class="badge badge-gold" style="font-size: 0.7rem; padding: 2px 8px; margin-bottom: 4px;">EXCLUSIVE HOST</span>
                                <h4 style="margin: 0; color: var(--color-text); font-size: 1.05rem;">🌴 <?= e($cV['title']) ?></h4>
                                <small style="color: var(--color-cyan);"><?= e($cV['destination']) ?> &bull; <?= e($cV['duration']) ?></small>
                            </div>
                            <a href="/book.php?vacation=<?= urlencode($cV['slug']) ?>&celebrity=<?= urlencode($celeb['slug']) ?>" class="btn btn-primary btn-sm">
                                Reserve Vacation
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($celeb['short_bio']): ?>
                <p style="font-size: 1.12rem; color: var(--color-text); font-weight: 500; line-height: 1.6; margin: 16px 0;">
                    <?= e($celeb['short_bio']) ?>
                </p>
            <?php endif; ?>

            <?php if ($celeb['bio']): ?>
                <div style="color: var(--color-text-muted); line-height: 1.7; margin-bottom: 24px;">
                    <?= nl2br(e($celeb['bio'])) ?>
                </div>
            <?php endif; ?>

            <?php if ($celeb['base_fee_note']): ?>
                <div style="background: var(--color-surface); padding: 14px 18px; border-radius: var(--radius-sm); border: 1px solid var(--color-border); margin-bottom: 26px;">
                    <strong style="color: var(--color-gold);">Booking Note:</strong> <?= e($celeb['base_fee_note']) ?>
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                <a href="/book.php?celebrity=<?= e($celeb['slug']) ?>" class="btn btn-primary btn-lg">
                    ⚡ Book <?= e($celeb['name']) ?>
                </a>
                <a href="/celebrities.php" class="btn btn-ghost btn-lg">
                    Back to All Celebrities
                </a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>
