<?php
/**
 * Contact message handler — for the simple general-inquiry form
 * (contact.php), deliberately separate from handlers/quote_handler.php.
 * Validates input, rejects likely bots via honeypot, stores the message
 * in `contact_messages`, and attempts to email a notification. Always
 * responds with JSON so js/main.js can update the page without a reload.
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
    respond(true, "Thanks — we'll be in touch soon.");
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];

if ($name === '' || mb_strlen($name) > 150) {
    $errors[] = 'Please enter your name.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors[] = 'Please enter a valid email address.';
}
if ($subject === '' || mb_strlen($subject) > 150) {
    $errors[] = 'Please enter a subject.';
}
if ($message === '') {
    $errors[] = 'Please enter a message.';
}

if (!empty($errors)) {
    respond(false, implode(' ', $errors), 422);
}

// --- Save the message ------------------------------------------------------
try {
    $stmt = $pdo->prepare(
        'INSERT INTO contact_messages (name, email, subject, message)
         VALUES (:name, :email, :subject, :message)'
    );
    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':subject' => $subject,
        ':message' => $message,
    ]);
} catch (PDOException $e) {
    error_log('Failed to save contact message: ' . $e->getMessage());
    respond(false, 'Sorry, we could not send your message. Please try again later.', 500);
}

// --- Notify by email ------------------------------------------------------
// Sent through includes/mailer.php (PHPMailer + SMTP from config/.env).
// Delivery failing never breaks the flow — the message is already saved.
require_once __DIR__ . '/../includes/mailer.php';

$adminEmail = env('ADMIN_EMAIL', 'jairusonkundi@gmail.com');

$subjectLine = 'New contact message — Brightframe Software';
$body        = "New message from the website contact form:\n\n"
             . "Name: {$name}\n"
             . "Email: {$email}\n"
             . "Subject: {$subject}\n\n"
             . "Message:\n{$message}\n";

send_email_notification($adminEmail, $subjectLine, $body, $email);

respond(true, "Thanks — we'll be in touch soon.");
