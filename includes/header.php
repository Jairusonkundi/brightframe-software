<?php
/**
 * Shared <head> + opening markup.
 * Pages may set $pageTitle / $pageDescription before including this file.
 */
$pageTitle       = $pageTitle ?? 'Brightframe Software — Software, web, and product development';
$pageDescription = $pageDescription ?? "Brightframe Software builds full-stack web applications, custom software, and the integrations that connect them — designed to be clear, reliable, and easy for your team to actually use.";
$siteUrl         = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:url" content="<?= htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:site_name" content="Brightframe Software">
<meta property="og:image" content="<?= htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8') ?>/assets/og-image.svg">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">

<link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
<link rel="canonical" href="<?= htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8') ?>/">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/styles.css">
</head>
<body>
<script>
  // Progressive enhancement gate for scroll-reveal animations (css/styles.css,
  // js/main.js): only enable them when JS actually runs AND the visitor
  // hasn't asked for reduced motion. Inline + synchronous so it applies
  // before first paint, avoiding any flash of hidden content.
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.documentElement.classList.add('reveal-js');
  }
</script>

<?php include __DIR__ . '/topbar.php'; ?>
<?php include __DIR__ . '/nav.php'; ?>
