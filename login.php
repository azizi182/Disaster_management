

 <!-- http://localhost:8000 , php -S localhost:8000
-->

<?php

$error = "";

if (isset($_POST['login'])) {
    if ($_POST['username'] === "admin" && $_POST['password'] === "1234") {
        $_SESSION['logged_in'] = true;
        
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>DVMD Login</title>
<link rel="stylesheet" href="css/style.css">
</head>

<header>
    <div class="header-left">
        <img src="assets/logo.png" class="logo">
        <h1>Digital Village Management Dashboard

        </h1>
    </div>
    <nav>
        <a href="dashboard/villager_dashboard.php">Villagers</a>
        <a href="dashboard/ketuakampung_dashboard.php">Ketua Kampung</a>
        <a href="#">Penghulu</a>
        <a href="#">Pejabat Daerah</a>
        <a href="#">KPLB HQ</a>
    </nav>
</header>

<body>

<div class="card">
  <h2>DVMD Login</h2>

  <form method="POST">
    <div class="input-group">
      <label>Username</label>
      <input type="text" name="username" required>
    </div>

    <div class="input-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>

    <button class="btn" name="login">Login</button>
  </form>

  <div class="error"><?= $error ?></div>

  <div class="link">
    No account? <a href="signup.php">Sign up</a>
  </div>
</div>
</body>
</html>
