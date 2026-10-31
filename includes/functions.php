<?php
/**
 * Shared helper functions used across the public site and admin panel.
 */

// ---------------------------------------------------------------
// Output / sanitization helpers
// ---------------------------------------------------------------
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function slugify(string $text): string
{
    $text = trim($text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item-' . substr(md5(uniqid('', true)), 0, 8);
}

function unique_slug(string $baseSlug, string $table, ?int $ignoreId = null): string
{
    $pdo = db();
    $slug = $baseSlug;
    $i = 1;
    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = :slug";
        if ($ignoreId) {
            $sql .= " AND id != :id";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        if ($ignoreId) {
            $stmt->bindValue(':id', $ignoreId, PDO::PARAM_INT);
        }
        $stmt->execute();
        if (!$stmt->fetch()) {
            return $slug;
        }
        $i++;
        $slug = $baseSlug . '-' . $i;
    }
}

// ---------------------------------------------------------------
// Flash messages (one-time session messages, e.g. "Saved successfully")
// ---------------------------------------------------------------
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_get_all(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

// ---------------------------------------------------------------
// CSRF protection
// ---------------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ---------------------------------------------------------------
// Simple per-form rate limiting (basic brute-force / spam protection)
// ---------------------------------------------------------------
function too_many_requests(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool
{
    $now = time();
    $bucket = $_SESSION['rate_' . $key] ?? ['count' => 0, 'start' => $now];
    if ($now - $bucket['start'] > $windowSeconds) {
        $bucket = ['count' => 0, 'start' => $now];
    }
    $bucket['count']++;
    $_SESSION['rate_' . $key] = $bucket;
    return $bucket['count'] > $maxAttempts;
}

// ---------------------------------------------------------------
// Site settings (key/value store)
// ---------------------------------------------------------------
function get_setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $stmt = db()->query('SELECT setting_key, setting_value FROM site_settings');
        foreach ($stmt->fetchAll() as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache[$key] ?? $default;
}

function set_setting(string $key, string $value): void
{
    if (db_driver() === 'sqlite') {
        $stmt = db()->prepare(
            'INSERT INTO site_settings (setting_key, setting_value) VALUES (:k, :v)
             ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value'
        );
        $stmt->execute(['k' => $key, 'v' => $value]);
        return;
    }
    $stmt = db()->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE setting_value = :v2'
    );
    $stmt->execute(['k' => $key, 'v' => $value, 'v2' => $value]);
}

// ---------------------------------------------------------------
// Image upload handler (used by admin for celebrities & blog posts)
// ---------------------------------------------------------------
function handle_image_upload(array $file, string $targetDir, ?string $oldFile = null): array
{
    // Returns ['ok' => bool, 'filename' => string|null, 'error' => string|null]
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'filename' => $oldFile, 'error' => null]; // no new file chosen, keep old
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'filename' => null, 'error' => 'Upload failed (error code ' . $file['error'] . ').'];
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        return ['ok' => false, 'filename' => null, 'error' => 'Image is too large. Max size is 3MB.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!isset($allowed[$mime])) {
        return ['ok' => false, 'filename' => null, 'error' => 'Only JPG, PNG, WEBP or GIF images are allowed.'];
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $newName = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    $destination = rtrim($targetDir, '/') . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['ok' => false, 'filename' => null, 'error' => 'Could not save uploaded file.'];
    }

    // Remove old file if it existed and isn't a shared/default asset
    if ($oldFile) {
        $oldPath = rtrim($targetDir, '/') . '/' . $oldFile;
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    return ['ok' => true, 'filename' => $newName, 'error' => null];
}

// ---------------------------------------------------------------
// Notification email (uses PHP mail(); swap in SMTP later if needed)
// ---------------------------------------------------------------
function send_notification_email(string $to, string $subject, string $bodyHtml): bool
{
    $fromAddress = MAIL_FROM !== '' ? MAIL_FROM : 'no-reply@' . (parse_url(SITE_URL, PHP_URL_HOST) ?: 'localhost');

    if (SMTP_HOST !== '') {
        return smtp_send($to, $subject, $bodyHtml, $fromAddress);
    }

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . SITE_NAME . ' <' . $fromAddress . '>';
    return @mail($to, $subject, $bodyHtml, implode("\r\n", $headers));
}

/**
 * Minimal SMTP client (AUTH LOGIN, STARTTLS or implicit SSL). Needed on hosts
 * like Render where PHP mail() does not work. Never throws; returns false on failure.
 */
function smtp_send(string $to, string $subject, string $bodyHtml, string $fromAddress): bool
{
    $host = SMTP_HOST;
    $port = SMTP_PORT;
    $remote = (SMTP_SECURE === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;

    $fp = @stream_socket_client($remote, $errno, $errstr, 12);
    if (!$fp) {
        error_log("SMTP connect failed: $errstr ($errno)");
        return false;
    }
    stream_set_timeout($fp, 12);

    $read = function () use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };
    $send = function (string $cmd, string $expect) use ($fp, $read): bool {
        fwrite($fp, $cmd . "\r\n");
        $resp = $read();
        if (strpos($resp, $expect) !== 0) {
            error_log('SMTP unexpected reply to ' . (stripos($cmd, 'AUTH') === 0 ? 'AUTH' : $cmd) . ': ' . trim($resp));
            return false;
        }
        return true;
    };

    try {
        if (strpos($read(), '220') !== 0) {
            fclose($fp);
            return false;
        }
        $ehloHost = parse_url(SITE_URL, PHP_URL_HOST) ?: 'localhost';
        if (!$send('EHLO ' . $ehloHost, '250')) { fclose($fp); return false; }

        if (SMTP_SECURE === 'tls') {
            if (!$send('STARTTLS', '220')) { fclose($fp); return false; }
            if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                error_log('SMTP STARTTLS handshake failed');
                fclose($fp);
                return false;
            }
            if (!$send('EHLO ' . $ehloHost, '250')) { fclose($fp); return false; }
        }

        if (SMTP_USER !== '') {
            if (!$send('AUTH LOGIN', '334')
                || !$send(base64_encode(SMTP_USER), '334')
                || !$send(base64_encode(SMTP_PASS), '235')) {
                fclose($fp);
                return false;
            }
        }

        $fromName = str_replace(['"', "\r", "\n"], '', SITE_NAME);
        $headers = [
            'Date: ' . date('r'),
            'From: "' . $fromName . '" <' . $fromAddress . '>',
            'To: <' . $to . '>',
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];
        $body = chunk_split(base64_encode($bodyHtml));

        if (!$send('MAIL FROM:<' . $fromAddress . '>', '250')
            || !$send('RCPT TO:<' . $to . '>', '25')
            || !$send('DATA', '354')) {
            fclose($fp);
            return false;
        }
        fwrite($fp, implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n");
        $ok = strpos($read(), '250') === 0;
        $send('QUIT', '221');
        fclose($fp);
        return $ok;
    } catch (Throwable $e) {
        error_log('SMTP error: ' . $e->getMessage());
        return false;
    }
}

// ---------------------------------------------------------------
// Pagination helper
// ---------------------------------------------------------------
function paginate_params(int $defaultPerPage = 15): array
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = $defaultPerPage;
    $offset = ($page - 1) * $perPage;
    return [$page, $perPage, $offset];
}

