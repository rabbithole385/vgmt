<?php
/**
 * PDO connection (shared singleton via db()). Supports MySQL and SQLite.
 */

function db_driver(): string
{
    return DB_DRIVER === 'sqlite' ? 'sqlite' : 'mysql';
}

/** "INSERT IGNORE" on MySQL, "INSERT OR IGNORE" on SQLite. */
function sql_insert_ignore(): string
{
    return db_driver() === 'sqlite' ? 'INSERT OR IGNORE' : 'INSERT IGNORE';
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            if (db_driver() === 'sqlite') {
                $dir = dirname(DB_SQLITE_PATH);
                if (!is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
                $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null, $options);
                $pdo->exec('PRAGMA foreign_keys = ON');
                $pdo->exec('PRAGMA busy_timeout = 5000');
                $pdo->exec('PRAGMA journal_mode = WAL');
            } else {
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            }
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            if (PHP_SAPI === 'cli') {
                throw $e;
            }
            http_response_code(500);
            die('Site is temporarily unavailable. Please try again shortly.');
        }
    }
    return $pdo;
}
