<?php
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/middleware/require_login.php';
require_once __DIR__ . '/../app/lib/helpers.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
  SELECT v.*, u.full_name AS end_user_name
  FROM vouchers v
  JOIN users u ON u.id=v.created_by
  WHERE v.id=?
");
$stmt->execute([$id]);
$v = $stmt->fetch();
if (!$v) { http_response_code(404); exit("Not found"); }

// Access check
$u = current_user();
if ($u['role'] !== 'SUPER_ADMIN') {
  if ($u['role'] === 'END_USER' && (int)$v['created_by'] !== (int)$u['id']) {
    http_response_code(403); exit("Forbidden");
  }
  if (in_array($u['role'], ['BUDGET','ACCOUNTING','CASHIER'], true) && $v['current_office'] !== $u['role']) {
    http_response_code(403); exit("Forbidden");
  }
}

$hist = $pdo->prepare("
  SELECT h.*, u.full_name
  FROM voucher_history h
  JOIN users u ON u.id=h.acted_by
  WHERE h.voucher_id=?
  ORDER BY h.id DESC
");
$hist->execute([$id]);
$history = $hist->fetchAll();

require_once __DIR__ . '/../app/views/layout_header.php';
?>
<div class="container-fluid">
  <div class="row">
    <?php require __DIR__ . '/../app/views/sidebar.php'; ?>

    <div class="col-12 col-lg-9 col-xl-10 p-4">
      <div class="card shadow-soft card-soft">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <h4 class="mb-0"><?= h($v['voucher_number']) ?></h4>
            <a class="btn btn-outline-secondary btn-sm" href="dashboard.php">Back</a>
          </div>
          <hr>

          <div class="row g-3">
            <div class="col-md-4"><div class="text-muted small">End User</div><div class="fw-semibold"><?= h($v['end_user_name']) ?></div></div>
            <div class="col-md-4"><div class="text-muted small">Office</div><div class="fw-semibold"><?= h(office_label($v['current_office'])) ?></div></div>
            <div class="col-md-4"><div class="text-muted small">Status</div><div class="fw-semibold"><?= h($v['status']) ?></div></div>

            <div class="col-md-4"><div class="text-muted small">Payee Type</div><div class="fw-semibold"><?= h($v['payee_type']) ?></div></div>
            <div class="col-md-8"><div class="text-muted small">Payee</div><div class="fw-semibold"><?= h($v['payee']) ?></div></div>

            <div class="col-md-12"><div class="text-muted small">Address</div><div><?= h($v['address'] ?? '') ?></div></div>
            <div class="col-md-12"><div class="text-muted small">Particulars</div><div><?= nl2br(h($v['particulars'])) ?></div></div>

            <div class="col-md-4"><div class="text-muted small">Amount</div><div class="fw-semibold"><?= h(money_php($v['amount'])) ?></div></div>
            <div class="col-md-8"><div class="text-muted small">Remarks</div><div><?= h($v['remarks'] ?? '') ?></div></div>
          </div>

          <hr>
          <h6>History</h6>
          <?php if (!$history): ?>
            <div class="text-muted">No history yet.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr><th>Date</th><th>By</th><th>From</th><th>To</th><th>Remarks</th></tr>
                </thead>
                <tbody>
                <?php foreach ($history as $hrow): ?>
                  <tr>
                    <td><?= h($hrow['acted_at']) ?></td>
                    <td><?= h($hrow['full_name']) ?></td>
                    <td><?= h(($hrow['from_office'] ?? '-') . ' / ' . ($hrow['from_status'] ?? '-')) ?></td>
                    <td><?= h(($hrow['to_office'] ?? '-') . ' / ' . ($hrow['to_status'] ?? '-')) ?></td>
                    <td><?= h($hrow['remarks'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

  </div>
</div>
<?php require_once __DIR__ . '/../app/views/layout_footer.php'; ?>