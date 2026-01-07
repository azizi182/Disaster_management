<?php
session_start();
include "../../dbconnect.php";

// Security check
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "kplbhq") {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["user_name"];
$role = $_SESSION["user_role"];

// --- Villager Reports with Kampung ---
$sql = "
    SELECT
        vr.*,
        v.user_name AS villager_name,
        k.user_name AS ketua_name,
        kp.kampung_name
    FROM villager_report vr
    JOIN tbl_users v ON vr.villager_id = v.user_id
    JOIN tbl_users k ON vr.ketua_id = k.user_id
    JOIN kampung kp ON v.kampung_id = kp.kampung_id
    ORDER BY kp.kampung_name ASC, vr.report_date ASC
";
$villagerreportresult = mysqli_query($conn, $sql);

// --- Ketua Kampung Reports ---
$sql = "
    SELECT r.*, k.user_name AS ketua_name
    FROM ketua_report r
    JOIN tbl_users k ON r.ketua_id = k.user_id
    ORDER BY r.created_at DESC
";
$ketuareportresult = mysqli_query($conn, $sql);

// --- Penghulu Reports ---
$sql = "
    SELECT r.*, k.user_name AS penghulu_name
    FROM penghulu_report r
    JOIN tbl_users k ON r.penghulu_id = k.user_id
    ORDER BY r.created_at DESC
";
$penghulureportresult = mysqli_query($conn, $sql);

// --- Map Pins: Villager Reports ---
$report_sql = "
    SELECT r.latitude, r.longitude, r.report_title, r.report_type, r.report_status, u.user_name AS submitted_by
    FROM villager_report r
    JOIN tbl_users u ON r.villager_id = u.user_id
    WHERE r.report_status = 'Pending'
";
$report_result = mysqli_query($conn, $report_sql);
$reports = [];
while ($row = mysqli_fetch_assoc($report_result)) {
    $row["type"] = "report";
    $reports[] = $row;
}

// --- Map Pins: SOS Alerts ---
$sos_sql = "
    SELECT s.latitude, s.longitude, s.sos_status, u.user_name AS sent_by
    FROM sos_villager s
    JOIN tbl_users u ON s.villager_id = u.user_id
    WHERE s.sos_status = 'Sent'
";
$sos_result = mysqli_query($conn, $sos_sql);
$sos = [];
while ($row = mysqli_fetch_assoc($sos_result)) {
    $row["type"] = "sos";
    $sos[] = $row;
}

// Combine pins for map
$allPins = array_merge($reports, $sos);
$pinreports_json = json_encode($allPins);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pejabat Daerah Dashboard - DVMD</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="../../css/style_villager_dashboard.css">
<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<style>
body {
    font-family: 'Roboto', sans-serif;
    margin: 0;
    background: #f0f2f5;
}
.dashboard {
    display: flex;
    min-height: 100vh;
}
.sidebar {
    width: 220px;
    background: #1e40af;
    color: white;
    padding: 20px;
}
.sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
}
.sidebar ul {
    list-style: none;
    padding: 0;
}
.sidebar ul li {
    margin: 15px 0;
}
.sidebar ul li a {
    color: white;
    text-decoration: none;
}
.main {
    flex: 1;
    padding: 20px;
}
.header h1 {
    margin: 0;
    color: #1e40af;
}
.table-container {
    background: white;
    padding: 20px;
    margin-bottom: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}
th {
    background: #1e40af;
    color: white;
}
.status-pending { color: orange; font-weight: bold; }
.status-approved { color: green; font-weight: bold; }
.status-rejected { color: red; font-weight: bold; }
.map-placeholder {
    width: 100%;
    height: 300px;
    border-radius: 8px;
}
</style>
</head>
<body>

<div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">
        <h2>Pejabat Daerah</h2>
        <ul>
            <li><a href="pejabatdaerah_dashboard.php"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="../../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main">

        <div class="header">
            <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
            <p>Digital Village Management Dashboard (DVMD)</p>
        </div>

        <!-- Villager Reports -->
        <div class="table-container">
            <h2>Villager Reports</h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Villager</th>
                        <th>Kampung</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Ketua Kampung</th>
                        <th>Status</th>
                        <th>Feedback</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($villagerreportresult) > 0): 
                        $i=1; 
                        while($row = mysqli_fetch_assoc($villagerreportresult)): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['villager_name']) ?></td>
                                <td><?= htmlspecialchars($row['kampung_name']) ?></td>
                                <td><?= htmlspecialchars($row['report_title']) ?></td>
                                <td><?= htmlspecialchars($row['report_type']) ?></td>
                                <td><?= htmlspecialchars($row['report_desc']) ?></td>
                                <td><?= htmlspecialchars($row['report_date']) ?></td>
                                <td><?= htmlspecialchars($row['report_location']) ?></td>
                                <td><?= htmlspecialchars($row['ketua_name']) ?></td>
                                <td class="status-<?= strtolower($row['report_status']) ?>">
                                    <?= htmlspecialchars($row['report_status']) ?>
                                </td>
                                <td>
                                    <button onclick="alert('Feedback: <?= addslashes($row['report_feedback']) ?>')">View</button>
                                </td>
                            </tr>
                    <?php endwhile; else: ?>
                        <tr>
                            <td colspan="11" style="text-align:center;">No reports submitted yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Ketua Kampung & Penghulu Reports can follow the same structure -->
        <!-- Map -->
        <div class="table-container">
            <h2>Incident Map</h2>
            <div id="incident-map" class="map-placeholder"></div>
        </div>

    </main>
</div>

<script>
var greenIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41]
});
var redIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41]
});

var pins = <?= $pinreports_json ?>;

// Dashboard Map
var incidentMap = L.map('incident-map').setView([6.4432, 100.2056], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(incidentMap);

pins.forEach(function(pin){
    if(pin.latitude && pin.longitude){
        let icon = pin.type === 'report' ? greenIcon : redIcon;
        let popup = pin.type === 'report' ?
            `<b>Report: ${pin.report_type}</b><br>Title: ${pin.report_title}<br>Status: ${pin.report_status}<br>Submitted by: ${pin.submitted_by}` :
            `<b>SOS Alert</b><br>Status: ${pin.sos_status}<br>Sent by: ${pin.sent_by}`;

        L.marker([pin.latitude, pin.longitude], {icon: icon}).addTo(incidentMap).bindPopup(popup);
    }
});
</script>

</body>
</html>
