<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'Book A Celebrity - ' . get_setting('site_name');
$currentPage = 'book';
$errors = [];

$celebrities = db()->query(
    "SELECT id, name, slug, category FROM celebrities WHERE status = 'published' ORDER BY name ASC"
)->fetchAll();

$preselectedSlug = trim($_GET['celebrity'] ?? '');

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
                '<h3>New Booking Request</h3>'
                . '<p><strong>Celebrity:</strong> ' . e($celebName ?: 'Not specified') . '</p>'
                . '<p><strong>Name:</strong> ' . e($fullName) . '</p>'
                . '<p><strong>Email:</strong> ' . e($email) . '</p>'
                . '<p><strong>Phone:</strong> ' . e($phone) . '</p>'
                . '<p><strong>Organization:</strong> ' . e($organization) . '</p>'
                . '<p><strong>Event Type:</strong> ' . e($eventType) . '</p>'
                . '<p><strong>Event Date:</strong> ' . e($eventDate) . '</p>'
                . '<p><strong>Event Location:</strong> ' . e($eventLocation) . '</p>'
                . '<p><strong>Budget Range:</strong> ' . e($budgetRange) . '</p>'
                . '<p><strong>Message:</strong><br>' . nl2br(e($message)) . '</p>'
            );

            flash_set('success', 'Your booking request has been received! Our team will contact you shortly.');
            redirect('/book.php?submitted=1');
        }
    }
}

require __DIR__ . '/includes/templates/header.php';
?>

<section class="page-title-band">
    <div class="container">
        <h1>Book A Celebrity</h1>
        <p>Tell us about your event in a few quick steps.</p>
    </div>
</section>

<section class="content-section">
    <div class="container narrow">

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <div class="wizard-steps" id="wizardSteps">
            <span class="step-dot active" data-step-dot="1">1. Celebrity</span>
            <span class="step-dot" data-step-dot="2">2. Event Details</span>
            <span class="step-dot" data-step-dot="3">3. Your Info</span>
            <span class="step-dot" data-step-dot="4">4. Review</span>
        </div>

        <form method="post" id="bookingForm" novalidate>
            <?= csrf_field() ?>
            <div style="position:absolute;left:-5000px" aria-hidden="true">
                <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <!-- Step 1: Celebrity -->
            <div class="wizard-step" data-step="1">
                <div class="form-row">
                    <label>Which celebrity would you like to book?
                        <select name="celebrity_id" id="celebritySelect">
                            <option value="">Not sure yet / General inquiry</option>
                            <?php foreach ($celebrities as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"
                                    data-slug="<?= e($c['slug']) ?>"
                                    <?= $preselectedSlug === $c['slug'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?> &mdash; <?= e($c['category']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="wizard-actions">
                    <span></span>
                    <button type="button" class="btn btn-primary wizard-next">Next</button>
                </div>
            </div>

            <!-- Step 2: Event Details -->
            <div class="wizard-step" data-step="2" hidden>
                <div class="form-row">
                    <label>Event Type
                        <input type="text" name="event_type" placeholder="e.g. Corporate gala, conference, wedding">
                    </label>
                </div>
                <div class="form-row-group">
                    <label>Event Date
                        <input type="date" name="event_date">
                    </label>
                    <label>Budget Range
                        <select name="budget_range">
                            <option value="">Select a range</option>
                            <option value="Under $10,000">Under $10,000</option>
                            <option value="$10,000 - $50,000">$10,000 - $50,000</option>
                            <option value="$50,000 - $100,000">$50,000 - $100,000</option>
                            <option value="$100,000+">$100,000+</option>
                        </select>
                    </label>
                </div>
                <div class="form-row">
                    <label>Event Location
                        <input type="text" name="event_location" placeholder="City, Country / Venue">
                    </label>
                </div>
                <div class="wizard-actions">
                    <button type="button" class="btn btn-outline wizard-back">Back</button>
                    <button type="button" class="btn btn-primary wizard-next">Next</button>
                </div>
            </div>

            <!-- Step 3: Contact Info -->
            <div class="wizard-step" data-step="3" hidden>
                <div class="form-row-group">
                    <label>Full Name *
                        <input type="text" name="full_name" required>
                    </label>
                    <label>Email *
                        <input type="email" name="email" required>
                    </label>
                </div>
                <div class="form-row-group">
                    <label>Phone
                        <input type="tel" name="phone">
                    </label>
                    <label>Organization / Company
                        <input type="text" name="organization">
                    </label>
                </div>
                <div class="form-row">
                    <label>Additional Message
                        <textarea name="message" rows="5" placeholder="Tell us more about your event"></textarea>
                    </label>
                </div>
                <div class="wizard-actions">
                    <button type="button" class="btn btn-outline wizard-back">Back</button>
                    <button type="button" class="btn btn-primary wizard-next">Review</button>
                </div>
            </div>

            <!-- Step 4: Review -->
            <div class="wizard-step" data-step="4" hidden>
                <div class="review-box" id="reviewBox"></div>
                <div class="wizard-actions">
                    <button type="button" class="btn btn-outline wizard-back">Back</button>
                    <button type="submit" class="btn btn-primary btn-lg">Submit Booking Request</button>
                </div>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>
