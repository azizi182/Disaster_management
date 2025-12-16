<?php
session_start();
include '../../dbconnect.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ketuakampung') {
    header('Location: ../login.php');
    exit();
}

$username = $_SESSION['user_name'];
$role = $_SESSION['user_role'];

$ketua_id = $_SESSION['user_id'];
$sql = "SELECT COUNT(*) AS pending_count FROM villager_report
        WHERE ketua_id = '$ketua_id' AND report_status = 'Pending'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$pending_count = $row['pending_count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ketua Kampung Dashboard</title>

    <link rel="stylesheet" href="../../css/style_villager_dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<style>
    .btn-with-badge {
        position: relative;
        display: inline-block;
        padding: 10px 20px;
        background-color: #1e40af;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }

    .btn-with-badge .badge {
        position: absolute;
        top: -5px;
        right: -10px;
        background-color: red;
        color: white;
        border-radius: 50%;
        padding: 5px 10px;
        font-size: 12px;
    }
</style>

<body>
    <div class="dashboard">
        <!-- Sidebar / Drawer -->
        <div class="sidebar">
            <h2>Ketua Kampung</h2>
            <ul>
                <li><a href="#"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="ketua_report_list.php"><i class="fa fa-edit"></i> Monitor Village Reports - Notify Village</a></li>
                <li><a href="#"><i class="fa fa-calendar-plus"></i> Create Community Event and Information</a></li>
                <li><a href="#"><i class="fa fa-comments"></i> Communicate with Penghulu</a></li>
                <li><a href="#"><i class="fa-solid fa-map-location-dot"></i> Incident Map</a></li>
                <li><a href="../../logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main content -->
        <div class="main">
            <!-- Header -->
            <div class="header">
                <h1>Welcome, <?php echo $username;  ?> !</h1>
            </div>

            <!-- Dashboard content -->
            <div class="content">
                <!-- Village Reports -->
                <div class="card">
                    <h3>Monitor Village Reports</h3>
                    <p>View local reports submitted by villagers and update status and classify villager reports.</p>
                    <p>Send directives or alerts to villager</p>

                    <a href="ketua_report_list.php" class="btn-with-badge">
                        View Reports
                        <?php if ($pending_count > 0): ?>
                            <span class="badge"><?= $pending_count ?></span>
                        <?php endif; ?>
                    </a>

                </div>


                <!-- Create Community Event -->
                <div class="card">
                    <h3>Create Community Event and Information</h3>
                    <p>Publish event to villagers' dashboards.</p>
                    <button>Create Event</button>
                </div>


                <!-- Communicate with Penghulu -->
                <div class="card">
                    <h3>Communicate with Penghulu</h3>
                    <p>Send messages or requests to Penghulu.</p>
                    <button>Open Chat</button>
                </div>

                <!-- Map / Incident Location -->
                <div class="card">
                    <h3>Incident Map</h3>
                    <p>Identify incident points using GPS/maps.</p>
                    <div class="map-placeholder">Map area (Google Maps API later)</div>
                </div>
            </div>
        </div>
    </div>
</body>

<script>

</script>

</html>