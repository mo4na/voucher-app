<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/csrf.php';
require_once __DIR__ . '/../../lib/logger.php';

require_any_role(['SUPER_ADMIN']);
csrf_check();

$ids = $_POST['ids'] ?? [];
if (!is_array($ids) || count($ids) === 0) exit("No users selected.");

$ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($x) => $x > 0)));
if (count($ids) === 0) exit("No valid users selected.");

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "UPDATE users SET deleted_at = NULL WHERE deleted_at IS NOT NULL AND id IN ($placeholders)";
$pdo->prepare($sql)->execute($ids);

log_action($pdo, (int)current_user()['id'], "Bulk restore users", "user", 0, "count=" . count($ids));

header("Location: " . BASE_URL . "/admin_users_trash.php");
exit;