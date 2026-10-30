<?php
// Render health check: confirms PHP is up and the database is reachable.
require_once __DIR__ . '/config.php';
header('Content-Type: text/plain');
header('Cache-Control: no-store');
try {
    db()->query('SELECT 1')->fetch();
    echo 'ok';
} catch (Throwable $e) {
    http_response_code(500);
    echo 'db error';
}
