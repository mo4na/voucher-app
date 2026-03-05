<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/csrf.php';
require_once __DIR__ . '/../../lib/logger.php';

require_any_role(['SUPER_ADMIN']);
csrf_check();

$pdo->exec("UPDATE users SET deleted_at = NULL WHERE deleted_at IS NOT NULL");

log_action($pdo, (int)current_user()['id'], "Restore all users", "user", 0, null);

header("Location: " . BASE_URL . "/admin_users.php");
exit;