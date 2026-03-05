<?php
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/middleware/require_login.php';
require_once __DIR__ . '/../app/lib/helpers.php';
require_once __DIR__ . '/../app/lib/csrf.php';
require_once dirname(__DIR__) . '/app/lib/compat.php';

$u = current_user();
$role = $u['role'];

// SUPER_ADMIN can "act as" a role for UI + filtering.
$asRole = $_GET['as_role'] ?? $role;
$allowedAs = ['SUPER_ADMIN','END_USER','BUDGET','ACCOUNTING','CASHIER','ALL'];
if ($role !== 'SUPER_ADMIN') $asRole = $role;
if (!in_array($asRole, $allowedAs, true)) $asRole = 'SUPER_ADMIN';

$tab = $_GET['tab'] ?? 'needs_action';

$tabMap = [
  'needs_action' => ['DRAFT','RETURNED_TO_ENDUSER'],
  'for_receiving'=> ['FOR_RECEIVING_BUDGET','FOR_RECEIVING_ACCOUNTING','FOR_RECEIVING_CASHIER'],
  'on_process'   => ['RECEIVED_BUDGET','RECEIVED_ACCOUNTING','RECEIVED_CASHIER'],
  'paid'         => ['PAID'],
];

$statuses = $tabMap[$tab] ?? $tabMap['needs_action'];

/**
 * END USER FILTER (for SUPER_ADMIN acting as END_USER)
 */
$selectedEndUserId = null;
$endUserOptions = [];

