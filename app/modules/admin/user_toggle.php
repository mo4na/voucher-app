<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/csrf.php';
require_once __DIR__ . '/../../lib/logger.php';

require_any_role(['SUPER_ADMIN']);
csrf_check();

$id = (int)($_POST['id'] ?? 0);
$me = (int)(current_user()['id'] ?? 0);
if ($id <= 0) exit("Invalid id");
if ($id === $me) exit("You cannot disable your own account.");

$pdo->prepare("UPDATE users SET is_active = 1 - is_active WHERE id=? AND deleted_at IS NULL")->execute([$id]);

log_action($pdo, $me, "Toggle user active", "user", $id, null);

header("Location: " . BASE_URL . "/admin_users.php");
exit;