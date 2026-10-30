<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (too_many_requests('newsletter_subscribe', 8, 600)) {
    echo json_encode(['ok' => false, 'message' => 'Too many attempts. Please try again later.']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    $stmt = db()->prepare(sql_insert_ignore() . ' INTO subscribers (email) VALUES (:email)');
    $stmt->execute(['email' => $email]);
    echo json_encode(['ok' => true, 'message' => 'Thanks for subscribing!']);
} catch (Exception $e) {
    error_log('Subscribe error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'message' => 'Something went wrong. Please try again.']);
}
