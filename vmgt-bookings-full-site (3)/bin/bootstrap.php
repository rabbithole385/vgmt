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

$appRoot = getenv('APP_ROOT') ?: dirname(__DIR__);
$sqlDir  = getenv('BOOTSTRAP_SQL_DIR') ?: $appRoot . '/sql';

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

    // ---- 2. First admin ----
    $adminCount = (int)$pdo->query('SELECT COUNT(*) AS c FROM admins')->fetch()['c'];
    if ($adminCount === 0) {
        $user = trim((string)getenv('ADMIN_USERNAME'));
        $pass = (string)getenv('ADMIN_PASSWORD');
        if ($user === '' || $pass === '') {
            say('No admin exists and ADMIN_USERNAME / ADMIN_PASSWORD are not set. '
              . 'Set them in the Render dashboard (Environment tab) and redeploy.');
        } elseif (strlen($pass) < 8) {
            say('ADMIN_PASSWORD must be at least 8 characters. Admin NOT created.');
        } else {
            $email = ADMIN_EMAIL !== 'admin@example.com' ? ADMIN_EMAIL : 'admin@localhost';
            $stmt = $pdo->prepare(
                'INSERT INTO admins (name, username, email, password_hash, role) VALUES (:n, :u, :e, :p, :r)'
            );
            $stmt->execute([
                'n' => 'Administrator', 'u' => $user, 'e' => $email,
                'p' => password_hash($pass, PASSWORD_BCRYPT), 'r' => 'super_admin',
            ]);
            say("Super admin '$user' created. Log in at /admin/login.php");
        }
    } else {
        say("Admin accounts present ($adminCount).");
    }
} catch (Throwable $e) {
    fwrite(STDERR, '[bootstrap] FAILED: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
