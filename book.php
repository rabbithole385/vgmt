<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'Book A Celebrity Or VIP Vacation - ' . get_setting('site_name');
$currentPage = 'book';
$errors = [];

$celebrities = db()->query(
    "SELECT id, name, slug, category FROM celebrities WHERE status = 'published' ORDER BY name ASC"
)->fetchAll();

$preselectedSlug = trim($_GET['celebrity'] ?? '');
$preselectedVacationSlug = trim($_GET['vacation'] ?? '');
$selectedVacation = $preselectedVacationSlug !== '' ? get_vacation_by_slug($preselectedVacationSlug) : null;

// If a vacation was selected, match the host celebrity if possible
if ($selectedVacation && $preselectedSlug === '') {
    $preselectedSlug = $selectedVacation['host_slug'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } elseif (too_many_requests('booking_form', 6, 600)) {
        $errors[] = 'Too many booking requests submitted. Please try again later.';
    } else {
        $celebrityId   = (int)($_POST['celebrity_id'] ?? 0) ?: null;
        $eventType     = trim($_POST['event_type'] ?? '');
        $eventDate     = trim($_POST['event_date'] ?? '');
        $eventLocation = trim($_POST['event_location'] ?? '');
        $budgetRange   = trim($_POST['budget_range'] ?? '');
        $fullName      = trim($_POST['full_name'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $phone         = trim($_POST['phone'] ?? '');
        $organization  = trim($_POST['organization'] ?? '');
        $message       = trim($_POST['message'] ?? '');
        $honeypot      = trim($_POST['website'] ?? '');

        if ($honeypot !== '') {
            $errors[] = 'Submission rejected.';
        }
        if ($fullName === '' || $email === '') {
            $errors[] = 'Please provide your name and email address.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($eventDate !== '') {
            $d = DateTime::createFromFormat('Y-m-d', $eventDate);
            if (!$d || $d->format('Y-m-d') !== $eventDate) {
                $errors[] = 'Please enter a valid event date.';
            }
        }

        $celebName = null;
        if ($celebrityId) {
            $c = db()->prepare('SELECT name FROM celebrities WHERE id = :id');
            $c->execute(['id' => $celebrityId]);
            $row = $c->fetch();
            $celebName = $row['name'] ?? null;
        }

        if (empty($errors)) {
            $stmt = db()->prepare(
                'INSERT INTO bookings
                 (celebrity_id, celebrity_name_snapshot, full_name, email, phone, organization,
                  event_type, event_date, event_location, budget_range, message)
                 VALUES (:celebrity_id, :celeb_name, :full_name, :email, :phone, :organization,
                  :event_type, :event_date, :event_location, :budget_range, :message)'
            );
            $stmt->execute([
                'celebrity_id'   => $celebrityId,
                'celeb_name'     => $celebName,
                'full_name'      => $fullName,
                'email'          => $email,
                'phone'          => $phone,
                'organization'   => $organization,
                'event_type'     => $eventType,
                'event_date'     => $eventDate !== '' ? $eventDate : null,
                'event_location' => $eventLocation,
                'budget_range'   => $budgetRange,
                'message'        => $message,
            ]);

            send_notification_email(
                ADMIN_EMAIL,
                'New booking request - ' . get_setting('site_name'),
                '<h3>New VIP Booking Request</h3>'
                . '<p><strong>Celebrity / Host:</strong> ' . e($celebName ?: 'Not specified') . '</p>'
                . '<p><strong>Name:</strong> ' . e($fullName) . '</p>'
                . '<p><strong>Email:</strong> ' . e($email) . '</p>'
                . '<p><strong>Phone:</strong> ' . e($phone) . '</p>'
                . '<p><strong>Organization:</strong> ' . e($organization) . '</p>'
                . '<p><strong>Booking Type:</strong> ' . e($eventType) . '</p>'
                . '<p><strong>Target Date:</strong> ' . e($eventDate) . '</p>'
                . '<p><strong>Location / Destination:</strong> ' . e($eventLocation) . '</p>'
                . '<p><strong>Budget Range:</strong> ' . e($budgetRange) . '</p>'
                . '<p><strong>Message / Special Requests:</strong><br>' . nl2br(e($message)) . '</p>'
            );

            flash_set('success', 'Your VIP request has been received! Our concierge team will reach out to you within 24 hours.');
            redirect('/book.php?submitted=1');
        }
    }
}

require __DIR__ . '/includes/templates/header.php';
?>

<section class="page-title-band">
    <div class="container">
        <span class="badge badge-gold" style="margin-bottom: 8px;">PRIVATE VIP CONCIERGE</span>
        <h1 class="text-gold">Bespoke VIP Booking</h1>
        <p>Book a world-class celebrity appearance or reserve an exclusive luxury vacation getaway.</p>
    </div>
</section>

<section class="content-section">
    <div class="container narrow">

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php if ($selectedVacation): ?>
            <!-- Vacation Selection Banner -->
            <div style="background: var(--gold-gradient-subtle); border: 1px solid var(--color-border-gold); border-radius: var(--radius-md); padding: 18px 22px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <img src="<?= e($selectedVacation['image']) ?>" alt="<?= e($selectedVacation['title']) ?>" style="width: 64px; height: 64px; border-radius: var(--radius-sm); object-fit: cover;">
                    <div>
                        <span class="badge badge-gold" style="font-size: 0.68rem; padding: 2px 7px;">VACATION SELECTED</span>
                        <h4 style="margin: 4px 0 2px; color: var(--color-text);"><?= e($selectedVacation['title']) ?></h4>
                        <small style="color: var(--color-cyan);">📍 <?= e($selectedVacation['destination']) ?> &bull; Host: <?= e($selectedVacation['celebrity_host']) ?></small>
                    </div>
                </div>
                <a href="/vacations.php" class="btn btn-ghost btn-sm">Change Vacation</a>
            </div>
        <?php endif; ?>

        <div class="wizard-steps" id="wizardSteps">
            <span class="step-dot active" data-step-dot="1">1. Talent / Trip</span>
            <span class="step-dot" data-step-dot="2">2. Itinerary</span>
            <span class="step-dot" data-step-dot="3">3. Your Contact</span>
            <span class="step-dot" data-step-dot="4">4. Confirmation</span>
        </div>

        <form method="post" id="bookingForm" class="wizard-card" novalidate>
            <?= csrf_field() ?>
            <div style="position:absolute;left:-5000px" aria-hidden="true">
                <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <!-- Step 1: Celebrity / Vacation Host -->
            <div class="wizard-step" data-step="1">
                <div class="form-row">
                    <label>Select Celebrity or Vacation Host:
                        <select name="celebrity_id" id="celebritySelect">
                            <option value="">Not sure yet / General inquiry</option>
                            <?php foreach ($celebrities as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"
                                    data-slug="<?= e($c['slug']) ?>"
                                    <?= ($preselectedSlug === $c['slug']) ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?> &mdash; <?= e($c['category']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="form-row">
                    <label>Inquiry Category:
                        <select name="booking_category">
                            <option value="Celebrity Event / Appearance" <?= !$selectedVacation ? 'selected' : '' ?>>Celebrity Event, Keynote or Appearance</option>
                            <option value="VIP Vacation / Luxury Escape" <?= $selectedVacation ? 'selected' : '' ?>>VIP Vacation / Luxury Celebrity Escape</option>
                            <option value="Brand Endorsement / Campaign">Brand Endorsement & Commercial Campaign</option>
                            <option value="Private VIP Meet & Greet">Private VIP Meet & Greet</option>
                        </select>
                    </label>
                </div>

                <div class="wizard-actions">
                    <span></span>
                    <button type="button" class="btn btn-primary wizard-next">Continue to Details &rarr;</button>
                </div>
            </div>

            <!-- Step 2: Event / Vacation Details -->
            <div class="wizard-step" data-step="2" hidden>
                <div class="form-row">
                    <label>Event Type or Vacation Package Title
                        <input type="text" name="event_type" 
                               value="<?= $selectedVacation ? 'VIP Vacation: ' . e($selectedVacation['title']) : '' ?>"
                               placeholder="e.g. VIP Vacation, Corporate Keynote, Private Villa Gala, Wedding">
                    </label>
                </div>
                <div class="form-row-group">
                    <label>Desired Date / Season
                        <input type="date" name="event_date">
                    </label>
                    <label>Budget Range
                        <select name="budget_range">
                            <option value="">Select an investment tier</option>
                            <option value="Under $15,000" <?= ($selectedVacation && strpos($selectedVacation['price'], '$9') !== false) ? 'selected' : '' ?>>$9,000 - $15,000</option>
                            <option value="$15,000 - $50,000" <?= ($selectedVacation && (strpos($selectedVacation['price'], '$14') !== false || strpos($selectedVacation['price'], '$18') !== false)) ? 'selected' : '' ?>>$15,000 - $50,000</option>
                            <option value="$50,000 - $150,000">$50,000 - $150,000</option>
                            <option value="$150,000+">$150,000+ (Ultra VIP)</option>
                        </select>
                    </label>
                </div>
                <div class="form-row">
                    <label>Location / Destination
                        <input type="text" name="event_location"
                               value="<?= $selectedVacation ? e($selectedVacation['destination']) : '' ?>"
                               placeholder="e.g. Maldives, Zermatt, London, New York, Lagos, Dubai">
                    </label>
                </div>
                <div class="wizard-actions">
                    <button type="button" class="btn btn-ghost wizard-back">&larr; Back</button>
                    <button type="button" class="btn btn-primary wizard-next">Continue to Contact &rarr;</button>
                </div>
            </div>

            <!-- Step 3: Contact Info -->
            <div class="wizard-step" data-step="3" hidden>
                <div class="form-row-group">
                    <label>Full Name *
                        <input type="text" name="full_name" placeholder="Your full name" required>
                    </label>
                    <label>Email Address *
                        <input type="email" name="email" placeholder="your@email.com" required>
                    </label>
                </div>
                <div class="form-row-group">
                    <label>Phone / WhatsApp Number
                        <input type="tel" name="phone" placeholder="+1 ... or +234 ...">
                    </label>
                    <label>Organization / VIP Representative
                        <input type="text" name="organization" placeholder="Company, agency, or private principal">
                    </label>
                </div>
                <div class="form-row">
                    <label>Special Requests, Security Requirements, or Specific Needs
                        <textarea name="message" rows="4" placeholder="Mention guest count, dietary preferences, privacy NDA requests, or questions..."></textarea>
                    </label>
                </div>
                <div class="wizard-actions">
                    <button type="button" class="btn btn-ghost wizard-back">&larr; Back</button>
                    <button type="button" class="btn btn-primary wizard-next">Review Request &rarr;</button>
                </div>
            </div>

            <!-- Step 4: Review -->
            <div class="wizard-step" data-step="4" hidden>
                <h3 style="margin-top: 0; color: var(--color-gold);">Review Your VIP Booking Summary</h3>
                <div class="review-box" id="reviewBox" style="margin-bottom: 24px;"></div>
                <div class="wizard-actions">
                    <button type="button" class="btn btn-ghost wizard-back">&larr; Edit Details</button>
                    <button type="submit" class="btn btn-primary btn-lg">Submit VIP Request ⚡</button>
                </div>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>
