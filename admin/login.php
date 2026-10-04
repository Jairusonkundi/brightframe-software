<?php
/**
 * Admin login — session-based auth for the admin panel. Every other
 * admin/*.php page requires includes/admin-auth.php, which redirects
 * here if not logged in.
 *
 * Protections: password_hash/password_verify (never plaintext), CSRF
 * token on the form, IP-based rate limiting (5 failed attempts / 15 min,
 * see login_attempts in database.sql), a generic error message either
 * way so a wrong username doesn't confirm which part was wrong, and
 * session_regenerate_id() on success to prevent session fixation.
 */
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Already logged in? Skip straight to the panel.
if (!empty($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}

// Only allow redirecting to a plain filename within admin/ — never an
// external URL or path (open-redirect guard).
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';
if (!preg_match('/^[a-zA-Z0-9_-]+\.php$/', $redirect)) {
    $redirect = 'index.php';
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Your session expired. Please try again.';
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        try {
            $attemptStmt = $pdo->prepare(
                'SELECT COUNT(*) FROM login_attempts WHERE ip_address = :ip AND attempted_at > (NOW() - INTERVAL 15 MINUTE)'
            );
            $attemptStmt->execute(['ip' => $ip]);
            $recentAttempts = (int) $attemptStmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Failed to check login attempts: ' . $e->getMessage());
            $recentAttempts = 0;
        }

        if ($recentAttempts >= 5) {
            $error = 'Too many failed attempts. Please try again in 15 minutes.';
        } else {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $user     = false;

            try {
                $userStmt = $pdo->prepare('SELECT id, password_hash FROM admin_users WHERE username = :username LIMIT 1');
                $userStmt->execute(['username' => $username]);
                $user = $userStmt->fetch();
            } catch (PDOException $e) {
                error_log('Failed to look up admin user: ' . $e->getMessage());
            }

            if ($user && password_verify($password, $user['password_hash'])) {
                try {
                    $pdo->prepare('DELETE FROM login_attempts WHERE ip_address = :ip')->execute(['ip' => $ip]);
                } catch (PDOException $e) {
                    error_log('Failed to clear login attempts: ' . $e->getMessage());
                }
                session_regenerate_id(true);
                $_SESSION['admin_user_id']  = $user['id'];
                $_SESSION['admin_username'] = $username;
                header('Location: ' . $redirect);
                exit;
            }

            try {
                $pdo->prepare('INSERT INTO login_attempts (ip_address) VALUES (:ip)')->execute(['ip' => $ip]);
            } catch (PDOException $e) {
                error_log('Failed to record login attempt: ' . $e->getMessage());
            }
            $error = 'Invalid username or password.';
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Admin Login — Brightframe Software';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
<link rel="stylesheet" href="../css/styles.css?v=<?= @filemtime(__DIR__ . '/../css/styles.css') ?: '1' ?>">
<script>
  // Apply saved theme before first paint to avoid a flash of the wrong
  // mode. Light mode is the default; only 'dark' opts into dark mode.
  try {
    if (localStorage.getItem('bf-theme') === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  } catch (e) {}
</script>
</head>
<body>
<div class="admin-login-wrap">
  <button type="button" class="theme-toggle admin-login-theme-toggle" aria-label="Switch to dark mode" title="Switch to dark mode">
    <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
    <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
  </button>
  <div class="admin-login-card">
    <div class="logo admin-login-logo">
      <svg width="30" height="30" viewBox="0 0 32 32" role="img" aria-hidden="true">
        <defs>
          <linearGradient id="logoSparkLogin" x1="10" y1="10" x2="22" y2="22" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#159F7A"/>
            <stop offset="1" stop-color="#5660EF"/>
          </linearGradient>
        </defs>
        <rect x="1" y="1" width="30" height="30" rx="9" fill="#EEF6F2"/>
        <path d="M6 13 L6 7 L13 7" stroke="#134E4A" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M19 7 L26 7 L26 13" stroke="#134E4A" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M6 19 L6 25 L13 25" stroke="#134E4A" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M26 19 L26 25 L19 25" stroke="#134E4A" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M16 11 L17.3 14.7 L21 16 L17.3 17.3 L16 21 L14.7 17.3 L11 16 L14.7 14.7 Z" fill="url(#logoSparkLogin)"/>
      </svg>
      Brightframe Software
    </div>
    <h1>Admin Login</h1>
    <p class="admin-login-sub">Sign in to manage submissions, reviews, and site stats.</p>

    <?php if ($error): ?>
      <p class="admin-login-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" class="admin-login-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>">
      <div class="admin-field">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" autocomplete="username" required autofocus>
      </div>
      <div class="admin-field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
      </div>
      <button type="submit" class="admin-btn admin-btn-primary admin-login-submit">Log in</button>
    </form>
  </div>
</div>
<script src="../js/main.js?v=<?= @filemtime(__DIR__ . '/../js/main.js') ?: '1' ?>"></script>
</body>
</html>
