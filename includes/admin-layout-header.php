<?php
/**
 * Shared admin shell — dark sidebar + translucent topbar + main content.
 * Every admin/*.php page (except login.php, which isn't behind the
 * sidebar) sets $pageTitle and $adminActive (one of: dashboard, quotes,
 * messages, reviews, services, stats) before including this, then
 * includes admin-layout-footer.php at the end.
 *
 * Expects includes/admin-auth.php and config/db.php to already be
 * required by the including page (both are needed before this: auth
 * must run first to gate the page, and $pdo is used below for the
 * sidebar's badge counts).
 */
try {
    $adminBadgeCounts = [
        'quotes'   => (int) $pdo->query("SELECT COUNT(*) FROM quote_requests WHERE status = 'new'")->fetchColumn(),
        'messages' => (int) $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn(),
        'reviews'  => (int) $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn(),
    ];
} catch (PDOException $e) {
    error_log('Failed to load admin sidebar badge counts: ' . $e->getMessage());
    $adminBadgeCounts = ['quotes' => 0, 'messages' => 0, 'reviews' => 0];
}

$adminNavIcons = [
    'dashboard' => '<svg class="admin-nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>',
    'quotes'    => '<svg class="admin-nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h4"/></svg>',
    'messages'  => '<svg class="admin-nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>',
    'reviews'   => '<svg class="admin-nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.5 15.1 9 22.3 10 17 15 18.3 22 12 18.6 5.7 22 7 15 1.7 10 8.9 9Z"/></svg>',
    'services'  => '<svg class="admin-nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/><path d="M12 12 7 7M12 12l5 5M12 12l-5 5M12 12l5-5"/></svg>',
    'stats'     => '<svg class="admin-nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><rect x="8" y="9" width="3" height="8" rx="0.6"/><rect x="13.5" y="5" width="3" height="12" rx="0.6"/></svg>',
];

$adminNavItems = [
    'dashboard' => ['label' => 'Dashboard',        'href' => 'index.php',    'badge' => null],
    'quotes'    => ['label' => 'Quote Requests',   'href' => 'quotes.php',   'badge' => $adminBadgeCounts['quotes']],
    'messages'  => ['label' => 'Contact Messages', 'href' => 'messages.php', 'badge' => $adminBadgeCounts['messages']],
    'reviews'   => ['label' => 'Reviews',          'href' => 'reviews.php',  'badge' => $adminBadgeCounts['reviews']],
    'services'  => ['label' => 'Services',         'href' => 'services.php', 'badge' => null],
    'stats'     => ['label' => 'Site Stats',       'href' => 'stats.php',    'badge' => null],
];

$adminUsername = $_SESSION['admin_username'] ?? 'Admin';
$adminInitial  = strtoupper(substr(trim($adminUsername), 0, 1)) ?: 'A';
$adminSection  = $adminNavItems[$adminActive]['label'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= htmlspecialchars($pageTitle ?? 'Admin', ENT_QUOTES, 'UTF-8') ?> — Brightframe Software Admin</title>
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
<body class="admin-body">
<div class="admin-shell">

  <aside class="admin-sidebar" id="admin-sidebar">
    <div class="admin-sidebar-brand">
      <span class="admin-brand-logo">
        <svg width="22" height="22" viewBox="0 0 32 32" role="img" aria-hidden="true">
          <defs>
            <linearGradient id="logoSparkAdmin" x1="10" y1="10" x2="22" y2="22" gradientUnits="userSpaceOnUse">
              <stop offset="0" stop-color="#159F7A"/>
              <stop offset="1" stop-color="#5660EF"/>
            </linearGradient>
          </defs>
          <path d="M6 13 L6 7 L13 7" stroke="#134E4A" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M19 7 L26 7 L26 13" stroke="#134E4A" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M6 19 L6 25 L13 25" stroke="#134E4A" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M26 19 L26 25 L19 25" stroke="#134E4A" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M16 11 L17.3 14.7 L21 16 L17.3 17.3 L16 21 L14.7 17.3 L11 16 L14.7 14.7 Z" fill="url(#logoSparkAdmin)"/>
        </svg>
      </span>
      <div>
        <div class="admin-sidebar-brand-name">Brightframe</div>
        <div class="admin-sidebar-brand-sub">Admin Console</div>
      </div>
    </div>

    <div class="admin-sidebar-label">Main menu</div>

    <nav class="admin-sidebar-nav" aria-label="Admin sections">
      <?php foreach ($adminNavItems as $key => $item): ?>
        <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>" class="admin-sidebar-link<?= $adminActive === $key ? ' is-active' : '' ?>">
          <?= $adminNavIcons[$key] ?>
          <span class="label"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php if (!empty($item['badge'])): ?>
            <span class="admin-sidebar-badge"><?= (int) $item['badge'] ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="admin-sidebar-user">
      <span class="admin-sidebar-avatar"><?= htmlspecialchars($adminInitial, ENT_QUOTES, 'UTF-8') ?></span>
      <div class="admin-sidebar-user-info">
        <div class="admin-sidebar-user-name"><?= htmlspecialchars($adminUsername, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="admin-sidebar-user-role">Administrator</div>
      </div>
      <a href="logout.php" class="admin-sidebar-logout" title="Log out" aria-label="Log out">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
      </a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <button type="button" class="admin-sidebar-toggle" id="admin-sidebar-toggle" aria-expanded="false" aria-controls="admin-sidebar" aria-label="Toggle admin menu">
        <span></span><span></span><span></span>
      </button>
      <div class="admin-topbar-brand">
        <svg width="18" height="18" viewBox="0 0 32 32" role="img" aria-hidden="true">
          <path d="M6 13 L6 7 L13 7" stroke="#159F7A" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M19 7 L26 7 L26 13" stroke="#159F7A" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M6 19 L6 25 L13 25" stroke="#159F7A" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M26 19 L26 25 L19 25" stroke="#159F7A" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span><?= htmlspecialchars($adminSection, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <div class="admin-topbar-actions">
        <button type="button" class="theme-toggle" aria-label="Switch to dark mode" title="Switch to dark mode">
          <svg class="icon-moon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
          <svg class="icon-sun" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
        </button>
        <a href="../index.php" target="_blank" rel="noopener" class="admin-topbar-link">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="m10 14 11-11"/></svg>
          View site
        </a>
        <span class="admin-topbar-avatar"><?= htmlspecialchars($adminInitial, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </header>

    <main class="admin-main-inner">