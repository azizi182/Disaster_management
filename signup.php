<?php
$message = "";

if (isset($_POST['signup'])) {
    $message = "Account created successfully!";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>DVMD Signup</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<header>
    <div class="header-left">
        <img src="assets/logo.png" class="logo">
        <h1>Digital Village Management Dashboard
          
        </h1>
    </div>
    <nav>
        <a href="#">Villagers</a>
        <a href="#">Ketua Kampung</a>
        <a href="#">Penghulu</a>
        <a href="#">Pejabat Daerah</a>
        <a href="#">KPLB HQ</a>
    </nav>
</header>

<body>
    <div class="card">
        <h2>DVMD Sign Up</h2>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="input-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option>Villager</option>
                    <option>Ketua Kampung</option>
                    <option>Penghulu</option>
                    <option>Pejabat Daerah</option>
                    <option>KPLB HQ</option>
                </select>
            </div>

            <button class="btn" name="signup">Sign Up</button>
        </form>

        <div class="success"><?= $message ?></div>

        <div class="link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</body>

</html>