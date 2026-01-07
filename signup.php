<?php

$message = "";
$status = "";
include 'dbconnect.php';

// for security
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {


    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmpassword'] ?? '';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $role = $_POST['role'] ?? '';
    $kampung_id = $_POST['kampung_id'] ?? null;

    // ✅ Server-side validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        //email format
        $status = "error";
        $message = "Invalid email format";
    } elseif (!preg_match("/^[a-zA-Z0-9_ ]{3,50}$/", $username)) { //return 0 is invalid
        //username
        $status = "error";
        $message = "Invalid username";
    } elseif (strlen($password) < 4) {
        //length password
        $status = "error";
        $message = "Password must be at least 8 characters";
    } elseif ($password !== $confirmPassword) {
        //confirm password
        $status = "error";
        $message = "Passwords do not match";
    } elseif ($username == "" || $email == "" || $password == "" || $confirmPassword == "" || $role == "") {
        //empty fields
        $status = "error";
        $message = "Please fill in all fields";
    } elseif (in_array($role, ['villager', 'ketuakampung']) && empty($kampung_id)) {
        $status = "error";
        $message = "Please select your kampung";
    } else {


        // Check email exists (Prepared Statement) - more secure //sql injection
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
                "INSERT INTO tbl_users 
                (user_name, user_email, user_password, user_role,kampung_id) 
                VALUES (?, ?, ?, ?,?)"
            );
            $stmt->bind_param("ssssi", $username, $email, $hashedPassword, $role, $kampung_id);

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

<style>
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .modal-box {
        background: #fff;
        padding: 25px 30px;
        border-radius: 10px;
        text-align: center;
        width: 320px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        animation: popIn 0.3s ease;
    }

    .modal-box.success {
        border-top: 6px solid #28a745;
    }

    .modal-box.error {
        border-top: 6px solid #dc3545;
    }

    .modal-icon {
        font-size: 45px;
        margin-bottom: 10px;
    }

    .modal-box.success .modal-icon {
        color: #28a745;
    }

    .modal-box.error .modal-icon {
        color: #dc3545;
    }

    .modal-box p {
        font-size: 16px;
        margin-bottom: 20px;
    }

    .modal-box button {
        padding: 8px 25px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        background: #333;
        color: white;
    }

    @keyframes popIn {
        from {
            transform: scale(0.8);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }
</style>

<body>
    <div class="card">
        <h2>DVMD Sign Up</h2>

        <form method="POST">


            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
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
                <select name="role" id="role" required onchange="toggleKampung()">
                    <option value="">Select Role</option>
                    <option value="villager">Villager</option>
                    <option value="ketuakampung">Ketua Kampung</option>
                    <option value="penghulu">Penghulu</option>
                    <option value="pejabatdaerah">Pejabat Daerah</option>
                    <option value="kplbhq">KPLB HQ</option>
                </select>
            </div>

            <div class="input-group" id="kampungDiv" style="display:none;">
                <label>Kampung</label>
                <select name="kampung_id">
                    <option value="">Select Kampung</option>
                    <option value="1">Kampung Baru</option>
                    <option value="2">Kampung Selamat</option>
                    <option value="3">Kampung Bahagia</option>
                </select>
            </div>

            <button type="submit" class="btn" name="signup">Sign Up</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="modal-overlay">
                <div class="modal-box <?= $status === 'success' ? 'success' : 'error' ?>">
                    <div class="modal-icon">
                        <?= $status === 'success' ? '✔' : '❌' ?>
                    </div>
                    <p><?= htmlspecialchars($message) ?></p>
                    <button onclick="closeModal()">OK</button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success_signup'])): ?>
            <script>
                alert("Sign up submitted successfully!");
            </script>
        <?php endif; ?>




        <div class="link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</body>

<script>
    function toggleKampung() {
        const role = document.getElementById("role").value;
        document.getElementById("kampungDiv").style.display =
            (role === "villager" || role === "ketuakampung") ? "block" : "none";
    }

    function closeModal() {
        document.querySelector('.modal-overlay').style.display = 'none';
    }
</script>

</html>