<?php
/**
 * VMGT BOOKINGS - Main configuration
 *
 * Every value can be set with an environment variable (used on Render / Docker)
 * or edited directly below (used on cPanel). Environment variables win.
 */

function env_val(string $key, $default = '')
{
    $v = getenv($key);
    return ($v === false || $v === '') ? $default : $v;
}

// Behind Render's / any reverse proxy's HTTPS terminator
if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// ---- Database ----
// DB_DRIVER: 'mysql' (cPanel) or 'sqlite' (Render with a persistent disk)
define('DB_DRIVER', env_val('DB_DRIVER', 'mysql'));

// MySQL settings (cPanel). Ignored when DB_DRIVER=sqlite.
define('DB_HOST', env_val('DB_HOST', 'localhost'));
define('DB_NAME', env_val('DB_NAME', 'your_cpanel_db_name'));
define('DB_USER', env_val('DB_USER', 'your_cpanel_db_user'));
define('DB_PASS', env_val('DB_PASS', 'your_cpanel_db_password'));

// SQLite settings (Render). DATA_DIR should be the persistent disk mount path.
define('DATA_DIR', rtrim(env_val('DATA_DIR', __DIR__ . '/storage'), '/'));
define('DB_SQLITE_PATH', env_val('DB_SQLITE_PATH', DATA_DIR . '/vmgt.sqlite'));

// ---- Site ----
define('SITE_URL', rtrim(env_val('SITE_URL', env_val('RENDER_EXTERNAL_URL', 'https://vmgtbookings.com')), '/'));
define('SITE_NAME', env_val('SITE_NAME', 'VMGT BOOKINGS'));
define('ADMIN_EMAIL', env_val('ADMIN_EMAIL', 'admin@example.com')); // receives booking/contact notifications

// ---- Email (SMTP). Leave SMTP_HOST empty to fall back to PHP mail() ----
define('SMTP_HOST', env_val('SMTP_HOST', ''));
define('SMTP_PORT', (int)env_val('SMTP_PORT', 587));
define('SMTP_USER', env_val('SMTP_USER', ''));
define('SMTP_PASS', env_val('SMTP_PASS', ''));
define('SMTP_SECURE', env_val('SMTP_SECURE', 'tls')); // 'tls' (STARTTLS, 587) or 'ssl' (465) or ''
define('MAIL_FROM', env_val('MAIL_FROM', ''));         // defaults to no-reply@your-domain

// ---- Uploads ----
// UPLOADS_PATH is a persistent folder on Render (served at /uploads via Apache Alias).
$uploadsBase = rtrim(env_val('UPLOADS_PATH', __DIR__ . '/uploads'), '/');
define('UPLOAD_DIR_CELEB', $uploadsBase . '/celebrities');
define('UPLOAD_URL_CELEB', SITE_URL . '/uploads/celebrities');
define('UPLOAD_DIR_BLOG', $uploadsBase . '/blog');
define('UPLOAD_URL_BLOG', SITE_URL . '/uploads/blog');
define('MAX_UPLOAD_BYTES', 3 * 1024 * 1024); // 3MB

// ---- Security ----
// Change this to a long random string. Set APP_SECRET as an env var on Render.
define('APP_SECRET', env_val('APP_SECRET', 'change-this-to-a-long-random-string-1234567890'));

// ---- Session (skipped for command-line scripts such as the bootstrap) ----
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    if (!empty($_SERVER['HTTPS'])) {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// ---- Error display (turn OFF on live production) ----
error_reporting(E_ALL);
ini_set('display_errors', env_val('APP_DEBUG', '0') === '1' ? '1' : '0');
date_default_timezone_set('Africa/Lagos');

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