function format_date(?string $datetime, string $format = 'M j, Y'): string
{
    if (!$datetime) {
        return '';
    }
    $ts = strtotime($datetime);
    return $ts ? date($format, $ts) : '';
}

// ---------------------------------------------------------------
// VIP Vacations & Luxury Getaways
// ---------------------------------------------------------------
function get_vacations(): array
{
    static $vacations = null;
    if ($vacations !== null) {
        return $vacations;
    }

    $vacations = [
        [
            'id' => 1,
            'slug' => 'maldives-atoll-keanu',
            'title' => 'Maldives Private Island & Sunset Cruise',
            'destination' => 'Baa Atoll, Maldives',
            'duration' => '5 Days / 4 Nights',
            'celebrity_host' => 'Keanu Reeves',
            'host_image' => 'Keanu-Reeves.png',
            'host_slug' => 'keanu-reeves',
            'image' => 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1200&q=80',
            'price' => '$14,500 / guest',
            'tag' => 'Ultra VIP',
            'category' => 'Island Luxury',
            'rating' => '5.0 (24 reviews)',
            'highlights' => ['Private Overwater Villa', 'Helicopter Island Transfer', 'Exclusive Sunset Dinner Cruise', 'Private 1-on-1 Fireside Chat'],
            'description' => 'Escape to an ultra-private 5-star Maldivian coral atoll. Enjoy crystalline turquoise lagoons, private coral reef diving, and intimate seaside dinners hosted in an exclusive small-group retreat alongside Keanu Reeves.'
        ],
        [
            'id' => 2,
            'slug' => 'swiss-alps-hugh-jackman',
            'title' => 'Swiss Alps Luxury Ski Chalet & Gala',
            'destination' => 'Zermatt, Switzerland',
            'duration' => '6 Days / 5 Nights',
            'celebrity_host' => 'Hugh Jackman',
            'host_image' => 'Hugh-Michael-Jackman.png',
            'host_slug' => 'hugh-jackman',
            'image' => 'https://images.unsplash.com/photo-1502784444187-359ac186c5bb?auto=format&fit=crop&w=1200&q=80',
            'price' => '$18,000 / guest',
            'tag' => 'Alpine Grandeur',
            'category' => 'Mountain Retreat',
            'rating' => '4.9 (19 reviews)',
            'highlights' => ['Matterhorn Panoramic Chalet', 'Private Heliskiing Passes', 'Michelin-Star Fireside Dining', 'VIP Creative Masterclass'],
            'description' => 'Ascend to the breathtaking peaks of Zermatt with Hugh Jackman. Experience fresh Alpine powder, private heated infinity pools with views of the Matterhorn, and five-star evening soirees by the fireplace.'
        ],
        [
            'id' => 3,
            'slug' => 'santorini-michele-morrone',
            'title' => 'Santorini Superyacht & Cliffside Villa',
            'destination' => 'Oia & Cyclades, Greece',
            'duration' => '4 Days / 3 Nights',
            'celebrity_host' => 'Michele Morrone',
            'host_image' => 'MICHELE-MORRONE.png',
            'host_slug' => 'michele-morrone',
            'image' => 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?auto=format&fit=crop&w=1200&q=80',
            'price' => '$12,900 / guest',
            'tag' => 'Mediterranean VIP',
            'category' => 'Yacht & Coast',
            'rating' => '5.0 (31 reviews)',
            'highlights' => ['Private 120ft Caldera Yacht', 'Cliffside Infinity Cave Suites', 'Private Vineyard Gastronomy', 'VIP Sunset Acoustic Gala'],
            'description' => 'Sail the Aegean Sea with Michele Morrone. Marvel at world-famous Oia sunsets from a luxury yacht, savor private Mediterranean feasts, and celebrate under the stars at an intimate clifftop estate.'
        ],
        [
            'id' => 4,
            'slug' => 'bali-park-seo-joon',
            'title' => 'Bali Rainforest Wellness & Zen Estate',
            'destination' => 'Ubud & Uluwatu, Bali',
            'duration' => '7 Days / 6 Nights',
            'celebrity_host' => 'Park Seo-joon',
            'host_image' => 'Park-Seo-joon.png',
            'host_slug' => 'park-seo-joon',
            'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80',
            'price' => '$9,800 / guest',
            'tag' => 'Zen Retreat',
            'category' => 'Wellness',
            'rating' => '4.9 (28 reviews)',
            'highlights' => ['Private River Bamboo Estate', 'Holistic Sound Baths & Spa', 'Curated Organic Culinary Experience', 'Intimate Cultural & Art Tour'],
            'description' => 'Recharge body and soul in the lush canopy of Ubud with Park Seo-joon. Features world-class wellness therapy, private infinity villa suites, and rejuvenating culinary journeys in paradise.'
        ],
        [
            'id' => 5,
            'slug' => 'french-riviera-jason-momoa',
            'title' => 'French Riviera Superyacht Voyage',
            'destination' => 'Cannes & Monte Carlo, France',
            'duration' => '5 Days / 4 Nights',
            'celebrity_host' => 'Jason Momoa',
            'host_image' => 'Jason-Momoa.png',
            'host_slug' => 'jason-momoa',
            'image' => 'https://images.unsplash.com/photo-1533105079780-92b9be482077?auto=format&fit=crop&w=1200&q=80',
            'price' => '$22,500 / guest',
            'tag' => 'High Glamour',
            'category' => 'Superyacht',
            'rating' => '5.0 (15 reviews)',
            'highlights' => ['150ft Tri-Deck Superyacht', 'Monaco VIP Club Access', 'Private Speedboat Safari', 'Exclusive Beach Club Soiree'],
            'description' => 'Live the epitome of Mediterranean glamour with Jason Momoa. Anchor along Cannes and Monte Carlo with private yacht suites, jet skis, and five-star culinary service.'
        ],
        [
            'id' => 6,
            'slug' => 'tokyo-kyoto-song-joong-ki',
            'title' => 'Tokyo Skyline & Kyoto Heritage Tour',
            'destination' => 'Tokyo & Kyoto, Japan',
            'duration' => '6 Days / 5 Nights',
            'celebrity_host' => 'Song Joong-ki',
            'host_image' => 'SONG-JOONG-KI.png',
            'host_slug' => 'song-joong-ki',
            'image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=1200&q=80',
            'price' => '$16,000 / guest',
            'tag' => 'Cultural VIP',
            'category' => 'Metropolis & Zen',
            'rating' => '4.9 (22 reviews)',
            'highlights' => ['Penthouse Tokyo Skyline Suite', 'First-Class Shinkansen Travel', 'Historic Ryokan Hot Spring', 'Private Kaiseki Masterclass'],
            'description' => 'Embark on an exclusive journey through Japan’s most captivating sights alongside Song Joong-ki. Experience private temple viewings in Kyoto, Michelin-starred culinary dining in Ginza, and traditional tea ceremonies.'
        ],
    ];

    return $vacations;
}

function get_vacation_by_slug(string $slug): ?array
{
    foreach (get_vacations() as $v) {
        if ($v['slug'] === $slug) {
            return $v;
        }
    }
    return null;
}
