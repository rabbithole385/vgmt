<?php
require_once __DIR__ . '/config.php';

$pageTitle = get_setting('site_name') . ' - Premier Celebrity Booking Agency & Luxury Vacations';
$currentPage = 'home';

$stmt = db()->prepare(
    "SELECT * FROM celebrities WHERE status = 'published' AND is_featured = 1
     ORDER BY sort_order ASC, name ASC LIMIT 12"
);
$stmt->execute();
$featured = $stmt->fetchAll();

$vacations = array_slice(get_vacations(), 0, 3);

require __DIR__ . '/includes/templates/header.php';
?>

<!-- Luxury Hero Section -->
<section class="hero">
    <div class="container hero-inner">
        <span class="badge badge-gold" style="margin-bottom: 14px;">✨ WORLD-CLASS TALENT & VIP ESCAPES</span>
        <h1>Book Iconic Celebrities & <span class="text-gold">Luxury Vacations</span></h1>
        <p class="hero-subtitle"><?= e(get_setting('hero_subtitle', 'Direct access to global stars, keynote speakers, and bespoke all-inclusive luxury retreats.')) ?></p>
        
        <div class="hero-cta-group">
            <a href="/book.php" class="btn btn-primary btn-lg">⚡ Instant VIP Booking</a>
            <a href="/vacations.php" class="btn btn-outline btn-lg">🌴 View VIP Vacations</a>
        </div>

        <!-- Interactive Dynamic Image Shuffler Showcase -->
        <div class="showcase-container">
            <div class="showcase-header">
                <div class="showcase-title">
                    <span>🌟 Featured Roster & Getaways</span>
                </div>
                <button type="button" class="btn-shuffle" id="btnShuffleShowcase">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M10.59 9.17L5.41 4 4 5.41l5.17 5.17 1.42-1.41zM14.5 4l2.04 2.04L4 18.59 5.41 20 17.96 7.46 20 9.5V4h-5.5zm.33 9.41l-1.41 1.41 3.13 3.13L14.5 20H20v-5.5l-2.04 2.04-3.13-3.13z"/></svg>
                    Shuffle Showcase
                </button>
            </div>

            <!-- Horizontal Swipe Rail for Mobile & Grid for Desktop -->
            <div class="app-rail" id="showcaseRail">
                <?php foreach ($featured as $celeb): ?>
                    <div class="app-rail-item">
                        <a class="celebrity-card" href="/celebrity.php?slug=<?= e($celeb['slug']) ?>">
                            <div class="celebrity-photo">
                                <span class="celebrity-verified-badge">✓ VIP</span>
                                <img src="/uploads/celebrities/<?= e($celeb['image']) ?>" alt="<?= e($celeb['name']) ?>" loading="lazy">
                            </div>
                            <div class="celebrity-info">
                                <h3><?= e($celeb['name']) ?></h3>
                                <span class="category"><?= e($celeb['category']) ?></span>
                                <div class="celebrity-card-actions">
                                    <span class="btn btn-primary btn-card-book">Book VIP</span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Trust Metrics Bar -->
<section style="background: var(--color-surface); padding: 36px 0; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; text-align: center;">
            <div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--color-gold);">500+</div>
                <div style="font-size: 0.85rem; color: var(--color-text-muted);">Verified A-List Icons</div>
            </div>
            <div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--color-cyan);">35+</div>
                <div style="font-size: 0.85rem; color: var(--color-text-muted);">Global Vacation Destinations</div>
            </div>
            <div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--color-gold);">100%</div>
                <div style="font-size: 0.85rem; color: var(--color-text-muted);">Discreet Confidentiality</div>
            </div>
            <div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--color-cyan);">24 / 7</div>
                <div style="font-size: 0.85rem; color: var(--color-text-muted);">Dedicated VIP Concierge</div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Celebrities Roster -->
<section class="celebrities-section">
    <div class="container">
        <div style="text-align: center; margin-bottom: 30px;">
            <span class="badge badge-gold" style="margin-bottom: 8px;">PREMIER ROSTER</span>
            <h2>Celebrities & Keynote Figures</h2>
            <p class="section-subtitle">Browse iconic actors, international music artists, and visionary keynote speakers available for global bookings.</p>

            <!-- App Filter Pills -->
            <div class="filter-pills" style="justify-content: center;">
                <button type="button" class="filter-pill active" data-filter-pill="all">All Stars</button>
                <button type="button" class="filter-pill" data-filter-pill="actor">Actors</button>
                <button type="button" class="filter-pill" data-filter-pill="singer">Singers & Music</button>
                <button type="button" class="filter-pill" data-filter-pill="speaker">Keynotes</button>
            </div>
        </div>

        <div class="celebrity-grid">
            <?php foreach ($featured as $celeb): ?>
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
        </div>

        <div class="section-cta" style="margin-top: 40px;">
            <a href="/celebrities.php" class="btn btn-outline">Explore Full Celebrity Roster &rarr;</a>
        </div>
    </div>
</section>

<!-- Brand New VIP Vacations Showcase on Home -->
<section class="vacations-section">
    <div class="container">
        <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 30px;">
            <div>
                <span class="badge badge-gold" style="margin-bottom: 8px;">ALL-INCLUSIVE GETAWAYS</span>
                <h2 style="margin-bottom: 8px;">VIP Vacations With Celebrities</h2>
                <p style="margin: 0; max-width: 580px;">Combine exotic luxury destinations with intimate celebrity host access. Private islands, superyacht voyages, and alpine chalets.</p>
            </div>
            <a href="/vacations.php" class="btn btn-outline btn-sm">View All Vacations (<?= count(get_vacations()) ?>) &rarr;</a>
        </div>

        <div class="vacations-grid">
            <?php foreach ($vacations as $vacation): ?>
                <div class="vacation-card">
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
                            <?php foreach (array_slice($vacation['highlights'], 0, 3) as $highlight): ?>
                                <span class="vacation-pill">✓ <?= e($highlight) ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="vacation-footer">
                            <div class="vacation-price">
                                <?= e($vacation['price']) ?>
                                <small>VIP Experience</small>
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

<!-- About Section -->
<section class="about-section">
    <div class="container about-inner">
        <span class="badge badge-gold" style="margin-bottom: 10px;">ABOUT VMGT</span>
        <h2><?= e(get_setting('about_title', 'About Us')) ?></h2>
        <p><?= nl2br(e(get_setting('about_text', 'VMGT BOOKINGS is an elite talent acquisition and luxury concierge agency.'))) ?></p>
        <a href="/about.php" class="btn btn-outline" style="margin-top: 10px;">Learn More About Our Agency</a>
    </div>
</section>

<!-- Final CTA Band -->
<section class="cta-band">
    <div class="container cta-inner">
        <span class="badge badge-gold" style="margin-bottom: 12px;">READY TO ELEVATE YOUR EVENT?</span>
        <h2><?= e(get_setting('cta_title', 'Book A Celeb Or Vacation Now')) ?></h2>
        <p><?= e(get_setting('cta_text', 'Our concierge team coordinates flights, riders, security, and private contracts seamlessly.')) ?></p>
        <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; margin-top: 24px;">
            <a href="/book.php" class="btn btn-primary btn-lg">⚡ Start Booking</a>
            <a href="/vacations.php" class="btn btn-outline btn-lg">🌴 Explore Vacations</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>
