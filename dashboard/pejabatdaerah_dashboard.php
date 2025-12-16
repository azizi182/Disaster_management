<?php
session_start();

// Mock District Officer
$_SESSION['district_name'] = 'Pejabat Daerah Kubang Pasu';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Pejabat Daerah Dashboard - DVMD</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="../css/style_villager_dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
  <div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">
      <h2>Pejabat Daerah</h2>
      <ul>
        <li><a href="#"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="#"><i class="fa-solid fa-city"></i> Monitor All Villages</a></li>
        <li><a href="#"><i class="fa-solid fa-hand-holding-heart"></i> Aid Distribution Management</a></li>
        <li><a href="#"><i class="fa-solid fa-bell"></i> Disaster Commands</a></li>
        <li><a href="#"><i class="fa-solid fa-file-lines"></i> Reports from Penghulu </a></li>
        <li><a href="#"><i class="fa-solid fa-map-location-dot"></i> Incident Map</a></li>

        <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
      </ul>
    </aside>

    <!-- Main -->
    <main class="main">

      <!-- Header -->
      <div class="header">
        <h1><?php echo $_SESSION['district_name']; ?></h1>
        <p>Digital Village Management Dashboard (DVMD)</p>
      </div>

      <!-- Content -->
      <section class="content">

        <!-- Full village access -->
        <div class="card">
          <h3>Access and monitor All Villages</h3>
          <p>View and manage report for all villages in the district.</p>
          <button>View Villages</button>
        </div>

        <!-- Aid distribution -->
        <div class="card">
          <h3>Aid Distribution Management</h3>
          <p>Initiate and track aid distribution to affected areas.</p>
          <button>Manage Aid</button>
        </div>


        <!-- Reports from Penghulu -->
        <div class="card">
          <h3> Reports from Penghulu</h3>
          <p>communicate with penghulu and Review reports received from Penghulu.</p>
          <button>View Reports</button>
        </div>

        <!-- Emergency commands -->
        <div class="card critical">
          <h3> Disaster Commands</h3>
          <p>Issue district-level emergency commands , Send notifications to all villages and officials.</p>
          <button class="danger-btn">Issue Command</button>
        </div>


        <!-- Map -->
        <div class="card">
          <h3>Incident Map</h3>
          <p>Track emergencies using GPS/maps.</p>
          <div class="map-placeholder">
            Google Maps API (Coming Soon)
          </div>
        </div>

      </section>
    </main>
  </div>
</body>

</html>