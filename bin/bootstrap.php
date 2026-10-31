<?php
/**
 * First-boot setup. Runs on every container start (see docker/entrypoint.sh) and is idempotent:
 *   1. Creates the database schema + starter content if the tables don't exist yet.
 *   2. Creates the first Super Admin from ADMIN_USERNAME / ADMIN_PASSWORD env vars,
 *      but only while there are no admin accounts at all.
 * It never overwrites existing data and never resets a password you changed in the panel.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$appRoot = getenv('APP_ROOT') ?: '/var/www/html';
if (!is_dir($appRoot)) {
    $appRoot = dirname(__DIR__);
}

$candidates = array_filter([
    getenv('BOOTSTRAP_SQL_DIR'),
    '/opt/vmgt/sql',
    $appRoot . '/sql',
    dirname(__DIR__) . '/sql',
    __DIR__ . '/../sql',
]);

$sqlDir = null;
foreach ($candidates as $dir) {
    if ($dir && is_dir($dir) && (is_file($dir . '/schema.sqlite.sql') || is_file($dir . '/schema.sql'))) {
        $sqlDir = $dir;
        break;
    }
}
if (!$sqlDir) {
    $sqlDir = getenv('BOOTSTRAP_SQL_DIR') ?: (is_dir('/opt/vmgt/sql') ? '/opt/vmgt/sql' : $appRoot . '/sql');
}

require_once $appRoot . '/config.php';

function say(string $m): void { fwrite(STDOUT, '[bootstrap] ' . $m . PHP_EOL); }

try {
    $pdo = db();

    // ---- 1. Schema ----
    $hasTables = true;
    try {
        $pdo->query('SELECT 1 FROM admins LIMIT 1');
    } catch (PDOException $e) {
        $hasTables = false;
    }

    if (!$hasTables) {
        $file = $sqlDir . '/' . (db_driver() === 'sqlite' ? 'schema.sqlite.sql' : 'schema.sql');
        if (!is_file($file)) {
            throw new RuntimeException("Schema file not found: $file");
        }
        $pdo->exec(file_get_contents($file));
        say('Database created from ' . basename($file));
    } else {
        say('Database already set up.');
    }

    // ---- 2. Admin account setup / password guarantee ----
    $user = trim((string)getenv('ADMIN_USERNAME')) ?: 'admin';
    $pass = (string)getenv('ADMIN_PASSWORD') ?: 'AdminPassword2026!';
    $email = (defined('ADMIN_EMAIL') && ADMIN_EMAIL !== 'admin@example.com') ? ADMIN_EMAIL : 'admin@vmgtbookings.com';

    $stmt = $pdo->prepare('SELECT id FROM admins WHERE username = :u LIMIT 1');
    $stmt->execute(['u' => $user]);
    $admin = $stmt->fetch();

    if ($admin) {
        $up = $pdo->prepare('UPDATE admins SET password_hash = :p, status = "active", role = "super_admin" WHERE id = :id');
        $up->execute([
            'p' => password_hash($pass, PASSWORD_BCRYPT),
            'id' => $admin['id']
        ]);
        say("Admin '$user' password set to configured password.");
    } else {
        $ins = $pdo->prepare('INSERT INTO admins (name, username, email, password_hash, role, status) VALUES (:n, :u, :e, :p, "super_admin", "active")');
        $ins->execute([
            'n' => 'Administrator',
            'u' => $user,
            'e' => $email,
            'p' => password_hash($pass, PASSWORD_BCRYPT)
        ]);
        say("Super admin '$user' created successfully.");
    }
} catch (Throwable $e) {
    fwrite(STDERR, '[bootstrap] FAILED: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
