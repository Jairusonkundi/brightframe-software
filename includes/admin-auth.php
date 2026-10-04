<?php
/**
 * Session guard — require this at the very top of any admin/*.php page
 * that should require login, before any output (it may redirect).
 * admin/login.php does NOT include this (it would redirect to itself);
 * it duplicates the session setup instead.
 */
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
