<?php
session_start();
include '../../dbconnect.php';

//Only 'ketuakampung' role allowed
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ketuakampung') {
    header('Location: ../login.php');
    exit();
}
// Get ketua info
$username = $_SESSION['user_name'];
$role = $_SESSION['user_role'];

// Fetch count of pending reports
$ketua_id = $_SESSION['user_id'];
$sql = "SELECT COUNT(*) AS pending_count FROM villager_report
        WHERE ketua_id = '$ketua_id' AND report_status = 'Pending'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$pending_count = $row['pending_count'];

if (isset($_POST['submitinformation'])) {

    // Handle announcement publishing here
    $type = $_POST['announcement_type'];
    $title = $_POST['announcement_title'];
    $description = $_POST['announcement_description'];
    $date = $_POST['announcement_date'];
    $location = $_POST['announcement_location'];

    // Insert into database (example table: announcements)
    $sqlinsertannouncement = "INSERT INTO `ketua_announce`( `ketua_id`, `announce_title`, `announce_type`, `announce_desc`, `announce_date`, `announce_location`) 
    VALUES ('$ketua_id','$title','$type','$description','$date', '$location');";

    if (mysqli_query($conn, $sqlinsertannouncement)) {
        header("Location: ketuakampung_dashboard.php?success=1");
        exit();
    } else {
        echo "<script>alert('Error publishing announcement: " . mysqli_error($conn) . "');</script>";
    }
}
$sqlPenghulu = "SELECT user_id, user_name FROM tbl_users WHERE user_role = 'penghulu'";
$resultPenghulu = mysqli_query($conn, $sqlPenghulu);


if (isset($_POST['submit_to_penghulu'])) {

    $title = $_POST['kp_title'];
    $desc = $_POST['kp_desc'];
    $location = $_POST['kp_location'];
    $penghulu_id = $_POST['penghulu_id'];

    $sql = "INSERT INTO `ketua_report`(`ketua_id`, `penghulu_id`, `report_title`, `report_desc`, `report_location`, `report_status`) 
    VALUES ('$ketua_id','$penghulu_id','$title','$desc','$location','Pending')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ketuakampung_dashboard.php?success_reportpenghulu=1");
        exit();
    } else {
        echo "<script>alert('Error submitting report to penghulu: " . mysqli_error($conn) . "');</script>";
    }
}

//map 
// Villager reports
$report_sql = "SELECT r.latitude, r.longitude, r.report_title, r.report_type, r.report_status,
                u.user_name AS submitted_by
                FROM villager_report r
                JOIN tbl_users u ON r.villager_id = u.user_id
                WHERE r.report_status = 'Pending'";
$report_result = mysqli_query($conn, $report_sql);
$reports = [];
while ($row = mysqli_fetch_assoc($report_result)) {
    $row['type'] = 'report';
    $reports[] = $row;
}

// SOS alerts
$sos_sql = "SELECT s.latitude, s.longitude, s.sos_status, u.user_name AS sent_by
            FROM sos_villager s
            JOIN tbl_users u ON s.villager_id = u.user_id
            WHERE s.sos_status = 'Sent'";
$sos_result = mysqli_query($conn, $sos_sql);
$sos = [];
while ($row = mysqli_fetch_assoc($sos_result)) {
    $row['type'] = 'sos';
    $sos[] = $row;
}

