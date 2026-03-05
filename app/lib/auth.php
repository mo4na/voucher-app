<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function login_user(array $userRow): void {
  $_SESSION['user'] = [
    'id' => (int)$userRow['id'],
    'full_name' => $userRow['full_name'],
    'username' => $userRow['username'],
    'role' => $userRow['role_name'],
  ];
}

function logout_user(): void {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
  }
  session_destroy();
}

function is_super_admin(): bool {
  $u = current_user();
  return $u && $u['role'] === 'SUPER_ADMIN';
}

function has_role(string $role): bool {
  $u = current_user();
  if (!$u) return false;
  return $u['role'] === 'SUPER_ADMIN' || $u['role'] === $role;
}

function require_login(): void {
  if (!current_user()) {
    header("Location: login.php");
    exit;
  }
}

function require_any_role(array $roles): void {
  require_login();
  $u = current_user();
  if ($u['role'] === 'SUPER_ADMIN') return;
  if (!in_array($u['role'], $roles, true)) {
    http_response_code(403);
    exit("Forbidden");
  }
}