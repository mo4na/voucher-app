<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/csrf.php';
require_once __DIR__ . '/../../lib/logger.php';

require_any_role(['SUPER_ADMIN']);
csrf_check();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) exit("Invalid id");

$pdo->prepare("UPDATE users SET deleted_at = NULL WHERE id=? AND deleted_at IS NOT NULL")->execute([$id]);

log_action($pdo, (int)current_user()['id'], "Restore user", "user", $id, null);

header("Location: " . BASE_URL . "/admin_users_trash.php");
exit;