if ($asRole === 'END_USER') {
  if ($role === 'SUPER_ADMIN') {
    $endUserOptions = $pdo->query("
      SELECT u.id, u.full_name, u.username
      FROM users u
      JOIN roles r ON r.id = u.role_id
      WHERE r.name='END_USER' AND u.is_active=1
      ORDER BY u.full_name ASC
    ")->fetchAll();

    $selectedEndUserId = (int)($_GET['end_user_id'] ?? 0);

    if ($selectedEndUserId <= 0 && !empty($endUserOptions)) {
      $selectedEndUserId = (int)$endUserOptions[0]['id'];
    }
  } else {
    $selectedEndUserId = (int)$u['id'];
  }
}

$where = [];
$params = [];

// Filtering logic depends on asRole
if ($asRole === 'ALL' && $role === 'SUPER_ADMIN') {
  // show everything
} elseif ($asRole === 'SUPER_ADMIN' && $role === 'SUPER_ADMIN') {
  // show everything too
} elseif ($asRole === 'END_USER') {
  if ($selectedEndUserId) {
    $where[] = "v.created_by = ?";
    $params[] = $selectedEndUserId;
  } else {
    $where[] = "1=0";
  }
} else {
  $where[] = "v.current_office = ?";
  $params[] = $asRole;
}

$where[] = "v.status IN (" . implode(',', array_fill(0, count($statuses), '?')) . ")";
$params = array_merge($params, $statuses);

$sql = "
  SELECT v.*, u.full_name AS end_user_name
  FROM vouchers v
  JOIN users u ON u.id = v.created_by
";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY v.updated_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

/**
 * END USER STATS (DB-driven)
 */
$showEndUserStats = ($asRole === 'END_USER');

$stats = [
  'total_amount' => 0.0,
  'pending_amount' => 0.0,
  'paid_amount' => 0.0,
  'total_vouchers' => 0,
  'pending_vouchers' => 0,
  'paid_vouchers' => 0,
];

if ($showEndUserStats && $selectedEndUserId) {
  $q = $pdo->prepare("
    SELECT
      COUNT(*) AS total_vouchers,
      COALESCE(SUM(amount),0) AS total_amount,
      COALESCE(SUM(CASE WHEN status = 'PAID' THEN amount ELSE 0 END),0) AS paid_amount,
      COALESCE(SUM(CASE WHEN status <> 'PAID' THEN amount ELSE 0 END),0) AS pending_amount,
      COALESCE(SUM(CASE WHEN status = 'PAID' THEN 1 ELSE 0 END),0) AS paid_vouchers,
      COALESCE(SUM(CASE WHEN status <> 'PAID' THEN 1 ELSE 0 END),0) AS pending_vouchers
    FROM vouchers
    WHERE created_by = ?
  ");
  $q->execute([$selectedEndUserId]);
  $r = $q->fetch();
  if ($r) {
    $stats['total_vouchers']   = (int)$r['total_vouchers'];
    $stats['total_amount']     = (float)$r['total_amount'];
    $stats['paid_amount']      = (float)$r['paid_amount'];
    $stats['pending_amount']   = (float)$r['pending_amount'];
    $stats['paid_vouchers']    = (int)$r['paid_vouchers'];
    $stats['pending_vouchers'] = (int)$r['pending_vouchers'];
  }
}

require_once __DIR__ . '/../app/views/layout_header.php';
?>
<div class="container-fluid">
  <div class="row">
    <?php require __DIR__ . '/../app/views/sidebar.php'; ?>

    <div class="col-12 col-lg-9 col-xl-10 p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h2 class="mb-0">Voucher Management</h2>
          <div class="text-muted small">
  <?php if ($role === 'SUPER_ADMIN'): ?>
    Acting as: <b><?= h($asRole) ?></b>
  <?php endif; ?>
</div>
        </div>

        <div class="d-flex gap-2 align-items-center">
          <?php if ($role === 'SUPER_ADMIN'): ?>
            <form method="get" class="d-flex gap-2 align-items-center">
              <input type="hidden" name="tab" value="<?= h($tab) ?>">
              <?php if ($asRole === 'END_USER' && $selectedEndUserId): ?>
                <input type="hidden" name="end_user_id" value="<?= (int)$selectedEndUserId ?>">
              <?php endif; ?>
              <select class="form-select form-select-sm" name="as_role" onchange="this.form.submit()">
                <option value="SUPER_ADMIN" <?= $asRole==='SUPER_ADMIN'?'selected':'' ?>>SUPER_ADMIN (All)</option>
                <option value="ALL" <?= $asRole==='ALL'?'selected':'' ?>>ALL (No Filter)</option>
                <option value="END_USER" <?= $asRole==='END_USER'?'selected':'' ?>>END_USER</option>
                <option value="BUDGET" <?= $asRole==='BUDGET'?'selected':'' ?>>BUDGET</option>
                <option value="ACCOUNTING" <?= $asRole==='ACCOUNTING'?'selected':'' ?>>ACCOUNTING</option>
                <option value="CASHIER" <?= $asRole==='CASHIER'?'selected':'' ?>>CASHIER</option>
              </select>
            </form>
          <?php endif; ?>

          <?php if ($role === 'END_USER' || $role === 'SUPER_ADMIN'): ?>
            <a class="btn btn-primary" href="voucher_create.php">Create Voucher</a>
          <?php endif; ?>
          <a class="btn btn-outline-secondary" href="logout.php">Log Out</a>
        </div>
      </div>

      <?php if ($role === 'SUPER_ADMIN' && $asRole === 'END_USER'): ?>
        <div class="card shadow-sm card-soft mb-3">
          <div class="card-body">
            <div class="row g-2 align-items-end">
              <div class="col-12 col-lg-6">
                <label class="form-label mb-1">Select End User (for stats + vouchers)</label>
                <input id="endUserSearch" class="form-control form-control-sm mb-2" placeholder="Search end user name or username...">
                <select id="endUserSelect" class="form-select" size="6">
                  <?php foreach ($endUserOptions as $eu): ?>
                    <option
                      value="<?= (int)$eu['id'] ?>"
                      <?= ((int)$eu['id'] === (int)$selectedEndUserId) ? 'selected' : '' ?>
                    >
                      <?= h($eu['full_name']) ?> (<?= h($eu['username']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="text-muted small mt-1">Tip: type to filter, then select a user.</div>
              </div>

              <div class="col-12 col-lg-6">
                <form method="get" class="d-flex gap-2">
                  <input type="hidden" name="as_role" value="END_USER">
                  <input type="hidden" name="tab" value="<?= h($tab) ?>">
                  <input type="hidden" id="end_user_id_input" name="end_user_id" value="<?= (int)$selectedEndUserId ?>">
                  <button class="btn btn-primary" type="submit">Apply</button>
                  <a class="btn btn-outline-secondary" href="dashboard.php?<?= http_build_query(['as_role'=>'END_USER','tab'=>$tab]) ?>">Reset</a>
                </form>
              </div>
            </div>
          </div>
        </div>

        <script>
          (function(){
            const search = document.getElementById('endUserSearch');
            const sel = document.getElementById('endUserSelect');
            const hidden = document.getElementById('end_user_id_input');

            function filterOptions() {
              const q = (search.value || '').toLowerCase();
              for (const opt of sel.options) {
                const txt = opt.text.toLowerCase();
                opt.hidden = q && !txt.includes(q);
              }
            }

            search.addEventListener('input', filterOptions);

            sel.addEventListener('change', function(){
              hidden.value = sel.value;
            });

            hidden.value = sel.value;
          })();
        </script>
      <?php endif; ?>

      <?php if ($showEndUserStats): ?>
        <div class="row g-3 mb-3">
          <div class="col-12 col-lg-4">
            <div class="card shadow-sm card-soft">
              <div class="card-body">
                <div class="text-muted small">Total Amount</div>
                <div class="fs-3 fw-bold"><?= h(money_php($stats['total_amount'])) ?></div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="card shadow-sm card-soft">
              <div class="card-body">
                <div class="text-muted small">Pending Vouchers Amount</div>
                <div class="fs-3 fw-bold text-warning"><?= h(money_php($stats['pending_amount'])) ?></div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="card shadow-sm card-soft">
              <div class="card-body">
                <div class="text-muted small">Paid Vouchers Amount</div>
                <div class="fs-3 fw-bold text-success"><?= h(money_php($stats['paid_amount'])) ?></div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="card shadow-sm card-soft">
              <div class="card-body">
                <div class="text-muted small">Total Vouchers</div>
                <div class="fs-3 fw-bold"><?= (int)$stats['total_vouchers'] ?></div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="card shadow-sm card-soft">
              <div class="card-body">
                <div class="text-muted small">No. of Pending Vouchers</div>
                <div class="fs-3 fw-bold text-warning"><?= (int)$stats['pending_vouchers'] ?></div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="card shadow-sm card-soft">
              <div class="card-body">
                <div class="text-muted small">No. of Paid Vouchers</div>
                <div class="fs-3 fw-bold text-success"><?= (int)$stats['paid_vouchers'] ?></div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <ul class="nav nav-tabs mb-3">
        <?php
          $tabs = ['needs_action'=>'Needs Action','for_receiving'=>'For Receiving','on_process'=>'On Process','paid'=>'Paid'];
          foreach ($tabs as $k=>$label):
            $q = ['tab'=>$k];
            if ($role==='SUPER_ADMIN') $q['as_role'] = $asRole;
            if ($role==='SUPER_ADMIN' && $asRole==='END_USER' && $selectedEndUserId) $q['end_user_id'] = $selectedEndUserId;
            $qs = http_build_query($q);
        ?>
          <li class="nav-item">
            <a class="nav-link <?= $tab===$k ? 'active':'' ?>" href="dashboard.php?<?= $qs ?>"><?= $label ?></a>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="card card-soft shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-bold">Vouchers</div>
            <input id="tableSearch" class="form-control" style="max-width: 320px" placeholder="Search...">
          </div>

          <?php
            $effectiveOffice = $asRole;
            $cashierLike = ($effectiveOffice === 'CASHIER');
          ?>

          <?php if ($cashierLike && $tab === 'on_process'): ?>
            <form id="bulkPaidForm" method="post" action="../app/modules/vouchers/bulk_paid.php">
              <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <input type="hidden" id="bulkPaidIds" name="ids" value="">
              <button type="button" class="btn btn-success btn-sm mb-2" id="btnBulkPaid">Mark Selected Paid</button>
            </form>
          <?php endif; ?>

          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">

              <thead>
                <tr>
                  <?php if ($cashierLike && $tab === 'on_process') { ?>
                    <th style="width:40px;"></th>
                  <?php } ?>
                  <th>Voucher Number</th>
                  <th>Date</th>
                  <th>End User</th>
                  <th>Payee</th>
                  <th>Particulars</th>
                  <th>Amount</th>
                  <th>Current Office</th>
                  <th style="min-width:260px;">Action</th>
                  <th style="max-width:220px;">Remarks</th>
                </tr>
              </thead>

              <tbody>

              <?php if (!empty($rows)) { ?>

                <?php foreach ($rows as $r) { ?>

                  <?php
                    $status = $r['status'];

                    $isEndUserLike = ($effectiveOffice === 'END_USER');
                    $isBudgetLike = ($effectiveOffice === 'BUDGET');
                    $isAccountingLike = ($effectiveOffice === 'ACCOUNTING');
                    $isCashierLike = ($effectiveOffice === 'CASHIER');

                    $forReceiving = starts_with($status, 'FOR_RECEIVING_');
                    $received = starts_with($status, 'RECEIVED_');

                    $ownerIdForEndUserView = ($role === 'SUPER_ADMIN' && $asRole === 'END_USER')
                      ? (int)$selectedEndUserId
                      : (int)$u['id'];

                    $canEndUserEdit = (
                      $isEndUserLike
                      && ((int)$r['created_by'] === $ownerIdForEndUserView)
                      && in_array($status, ['DRAFT','RETURNED_TO_ENDUSER'], true)
                    );
                  ?>

                  <tr>

                    <?php if ($cashierLike && $tab === 'on_process') { ?>
                      <td>
                        <input type="checkbox" name="bulk_paid[]" value="<?= (int)$r['id'] ?>">
                      </td>
                    <?php } ?>

                    <td>
                      <a href="voucher_view.php?id=<?= (int)$r['id'] ?>">
                        <?= h($r['voucher_number']) ?>
                      </a>
                    </td>

                    <td><?= h(date('m.d.y', strtotime($r['date_created']))) ?></td>
                    <td><?= h($r['end_user_name']) ?></td>
                    <td><?= h($r['payee']) ?></td>
                    <td><?= h(mb_strimwidth($r['particulars'], 0, 40, '...')) ?></td>
                    <td><?= h(money_php($r['amount'])) ?></td>

                    <td>
                      <span class="badge bg-secondary">
                        <?= h(office_label($r['current_office'])) ?>
                      </span>
                    </td>

                    <!-- ACTION COLUMN -->
                    <td style="min-width:260px;" class="d-flex flex-wrap gap-2">

                      <!-- EDIT -->
                      <?php if ($isEndUserLike) { ?>
                        <?php if ($canEndUserEdit) { ?>
                          <a class="btn btn-outline-secondary btn-sm"
                            href="voucher_edit.php?id=<?= (int)$r['id'] ?>">
                            Edit
                          </a>
                        <?php } else { ?>
                          <button type="button"
                                  class="btn btn-outline-secondary btn-sm"
                                  data-bs-toggle="modal"
                                  data-bs-target="#lockedEditModal"
                                  data-vno="<?= h($r['voucher_number']) ?>">
                            🔒 Edit
                          </button>
                        <?php } ?>
                      <?php } else { ?>
                        <a class="btn btn-outline-secondary btn-sm"
                          href="voucher_edit.php?id=<?= (int)$r['id'] ?>">
                          Edit
                        </a>
                      <?php } ?>

                      <!-- RELEASE -->
                      <?php if ($isEndUserLike && in_array($status, ['DRAFT','RETURNED_TO_ENDUSER'], true)) { ?>
                        <form method="post" action="../app/modules/vouchers/action.php" class="d-inline">
                          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                          <input type="hidden" name="voucher_id" value="<?= (int)$r['id'] ?>">
                          <input type="hidden" name="action" value="release">
                          <button class="btn btn-primary btn-sm">Release</button>
                        </form>
                      <?php } ?>

                      <!-- RECEIVE -->
                      <?php if (($isBudgetLike || $isAccountingLike || $isCashierLike) && $forReceiving) { ?>
                        <form method="post" action="../app/modules/vouchers/action.php" class="d-inline">
                          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                          <input type="hidden" name="voucher_id" value="<?= (int)$r['id'] ?>">
                          <input type="hidden" name="action" value="receive">
                          <button class="btn btn-success btn-sm">Receive</button>
                        </form>
                      <?php } ?>

                      <!-- FORWARD -->
                      <?php if (($isBudgetLike || $isAccountingLike) && $received) { ?>
                        <form method="post" action="../app/modules/vouchers/action.php" class="d-inline">
                          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                          <input type="hidden" name="voucher_id" value="<?= (int)$r['id'] ?>">
                          <input type="hidden" name="action" value="approve_forward">
                          <button class="btn btn-primary btn-sm">Forward</button>
                        </form>
                      <?php } ?>

                      <!-- MARK PAID -->
                      <?php if ($isCashierLike && $status === 'RECEIVED_CASHIER') { ?>
                        <form method="post" action="../app/modules/vouchers/action.php" class="d-inline">
                          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                          <input type="hidden" name="voucher_id" value="<?= (int)$r['id'] ?>">
                          <input type="hidden" name="action" value="mark_paid">
                          <button class="btn btn-success btn-sm">Mark Paid</button>
                        </form>
                      <?php } ?>

                      <!-- RETURN -->
                      <?php if (($isBudgetLike || $isAccountingLike || $isCashierLike) && $status !== 'PAID') { ?>
                        <button class="btn btn-outline-danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#returnModal<?= (int)$r['id'] ?>">
                          Return
                        </button>
                      <?php } ?>

                    </td>

                    <!-- REMARKS COLUMN (RIGHT SIDE) -->
                    <td style="max-width:220px; word-break:break-word;">
                      <?= h($r['remarks'] ?? '') ?>
                    </td>

                  </tr>

                <?php } ?>

              <?php } else { ?>

                <tr>
                  <td colspan="10" class="text-center text-muted">
                    No vouchers found.
                  </td>
                </tr>

              <?php } ?>

              </tbody>
            </table>
          </div>

          <?php if (!$rows): ?>
            <div class="text-muted">No vouchers found for this tab.</div>
          <?php endif; ?>

        </div>
      </div>

    </div>
  </div>
</div>

<!-- Locked Edit Modal (single reusable modal) -->
<div class="modal fade" id="lockedEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">🔒 Editing Restricted</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">You can't edit this voucher.</div>
        <div class="text-muted small" id="lockedEditVoucherNo"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="button" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function(){
    const modal = document.getElementById('lockedEditModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;
      const vno = btn?.getAttribute('data-vno') || '';
      const el = document.getElementById('lockedEditVoucherNo');
      if (el) el.textContent = vno ? ('Voucher: ' + vno) : '';
    });
  })();
</script>

<?php require_once __DIR__ . '/../app/views/layout_footer.php'; ?>