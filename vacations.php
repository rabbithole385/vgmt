<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'VIP Celebrity Vacations & Luxury Escapes - ' . get_setting('site_name');
$currentPage = 'vacations';
$vacations = get_vacations();

require __DIR__ . '/includes/templates/header.php';
?>

<section class="page-title-band">
    <div class="container">
        <span class="badge badge-gold" style="margin-bottom: 12px;">EXCLUSIVE GETAWAYS</span>
        <h1 class="text-gold">VIP Celebrity Vacations & Escapes</h1>
        <p style="max-width: 620px; margin: 0 auto;">Experience world-class luxury destinations alongside your favorite icons. All-inclusive private islands, superyacht voyages, and alpine chalets.</p>
    </div>
</section>

<section class="content-section">
    <div class="container">
        <!-- App Category Filter Pills -->
        <div class="filter-pills" style="justify-content: center;">
            <button type="button" class="filter-pill active" data-filter-pill="all">All Experiences (<?= count($vacations) ?>)</button>
            <button type="button" class="filter-pill" data-filter-pill="island">Private Islands</button>
            <button type="button" class="filter-pill" data-filter-pill="mountain">Alpine Chalets</button>
            <button type="button" class="filter-pill" data-filter-pill="yacht">Superyachts</button>
            <button type="button" class="filter-pill" data-filter-pill="wellness">Wellness Retreats</button>
        </div>

        <!-- Vacations Grid -->
        <div class="vacations-grid">
            <?php foreach ($vacations as $vacation): ?>
                <div class="vacation-card" data-category="<?= e($vacation['category']) ?>">
                    <div class="vacation-image-box">
                        <img src="<?= e($vacation['image']) ?>" alt="<?= e($vacation['title']) ?>" loading="lazy">
                        <span class="vacation-tag"><?= e($vacation['tag']) ?></span>
                        <span class="vacation-duration"><?= e($vacation['duration']) ?></span>
                    </div>

                    <div class="vacation-body">
                        <a href="/celebrity.php?slug=<?= e($vacation['host_slug']) ?>" class="vacation-host-chip">
                            <img src="/uploads/celebrities/<?= e($vacation['host_image']) ?>" alt="<?= e($vacation['celebrity_host']) ?>">
                            <span>Host: <?= e($vacation['celebrity_host']) ?></span>
                        </a>

                        <div class="vacation-location">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                            <?= e($vacation['destination']) ?>
                        </div>

                        <h3 class="vacation-title"><?= e($vacation['title']) ?></h3>
                        <p class="vacation-desc"><?= e($vacation['description']) ?></p>

                        <div class="vacation-highlights">
                            <?php foreach ($vacation['highlights'] as $highlight): ?>
                                <span class="vacation-pill">✓ <?= e($highlight) ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="vacation-footer">
                            <div class="vacation-price">
                                <?= e($vacation['price']) ?>
                                <small>All-Inclusive VIP Experience</small>
                            </div>
                            <a href="/book.php?vacation=<?= urlencode($vacation['slug']) ?>&celebrity=<?= urlencode($vacation['host_slug']) ?>" class="btn btn-primary btn-sm">
                                Reserve VIP
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- VIP Experience Highlights Section -->
<section style="background: var(--color-surface); padding: 70px 0; border-top: 1px solid var(--color-border);">
    <div class="container" style="text-align: center;">
        <span class="badge badge-gold" style="margin-bottom: 12px;">UNRIVALED PRIVACY & LUXURY</span>
        <h2>The Celebrity Vacation Privilege</h2>
        <p style="max-width: 600px; margin: 0 auto 40px;">Every getaway package is managed by our dedicated VIP concierge team with maximum confidentiality and bespoke precision.</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; text-align: left;">
            <div style="background: var(--color-bg); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div style="font-size: 1.8rem; margin-bottom: 10px;">🛡️</div>
                <h4 style="margin: 0 0 8px; color: var(--color-gold);">Discreet Security</h4>
                <p style="margin: 0; font-size: 0.9rem;">Private transport, exclusive venue buyouts, and NDA-backed confidentiality for all attendees.</p>
            </div>
            <div style="background: var(--color-bg); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div style="font-size: 1.8rem; margin-bottom: 10px;">🚁</div>
                <h4 style="margin: 0 0 8px; color: var(--color-gold);">Helicopter & Yacht Transfers</h4>
                <p style="margin: 0; font-size: 0.9rem;">Direct point-to-point aviation and maritime charters bypass crowds and standard terminals.</p>
            </div>
            <div style="background: var(--color-bg); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div style="font-size: 1.8rem; margin-bottom: 10px;">🍽️</div>
                <h4 style="margin: 0 0 8px; color: var(--color-gold);">Michelin-Star Gastronomy</h4>
                <p style="margin: 0; font-size: 0.9rem;">Private personal chefs, vintage cellar pairings, and tailored culinary preferences on demand.</p>
            </div>
            <div style="background: var(--color-bg); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <div style="font-size: 1.8rem; margin-bottom: 10px;">⭐</div>
                <h4 style="margin: 0 0 8px; color: var(--color-gold);">Intimate 1-on-1 Access</h4>
                <p style="margin: 0; font-size: 0.9rem;">Fireside masterclasses, sunset yacht lounges, and meaningful personal memories with your icon.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container cta-inner">
        <h2>Custom Luxury Vacation Itinerary?</h2>
        <p>Looking for a custom private island retreat or bespoke corporate retreat with a celebrity of your choice? Speak with our private concierge.</p>
        <a href="/contact.php" class="btn btn-primary btn-lg">Contact Private Concierge</a>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>

