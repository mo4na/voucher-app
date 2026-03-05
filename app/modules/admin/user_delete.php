<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/csrf.php';
require_once __DIR__ . '/../../lib/logger.php';

require_any_role(['SUPER_ADMIN']);
csrf_check();

$id = (int)($_POST['id'] ?? 0);
$adminPass = (string)($_POST['admin_password'] ?? '');

$me = (int)(current_user()['id'] ?? 0);
if ($id <= 0) exit("Invalid id");
if ($id === $me) exit("You cannot delete your own account.");
if ($adminPass === '') exit("Password required.");

$meRow = $pdo->prepare("SELECT password_hash FROM users WHERE id=? AND deleted_at IS NULL LIMIT 1");
$meRow->execute([$me]);
$hash = $meRow->fetchColumn();

if (!$hash || !password_verify($adminPass, $hash)) {
  http_response_code(403);
  exit("Invalid SUPER_ADMIN password.");
}

$pdo->prepare("UPDATE users SET deleted_at = NOW() WHERE id=? AND deleted_at IS NULL")->execute([$id]);

log_action($pdo, $me, "Soft delete user", "user", $id, null);

header("Location: " . BASE_URL . "/admin_users.php");
exit;