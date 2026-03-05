<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/csrf.php';
require_once __DIR__ . '/../../lib/voucher_number.php';
require_once __DIR__ . '/../../lib/logger.php';

require_any_role(['END_USER','SUPER_ADMIN']);
csrf_check();

$u = current_user();

$payee_type  = $_POST['payee_type'] ?? 'INTERNAL';
$payee       = trim($_POST['payee'] ?? '');
$address     = trim($_POST['address'] ?? '');
$particulars = trim($_POST['particulars'] ?? '');
$amount      = (float)($_POST['amount'] ?? 0);
$remarks     = trim($_POST['remarks'] ?? '');

if ($payee === '' || $particulars === '') {
  http_response_code(400);
  exit("Payee and particulars required.");
}

$voucherNo = generate_voucher_number($pdo);

$stmt = $pdo->prepare("
  INSERT INTO vouchers (
    voucher_number, payee_type, payee, address, particulars, amount, remarks,
    created_by, current_office, status
  ) VALUES (?,?,?,?,?,?,?,?,?,?)
");
$stmt->execute([
  $voucherNo, $payee_type, $payee, ($address ?: null), $particulars, $amount, ($remarks ?: null),
  $u['id'], 'END_USER', 'DRAFT'
]);

$vid = (int)$pdo->lastInsertId();
log_action($pdo, $u['id'], "Create voucher", "voucher", $vid, "VoucherNo=$voucherNo");

header("Location: " . BASE_URL . "/dashboard.php");
exit;