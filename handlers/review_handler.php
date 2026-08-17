<?php
/**
 * Review submission handler — for reviews.php's "Share your experience"
 * form. Validates input, rejects likely bots via honeypot, and stores
 * the review in `reviews` with status='pending'. Nothing submitted here
 * appears on the public site until an admin approves it in
 * admin/reviews.php — see that file and reviews.php for the full flow.
 * Always responds with JSON so js/main.js can update the page without a
 * reload.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

function respond(bool $success, string $message, int $httpStatus = 200): void
{
    http_response_code($httpStatus);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.', 405);
}

// Honeypot: real visitors never see or fill this field. If it's filled,
// silently report success so bots don't learn to avoid the field.
if (!empty($_POST['website'])) {
    respond(true, 'Thanks for sharing your experience — it\'ll appear once reviewed.');
}

$name       = trim($_POST['name'] ?? '');
$company    = trim($_POST['company'] ?? '');
$rating     = (int) ($_POST['rating'] ?? 0);
$reviewText = trim($_POST['review_text'] ?? '');

$errors = [];

if ($name === '' || mb_strlen($name) > 150) {
    $errors[] = 'Please enter your name.';
}
if ($company !== '' && mb_strlen($company) > 150) {
    $errors[] = 'Company name is too long.';
}
if ($rating < 1 || $rating > 5) {
    $errors[] = 'Please choose a rating.';
}
if ($reviewText === '') {
    $errors[] = 'Please enter your review.';
}

if (!empty($errors)) {
    respond(false, implode(' ', $errors), 422);
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO reviews (name, company, rating, review_text, status)
         VALUES (:name, :company, :rating, :review_text, \'pending\')'
    );
    $stmt->execute([
        ':name'        => $name,
        ':company'     => $company !== '' ? $company : null,
        ':rating'      => $rating,
        ':review_text' => $reviewText,
    ]);
} catch (PDOException $e) {
    error_log('Failed to save review: ' . $e->getMessage());
    respond(false, 'Sorry, we could not save your review. Please try again later.', 500);
}

respond(true, "Thanks for sharing your experience — it'll appear on the site once we've reviewed it.");
