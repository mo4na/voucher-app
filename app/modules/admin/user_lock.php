<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/csrf.php';
require_once __DIR__ . '/../../lib/logger.php';

require_any_role(['SUPER_ADMIN']);
csrf_check();

$id = (int)($_POST['id'] ?? 0);
$minutes = (int)($_POST['minutes'] ?? 0);
$me = (int)(current_user()['id'] ?? 0);

if ($id <= 0) exit("Invalid id");
if ($minutes <= 0) exit("Invalid minutes");
if ($id === $me) exit("You cannot lock your own account.");

$pdo->prepare("
  UPDATE users
  SET locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE)
  WHERE id=? AND deleted_at IS NULL
")->execute([$minutes, $id]);

log_action($pdo, $me, "Lock user", "user", $id, "minutes=$minutes");

header("Location: " . BASE_URL . "/admin_users.php");
exit;