// Combine
$allPins = array_merge($reports, $sos);
$pinreports_json = json_encode($allPins);


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
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

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

    #reportform {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .notificationformketua {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 400px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .notificationformketua h2 {
        text-align: center;
        margin: 0 auto;
    }

    .notificationformketua label {
        display: block;
        margin-bottom: 5px;
    }

    .notificationformketua input,
    .notificationformketua select,
    .notificationformketua textarea {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;

    }

    .notificationformketua .btn {
        background-color: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;

    }

    #penghuluForm {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
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
                <li><a href="ketua_annoucment_list.php"><i class="fa fa-calendar-plus"></i> Announcement for villagers</a></li>
                <li><a href="#"><i class="fa fa-comments"></i> Report to Penghulu</a></li>
                <li>
                    <a href="javascript:void(0)" onclick="openFullMap()">
                        <i class="fa-solid fa-map-location-dot"></i> Incident Map
                    </a>
                </li>
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
                    <h3>Announcement for villagers</h3>
                    <p>Publish event to villagers' dashboards.</p>
                    <button class="btn" onclick="openForm()">Publish Information</button></a>

                </div>


                <!-- Communicate with Penghulu -->
                <div class="card">
                    <h3>Report to Penghulu</h3>
                    <p>submit report to Penghulu.</p>
                    <button class="btn" onclick="openPenghuluForm()">Submit Report</button>
                </div>

                <!-- Map / Incident Location -->
                <div class="card">
                    <h3>Incident Map</h3>
                    <p>Identify incident points using GPS/maps.</p>
                    <div id="incident-map" class="map-placeholder" onclick="openFullMap()"></div>
                </div>

            </div>
        </div>

        <div id="reportform">
            <form method="POST" action="" class="notificationformketua">

                <div class="form-card">
                    <span class="close" onclick="closeForm()">&times;</span>
                    <h2>Publish Announcement</h2>

                    <label>Type</label>
                    <select name="announcement_type" required>
                        <option value="">Select Announcement Type</option>
                        <option value="event">Event</option>
                        <option value="alert">Alert</option>
                        <option value="info">Information</option>
                        <option value="community">Community</option>
                    </select>

                    <label>Title</label>
                    <input type="text" name="announcement_title" required>

                    <label>Description</label>
                    <textarea name="announcement_description" required></textarea>

                    <label>Date</label>
                    <input type="date" name="announcement_date" required>

                    <label>Location</label>
                    <input type="text" name="announcement_location" placeholder="GPS / Address">



                    <button class="btn" name="submitinformation">Confirm Publish</button>
                </div>
            </form>

            <?php if (isset($_GET['success'])): ?>
                <script>
                    alert("Information published successfully!");
                </script>
            <?php endif; ?>

        </div>

        <div id="penghuluForm" >
            <form method="POST", action="" class="notificationformketua">
                <h2>Report to Penghulu</h2>

                <label>Report Title</label>
                <input type="text" name="kp_title" required>

                <label>Description</label>
                <textarea name="kp_desc" required></textarea>

                <label>Location</label>
                <input type="text" name="kp_location" required>

                <label>Penghulu</label>
                <select name="penghulu_id" required>
                    <option value="">Select Penghulu</option>
                    <?php while ($rowP = mysqli_fetch_assoc($resultPenghulu)): ?>
                        <option value="<?= htmlspecialchars($rowP['user_id']) ?>">
                            <?= htmlspecialchars($rowP['user_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button class="btn" name="submit_to_penghulu">Submit</button>
                <button type="button" class="btn" onclick="closePenghuluForm()">Cancel</button>
            </form>

            <?php if (isset($_GET['success_reportpenghulu'])): ?>
                <script>
                    alert("report to penghulu successfully!");
                </script>
            <?php endif; ?>

        </div>


    </div>

    <!-- Map Modal -->
    <div id="mapModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999;">
        <div style="background:#fff; width:90%; max-width:600px; height:400px; margin:50px auto; padding:10px;">
            <h3>Click on map to select location</h3>
            <div id="map" style="height:300px;"></div>
            <button onclick="closeMap()">Done</button>
        </div>
    </div>

    <!-- Fullscreen Map Modal -->
    <div id="fullMapModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999;">
        <div style="position:relative; width:100%; height:100%;">
            <span style="position:absolute; top:10px; right:20px; font-size:30px; color:white; cursor:pointer; z-index:1000;" onclick="closeFullMap()">&times;</span>
            <div id="fullIncidentMap" style="width:100%; height:100%;"></div>
        </div>
    </div>
</body>

<script>
    var reportform = document.getElementById("reportform");

    function openForm() {
        reportform.style.display = "flex";
    }

    function closeForm() {
        reportform.style.display = "none";
    }


    function openPenghuluForm() {
        document.getElementById("penghuluForm").style.display = "flex";
    }

    function closePenghuluForm() {
        document.getElementById("penghuluForm").style.display = "none";
    }

    // Map functionality
    var greenIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });

    var redIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });



    let map;
    let reportMarker;
    let sosMarker;

    function openMapPicker(type) { // type = 'report' or 'sos'
        document.getElementById("mapModal").style.display = "block";

        setTimeout(() => {
            if (map) {
                map.remove();
            }

            map = L.map('map').setView([6.4432, 100.2056], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            map.on('click', function(e) {
                let lat = e.latlng.lat;
                let lng = e.latlng.lng;

                if (type === 'report') {
                    if (reportMarker) {
                        reportMarker.setLatLng(e.latlng);
                    } else {
                        reportMarker = L.marker(e.latlng, {
                            icon: greenIcon
                        }).addTo(map);
                    }
                    document.getElementById("latitude").value = lat;
                    document.getElementById("longitude").value = lng;

                } else if (type === 'sos') {
                    if (sosMarker) {
                        sosMarker.setLatLng(e.latlng);
                    } else {
                        sosMarker = L.marker(e.latlng, {
                            icon: redIcon
                        }).addTo(map);
                    }
                    document.getElementById("sos_latitude").value = lat;
                    document.getElementById("sos_longitude").value = lng;
                }
            });

        }, 300);
    }

    function closeMap() {
        document.getElementById("mapModal").style.display = "none";
    }

    // Display incident pins on dashboard map
    let incidentMap = L.map('incident-map').setView([6.4432, 100.2056], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(incidentMap);

    // Get pins from PHP
    var pins = <?php echo $pinreports_json; ?>;

    pins.forEach(function(pin) {
        if (pin.latitude && pin.longitude) {
            let icon, popupContent;

            if (pin.type === 'report') {
                icon = greenIcon;
                popupContent = `<b>Report: ${pin.report_type}</b><br>
                            Title: ${pin.report_title}<br>
                            Status: ${pin.report_status}<br>
                            Submitted by: ${pin.submitted_by}`;
            } else if (pin.type === 'sos') {
                icon = redIcon;
                popupContent = `<b>SOS Alert</b><br>
                            Status: ${pin.sos_status}<br>
                            Sent by: ${pin.sent_by}`;
            }

            L.marker([pin.latitude, pin.longitude], {
                    icon: icon
                })
                .addTo(incidentMap)
                .bindPopup(popupContent);
        }
    });

    // Fullscreen Map
    // Open full map modal
    function openFullMap() {
        document.getElementById('fullMapModal').style.display = 'block';

        setTimeout(() => {
            // Remove previous map instance if exists
            if (window.fullMap) {
                window.fullMap.remove();
            }

            // Initialize full map
            window.fullMap = L.map('fullIncidentMap').setView([6.4432, 100.2056], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(window.fullMap);

            // Add all pins
            pins.forEach(function(pin) {
                if (pin.latitude && pin.longitude) {
                    let icon, popupContent;

                    if (pin.type === 'report') {
                        icon = greenIcon;
                        popupContent = `<b>Report: ${pin.report_type}</b><br>
                            Title: ${pin.report_title}<br>
                            Status: ${pin.report_status}<br>
                            Submitted by: ${pin.submitted_by}`;
                    } else if (pin.type === 'sos') {
                        icon = redIcon;
                        popupContent = `<b>SOS Alert</b><br>
                            Status: ${pin.sos_status}<br>
                            Sent by: ${pin.sent_by}`;
                    }

                    L.marker([pin.latitude, pin.longitude], {
                            icon: icon
                        })
                        .addTo(window.fullMap)
                        .bindPopup(popupContent);
                }
            });

        }, 200);
    }

    // Close full map modal
    function closeFullMap() {
        document.getElementById('fullMapModal').style.display = 'none';
        if (window.fullMap) window.fullMap.remove();
    }
</script>

</html>