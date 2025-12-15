<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>DVMD - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{display:flex;align-items:center;justify-content:center;height:100vh;background:#f5f7fb}</style>
</head>
<body>
  <div class="card p-4" style="width:360px">
    <h4 class="mb-3">DVMD Login</h4>
    <form method="post" action="auth.php">
      <div class="mb-2">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <div class="d-grid mb-2">
        <button class="btn btn-primary" type="submit">Sign in</button>
      </div>
      <div class="d-grid">
        <a class="btn btn-outline-secondary" href="register.php">Register (Villager)</a>
      </div>
    </form>
  </div>
</body>
</html>
