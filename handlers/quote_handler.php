<?php
/**
 * Quote request handler (replaces the old generic contact_handler.php).
 * Validates input, rejects likely bots via honeypot, stores the request in
 * `quote_requests` + `quote_request_services`, and attempts to email a
 * notification. Always responds with JSON so js/main.js can update the
 * page without a reload.
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

$name           = trim($_POST['name'] ?? '');
$email          = trim($_POST['email'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$projectDetails = trim($_POST['project_details'] ?? '');
$budgetRange    = trim($_POST['budget_range'] ?? '');
$timeline       = trim($_POST['timeline'] ?? '');
$serviceIds     = array_filter(array_map('trim', $_POST['service_ids'] ?? []), 'ctype_digit');

// Controlled vocabularies — matches the <select> options in index.php.
// Validated here rather than trusted, since form fields can be tampered
// with client-side.
$validBudgets   = ['Under KSh 50,000', 'KSh 50,000–150,000', 'KSh 150,000–500,000', 'Above KSh 500,000', 'Not sure yet'];
$validTimelines = ['ASAP', 'Within 1 month', '1–3 months', 'Flexible'];

$errors = [];

if ($name === '' || mb_strlen($name) > 150) {
    $errors[] = 'Please enter your name.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors[] = 'Please enter a valid email address.';
}
if ($phone === '' || !preg_match('/^[0-9+\-\s()]{6,25}$/', $phone)) {
    $errors[] = 'Please enter a valid phone number.';
}
if ($projectDetails === '') {
    $errors[] = 'Please tell us a bit about your project.';
}
if (empty($serviceIds)) {
    $errors[] = 'Please select at least one service.';
}
if ($budgetRange !== '' && !in_array($budgetRange, $validBudgets, true)) {
    $errors[] = 'Please choose a valid budget range.';
}
if ($timeline !== '' && !in_array($timeline, $validTimelines, true)) {
    $errors[] = 'Please choose a valid timeline.';
}

if (!empty($errors)) {
    respond(false, implode(' ', $errors), 422);
}

// --- Resolve the selected service ids against the real catalog ------------
// Never trust client-submitted ids or titles directly — look each one up
// so quote_request_services always reflects a real (or once-real) service.
$placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
$stmt = $pdo->prepare("SELECT id, title FROM services WHERE id IN ($placeholders)");
$stmt->execute(array_map('intval', $serviceIds));
$matchedServices = $stmt->fetchAll();

if (empty($matchedServices)) {
    respond(false, 'Please select at least one valid service.', 422);
}

// --- Save the request -----------------------------------------------------
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO quote_requests (name, email, phone, project_details, budget_range, timeline)
         VALUES (:name, :email, :phone, :project_details, :budget_range, :timeline)'
    );
    $stmt->execute([
        ':name'            => $name,
        ':email'           => $email,
        ':phone'           => $phone,
        ':project_details' => $projectDetails,
        ':budget_range'    => $budgetRange !== '' ? $budgetRange : null,
        ':timeline'        => $timeline !== '' ? $timeline : null,
    ]);
    $quoteRequestId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'INSERT INTO quote_request_services (quote_request_id, service_id, service_title)
         VALUES (:quote_request_id, :service_id, :service_title)'
    );
    foreach ($matchedServices as $service) {
        $stmt->execute([
            ':quote_request_id' => $quoteRequestId,
            ':service_id'       => (int) $service['id'],
            ':service_title'    => $service['title'],
        ]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Failed to save quote request: ' . $e->getMessage());
    respond(false, 'Sorry, we could not save your request. Please try again later.', 500);
}

// --- Notify by email ------------------------------------------------------
// NOTE: PHP's mail() requires a configured local mail server (sendmail/SMTP).
// A stock XAMPP install on Windows has no mail server, so mail() will
// typically return false locally — that's expected in local development.
// The request is still saved to the database either way. For real
// delivery, configure sendmail in php.ini or switch to PHPMailer + SMTP.
$adminEmail = env('ADMIN_EMAIL', 'hello@brightframesoftware.com');
$mailFrom   = env('MAIL_FROM', 'no-reply@brightframesoftware.local');

$serviceTitles = implode(', ', array_column($matchedServices, 'title'));

$subject = 'New quote request — Brightframe Software';
$body    = "New quote request from the website:\n\n"
         . "Name: {$name}\n"
         . "Email: {$email}\n"
         . "Phone: {$phone}\n"
         . "Services: {$serviceTitles}\n"
         . 'Budget: ' . ($budgetRange !== '' ? $budgetRange : '—') . "\n"
         . 'Timeline: ' . ($timeline !== '' ? $timeline : '—') . "\n\n"
         . "Project details:\n{$projectDetails}\n";
$headers = "From: {$mailFrom}\r\nReply-To: {$email}\r\nContent-Type: text/plain; charset=UTF-8";

$mailSent = @mail($adminEmail, $subject, $body, $headers);
if (!$mailSent) {
    error_log('Quote request: mail() failed or is not configured on this server — request was still saved to the database.');
}

respond(true, "Thanks — we'll be in touch soon.");
