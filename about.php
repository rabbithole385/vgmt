<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'About Us - ' . get_setting('site_name');
$currentPage = 'about';

require __DIR__ . '/includes/templates/header.php';
?>

<section class="page-title-band">
    <div class="container">
        <span class="badge badge-gold" style="margin-bottom: 10px;">OUR STORY</span>
        <h1 class="text-gold"><?= e(get_setting('about_title', 'About Us')) ?></h1>
        <p>Premier talent concierge agency delivering world-class celebrity bookings and luxury VIP experiences.</p>
    </div>
</section>

<section class="content-section">
    <div class="container narrow" style="text-align: center;">
        <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text);"><?= nl2br(e(get_setting('about_text'))) ?></p>
    </div>
</section>

<!-- Why Choose VMGT -->
<section style="background: var(--color-bg-alt); padding: 70px 0; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
    <div class="container" style="text-align: center;">
        <span class="badge badge-gold" style="margin-bottom: 12px;">WHY VMGT</span>
        <h2>What Sets Us Apart</h2>
        <p style="max-width: 600px; margin: 0 auto 40px;">We combine elite industry connections with white-glove concierge service to deliver seamless, unforgettable experiences.</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; text-align: left;">
            <div style="background: var(--color-surface); padding: 28px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div style="font-size: 2rem; margin-bottom: 12px;">🌍</div>
                <h4 style="margin: 0 0 8px; color: var(--color-gold);">Global Talent Network</h4>
                <p style="margin: 0; font-size: 0.92rem;">Direct relationships with A-list talent agencies, managers, and publicists across six continents.</p>
            </div>
            <div style="background: var(--color-surface); padding: 28px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div style="font-size: 2rem; margin-bottom: 12px;">🔒</div>
                <h4 style="margin: 0 0 8px; color: var(--color-gold);">Absolute Discretion</h4>
                <p style="margin: 0; font-size: 0.92rem;">NDA-backed confidentiality, private logistics, and controlled media access for every engagement.</p>
            </div>
            <div style="background: var(--color-surface); padding: 28px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div style="font-size: 2rem; margin-bottom: 12px;">✨</div>
                <h4 style="margin: 0 0 8px; color: var(--color-gold);">End-to-End Concierge</h4>
                <p style="margin: 0; font-size: 0.92rem;">From contract negotiation and flight coordination to on-site hospitality — we handle everything.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container cta-inner">
        <span class="badge badge-gold" style="margin-bottom: 12px;">READY?</span>
        <h2><?= e(get_setting('cta_title', 'Book A Celeb Or Vacation Now')) ?></h2>
        <p><?= e(get_setting('cta_text')) ?></p>
        <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; margin-top: 20px;">
            <a href="/book.php" class="btn btn-primary btn-lg">⚡ Start Booking</a>
            <a href="/vacations.php" class="btn btn-outline btn-lg">🌴 VIP Vacations</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>
