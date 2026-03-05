<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/csrf.php';
require_once __DIR__ . '/../../lib/logger.php';

require_any_role(['CASHIER','SUPER_ADMIN']);
csrf_check();

$u = current_user();
$idsCsv = $_POST['ids'] ?? '';
$ids = array_filter(array_map('intval', explode(',', $idsCsv)));

foreach ($ids as $voucherId) {
  $v = $pdo->prepare("SELECT * FROM vouchers WHERE id=?");
  $v->execute([$voucherId]);
  $row = $v->fetch();
  if (!$row) continue;

  if ($row['current_office'] !== 'CASHIER') continue;
  if ($row['status'] !== 'RECEIVED_CASHIER') continue;

  $pdo->prepare("UPDATE vouchers SET status='PAID', date_paid=NOW() WHERE id=?")->execute([$voucherId]);
  log_action($pdo, $u['id'], "Bulk mark paid", "voucher", $voucherId, null);
}

header("Location: " . BASE_URL . "/dashboard.php?tab=paid");
exit;