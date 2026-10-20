<?php
/**
 * Session guard — require this at the very top of any admin/*.php page
 * that should require login, before any output (it may redirect).
 * admin/login.php does NOT include this (it would redirect to itself);
 * it duplicates the session setup instead.
 */
require_once __DIR__ . '/../config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (empty($_SESSION['admin_user_id'])) {
    $currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
    header('Location: login.php?redirect=' . urlencode($currentScript));
    exit;
}

// The admin area underpins every action in a live database. If MySQL is
// unreachable, fail clearly here instead of letting forms silently no-op.
if (!db_available()) {
    http_response_code(503);
    header('Retry-After: 60');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Database unavailable — Brightframe Technologies Admin</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="stylesheet" href="../css/styles.css?v=<?= @filemtime(__DIR__ . '/../css/styles.css') ?: '1' ?>">
    </head>
    <body>
    <div class="admin-login-wrap bf-dark">
      <div class="admin-login-card">
        <h1>Database unavailable</h1>
        <p class="admin-login-sub">The admin area can't reach the database right now. The reason has been written to <code>error.log</code>.</p>
        <p class="admin-login-sub">Check the <code>DB_*</code> values in your project-root <code>.env</code>, make sure MySQL is running, then reload this page.</p>
        <p><a class="admin-btn admin-btn-primary" href="../">View the public site</a></p>
        <p><a href="logout.php">Log out</a></p>
      </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// CSRF token — generated once per session, used by every admin POST form.
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

/**
 * Validate the CSRF token from a POST request. Call this at the top of
 * every admin POST handler. Dies with a 400 if the token is missing or
 * doesn't match the session value.
 */
function admin_csrf_check(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['admin_csrf'] ?? '', $token)) {
        http_response_code(400);
        exit('Invalid request.');
    }
}
