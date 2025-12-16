<?php

$message = "";
include 'dbconnect.php';

// for security
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {


    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmpassword'] ?? '';
    $hashedPassword = sha1($password);
    $role = $_POST['role'] ?? '';


    if ($password !== $confirmPassword) {
        $status = "error";
        $message = "Passwords do not match";
    } else {

        // Check email exists (Prepared Statement) - more secure
        $stmt = $conn->prepare("SELECT user_email FROM tbl_users WHERE user_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $status = "error";
            $message = "Email already exists";
        } else {

            // Insert user
            $stmt = $conn->prepare(
                "INSERT INTO tbl_users (user_name, user_email, user_password, user_role) 
                VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                $status = "success";
                $message = "Account created successfully!";
            } else {
                $status = "error";
                $message = "Error creating account";
            }
        }
    }
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
        <a href="#">About Us</a>
        <a href="#">Contact</a>

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
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirmpassword" required>
            </div>

            <div class="input-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="villager">Villager</option>
                    <option value="ketuakampung">Ketua Kampung</option>
                    <option value="penghulu">Penghulu</option>
                    <option value="pejabatdaerah">Pejabat Daerah</option>
                    <option value="kplbhq">KPLB HQ</option>
                </select>
            </div>

            <button type="submit" class="btn" name="signup">Sign Up</button>
        </form>

        <?php
        if (!empty($message)) {
            if ($status === "success") {
                echo '<div class="success">' . htmlspecialchars($message) . '</div>';
            } else {
                echo '<div class="error">' . htmlspecialchars($message) . '</div>';
            }
        }
        ?>




        <div class="link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</body>

</html>