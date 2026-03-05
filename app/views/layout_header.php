<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/csrf.php';
$u = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h(APP_NAME) ?></title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Optional premium font (online). Remove if you want offline-only -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

  <!-- Your theme CSS -->
  <link href="assets/app.css" rel="stylesheet">
</head>

<body>
  <!-- Top gradient bar (subtle branding) -->
  <div style="height:4px;background:linear-gradient(90deg,#2563eb,#16a34a);"></div>

  <!-- Bootstrap JS (needed for modals/dropdowns) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

  <!-- Your JS -->
  <script src="assets/app.js" defer></script>
  </body>
  </html>