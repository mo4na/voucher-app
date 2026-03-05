<?php
$u = current_user();
$role = $u['role'] ?? '';

function nav_active(string $file): string {
  $cur = basename($_SERVER['PHP_SELF'] ?? '');
  return ($cur === $file) ? 'active' : '';
}
?>
<div class="col-12 col-lg-3 col-xl-2 sidebar p-3">

  <!-- Brand -->
  <div class="brand mb-4">
    <div class="d-flex align-items-center gap-2">
      <div class="brand-badge">V</div>
      <div class="brand-title">
        <?= h(APP_NAME) ?>
        <div class="brand-sub">Workflow • Vouchers</div>
      </div>
    </div>
  </div>

  <!-- User chip -->
  <div class="user-chip mb-3">
    <div class="user-chip-name"><?= h($u['full_name'] ?? 'User') ?></div>
    <div class="user-chip-role"><?= h($role) ?></div>
  </div>

  <div class="menu-label">Menu</div>

  <div class="nav-list">
    <a class="nav-item <?= nav_active('dashboard.php') ?>" href="dashboard.php">
      <span class="nav-ic">🏠</span>
      <span>Dashboard</span>
    </a>

    <a class="nav-item <?= nav_active('dashboard.php') ?>" href="dashboard.php">
      <span class="nav-ic">🧾</span>
      <span>Vouchers</span>
    </a>

    <?php if ($role === 'END_USER' || $role === 'SUPER_ADMIN'): ?>
      <a class="nav-item <?= nav_active('voucher_create.php') ?>" href="voucher_create.php">
        <span class="nav-ic">➕</span>
        <span>Create Voucher</span>
      </a>
    <?php endif; ?>

    <?php if ($role === 'SUPER_ADMIN'): ?>
      <div class="menu-sep"></div>
      <div class="menu-label">Admin</div>

      <a class="nav-item <?= nav_active('admin_users.php') ?>" href="admin_users.php">
        <span class="nav-ic">👥</span>
        <span>User Management</span>
      </a>

      <a class="nav-item <?= nav_active('admin_logs.php') ?>" href="admin_logs.php">
        <span class="nav-ic">🕒</span>
        <span>Activity Logs</span>
      </a>
    <?php endif; ?>
  </div>

</div>