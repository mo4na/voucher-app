<?php
require_once __DIR__ . '/../app/config/db.php';

$exists = $pdo->query("SELECT COUNT(*) c FROM users")->fetch()['c'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ((int)$exists > 0) exit("Install already done. Delete install.php if you want.");

  $full = trim($_POST['full_name'] ?? 'Super Admin');
  $user = trim($_POST['username'] ?? 'admin');
  $pass = $_POST['password'] ?? 'admin123';

  $roleId = $pdo->query("SELECT id FROM roles WHERE name='SUPER_ADMIN'")->fetch()['id'];
  $hash = password_hash($pass, PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("INSERT INTO users(full_name, username, password_hash, role_id) VALUES(?,?,?,?)");
  $stmt->execute([$full, $user, $hash, $roleId]);

  echo "Super Admin created. You can login now. (Delete install.php for security)";
  exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Install</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4>Install - Create Super Admin</h4>
          <?php if ((int)$exists > 0): ?>
            <div class="alert alert-warning">Users already exist. Install is locked.</div>
          <?php else: ?>
            <form method="post">
              <div class="mb-2">
                <label class="form-label">Full Name</label>
                <input class="form-control" name="full_name" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Username</label>
                <input class="form-control" name="username" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" name="password" type="password" required>
              </div>
              <button class="btn btn-primary">Create Admin</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>