<?php
session_start();
$_SESSION['villager_name'] = 'Ahmad'; // Mock villager name
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villager Dashboard</title>

    <link rel="stylesheet" href="../css/style_villager_dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
    <div class="dashboard">
        <!-- Sidebar / Drawer -->
        <div class="sidebar">
            <h2>Village </h2>
            <ul>
                <li><a href="#"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="#"><i class="fa fa-flag"></i> Submit Report</a></li>
                <li><a href="#"><i class="fa fa-bell"></i> Alerts</a></li>
                <li><a href="#"><i class="fa fa-map-marker-alt"></i> Map</a></li>
                <li><a href="#"><i class="fa fa-siren-on"></i> SOS</a></li>
                <li><a href="#"><i class="fa fa-cog"></i> Settings</a></li>
                <li><a href="../logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main content -->
        <div class="main">
            <!-- Header -->
            <div class="header">
                <h1>Welcome, <?php echo $_SESSION['villager_name']; ?>!</h1>
            </div>

            <!-- Dashboard content -->
            <div class="content">
                <!-- Submit report -->
                <div class="card">
                    <h3>Submit Emergency/Complaint</h3>
                    <p>Report any incidents or complaints here.</p>
                    <button>Submit Report</button>
                </div>

                <!-- Alerts / Notifications -->
                <div class="card">
                    <h3>Alerts & Notifications</h3>
                    <p>Receive system alerts and instructions.</p>
                    <button>View Alerts</button>
                </div>

                <!-- Map placeholder -->
                <div class="card">
                    <h3>Incident Map</h3>
                    <p>View location of incidents on the map.</p>
                    <div class="map-placeholder">Map area (Google Maps API later)</div>
                </div>

                <!-- SOS / Quick Emergency -->
                <div class="card sos-card">
                    <h3>SOS / Quick Emergency</h3>
                    <p>Send a quick SOS alert to authorities.</p>
                    <button class="sos-button">Send SOS</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
