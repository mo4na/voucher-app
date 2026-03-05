<?php
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/middleware/require_login.php';
require_once __DIR__ . '/../app/lib/auth.php';
require_any_role(['SUPER_ADMIN']);

$logs = $pdo->query("
  SELECT l.*, u.full_name
  FROM activity_logs l
  LEFT JOIN users u ON u.id = l.user_id
  ORDER BY l.id DESC
  LIMIT 300
")->fetchAll();

require_once __DIR__ . '/../app/views/layout_header.php';
?>
<div class="container-fluid">
  <div class="row">
    <?php require __DIR__ . '/../app/views/sidebar.php'; ?>

    <div class="col-12 col-lg-9 col-xl-10 p-4">
      <div class="card shadow-sm card-soft">
        <div class="card-body">
          <h4>Activity Logs</h4>
          <div class="table-responsive">
            <table class="table table-sm table-striped">
              <thead><tr><th>Date</th><th>User</th><th>Action</th><th>Entity</th><th>ID</th><th>Details</th></tr></thead>
              <tbody>
              <?php foreach ($logs as $l): ?>
                <tr>
                  <td><?= h($l['created_at']) ?></td>
                  <td><?= h($l['full_name'] ?? 'System') ?></td>
                  <td><?= h($l['action']) ?></td>
                  <td><?= h($l['entity']) ?></td>
                  <td><?= h((string)($l['entity_id'] ?? '')) ?></td>
                  <td><?= h(mb_strimwidth($l['details'] ?? '', 0, 80, '...')) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<?php require_once __DIR__ . '/../app/views/layout_footer.php'; ?>