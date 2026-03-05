<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf'];
}

function csrf_check(): void {
  $t = $_POST['csrf'] ?? '';
  if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
    http_response_code(400);
    exit("Invalid CSRF token.");
  }
}