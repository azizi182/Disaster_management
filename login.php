<?php
include 'dbconnect.php';
session_start();

$status = "";
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  // Basic validation
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $status = "error";
    $message = "Invalid email format";
  } else if (empty($email) || empty($password)) {
    $status = "error";
    $message = "Please fill in all fields";
  } else {

    // Prepared statement (SQL Injection safe)
    $stmt = $conn->prepare(
      "SELECT * FROM tbl_users WHERE user_email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $userdata = $result->fetch_assoc();

      // ✅ Verify hashed password
      if (password_verify($password, $userdata['user_password'])) {

        // Set session securely
        $_SESSION['user_id'] = $userdata['user_id'];
        $_SESSION['user_name'] = $userdata['user_name'];
        $_SESSION['user_email'] = $userdata['user_email'];
        $_SESSION['user_role'] = $userdata['user_role'];

        // Role-based redirect
        switch ($userdata['user_role']) {
          case 'villager':
            header('Location: dashboard/villager/villager_dashboard.php');
            break;
          case 'ketuakampung':
            header('Location: dashboard/ketuakampung/ketuakampung_dashboard.php');
            break;
          case 'penghulu':
            header('Location: dashboard/penghulu/penghulu_dashboard.php');
            break;
          case 'pejabatdaerah':
            header('Location: dashboard/pejabatdaerah/pejabatdaerah_dashboard.php');
            break;
          case 'kplbhq':
            header('Location: dashboard/kplbhq/kplb_dashboard.php');
            break;
          default:
            header('Location: login.php');
        }
        exit();
      } else {
        $status = "error";
        $message = "Invalid email or password";
      }
    } else {
      $status = "error";
      $message = "Invalid email or password";
    }
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
    <h2>DVMD Login</h2>

    <form method="POST">
      <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>

      <div class="input-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>

      <button class="btn" name="login">Login</button>
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

    <div class="link">
      No account? <a href="signup.php">Sign up</a>
    </div>
  </div>

  <!-- Memo Carousel -->
  <div class="memo-carousel">
    <div class="memo-slide active">
      <p>Langkah-Langkah Terbakar!</p>
      <img src="assets/langkah terbakar.jpg" alt="Announcement 1">

    </div>
    <div class="memo-slide">
      <p>Dilarang membakar sampah dikawasan rumah!</p>
      <img src="assets/membakarsampah.jpg" alt="Announcement 2">

    </div>
    <div class="memo-slide">
      <p>Langkah-Langkah Banjir!</p>
      <img src="assets/LANGKAH-KESELAMATAN-DI-MUSIM-BANJIR-2.jpeg" alt="Announcement 3">

    </div>
  </div>


</body>

<script>
  let slides = document.querySelectorAll('.memo-slide');
  let current = 0;

  function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    slides[index].classList.add('active');
  }

  function nextSlide() {
    current = (current + 1) % slides.length;
    showSlide(current);
  }

  showSlide(current);
  setInterval(nextSlide, 3000); // change every 3 seconds

  function closeModal() {
    document.querySelector('.modal-overlay').style.display = 'none';
  }
</script>


</html>