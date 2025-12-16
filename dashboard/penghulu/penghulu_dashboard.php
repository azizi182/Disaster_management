<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$role = $_SESSION['user_role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Penghulu Dashboard - DVMD</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../css/style_villager_dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet"
     href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">
        <h2>Penghulu</h2>
        <ul>
            <li><a href="#"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="#"><i class="fa-solid fa-city"></i> Monitor All Villages - Review Issues - Notify Ketua Kampung</a></li>
            <li><a href="#"><i class="fa-solid fa-file-lines"></i> Reports from Ketua Kampung</a></li>
            <li><a href="#"><i class="fa fa-comments"></i> Communicate with Pejabat Daerah</a></li>
            <li><a href="#"><i class="fa-solid fa-map-location-dot"></i> Incident Map</a></li>
            
            <li><a href="../../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </aside>

    <!-- Main -->
    <main class="main">

        <!-- Header -->
        <div class="header">
            <h1>Welcome,<?php echo $username, $user_id, $role; ?></h1>
            <p>Digital Village Management Dashboard (DVMD)</p>
        </div>

        <!-- Content -->
        <section class="content">


            <!-- Monitor villages -->
            <div class="card">
                <h3>Monitor Village Status</h3>
                <p>Track safety, emergencies, and village conditions, .</p>
                <button>Monitor</button>
            </div>

            <!-- Review issues -->
            <div class="card">
                <h3>Reports from Ketua Kampung</h3>
                <p>Review Reported Issues, Analyze incidents escalated by Ketua Kampung , Send directives or alerts to  Ketua Kampung..</p>
                <button>Review Issues</button>
            </div>

            <!-- Report to Pejabat Daerah -->
            <div class="card critical">
                <h3>Report to Pejabat Daerah</h3>
                <p>Escalate critical issues for district action.</p>
                <button class="danger-btn">Submit Report</button>
            </div>

            <!-- Map placeholder -->
            <div class="card">
                <h3>Incident Location Map</h3>
                <p>Verify incident locations using GPS/maps.</p>
                <div class="map-placeholder">
                    Google Maps API (Coming Soon)
                </div>
            </div>

        </section>
    </main>

</div>
</body>
</html>
