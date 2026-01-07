<?php
session_start();
include '../../dbconnect.php';
$message = "";
$status = "";

if (isset($_GET['success_sos'])) {
    $status = "success";
    $message = "Sos submitted successfully!";
}

if (isset($_GET['success_submit'])) {
    $status = "success";
    $message = "Report submitted successfully!";
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'villager') {
    header('Location: ../login.php');
    exit();
}

$villager_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$role = $_SESSION['user_role'];

//get kampung id 
$kampung_id = '';
$kampung_name = '';

$stmt = $conn->prepare("
    SELECT uk.kampung_id, k.kampung_name
    FROM user_kampung uk
    JOIN tbl_kampung k ON uk.kampung_id = k.kampung_id
    WHERE uk.user_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $villager_id);
$stmt->execute();
$stmt->bind_result($kampung_id, $kampung_name);
$stmt->fetch();
$stmt->close();



// get ketua kampung refer on thier kampung
$sqlketua = "SELECT u.user_id, u.user_name
    FROM tbl_users u
    JOIN user_kampung uk ON u.user_id = uk.user_id
    WHERE u.user_role = 'ketuakampung' AND uk.kampung_id = ?";
$stmt = $conn->prepare($sqlketua);
$stmt->bind_param("i", $kampung_id);
$stmt->execute();
$resultketua = $stmt->get_result();

//insert report to database  villager report
if (isset($_POST['submitreport'])) {

    $title = $_POST['title'];
    $report_type = $_POST['report_type'];
    $description = $_POST['description'];
    $phone = $_POST['phone'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $ketua_id = $_POST['ketua_kampung'];
    $status_report = 'Pending';
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];


    // error handling & Validate required fields
    if (!ctype_digit($phone)) {

        $status = "error";
        $message = "Phone number must be numeric and 10-12 digits long";
    } else if (empty($lat) || empty($lng)) {

        $status = "error";
        $message = "Please select location on map";
    } else if (empty($ketua_id)) {

        $status = "error";
        $message = "Please select a Ketua Kampung";
    } else if (empty($title) || empty($report_type) || empty($description) || empty($date) || empty($location) || empty($phone)) {

        $status = "error";
        $message = "Please fill in all required fields";
    } else if (!preg_match("/^[a-zA-Z0-9_ ]{3,50}$/", $title)) {
        $status = "error";
        $message = "Invalid title";
    } else if (!preg_match("/^[a-zA-Z0-9_ ]{3,50}$/", $description)) {
        $status = "error";
        $message = "Invalid description";
    } else if (!preg_match("/^[a-zA-Z0-9_ ]{3,50}$/", $location)) {
        $status = "error";
        $message = "Invalid location";
    } else {


        $stmt = $conn->prepare("
        INSERT INTO villager_report
        (villager_id, ketua_id, report_title, report_type, report_desc, report_phone, 
        report_date, report_location, latitude, longitude, report_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

        $stmt->bind_param(
            "iissssssdds",
            $villager_id,
            $ketua_id,
            $title,
            $report_type,
            $description,
            $phone,
            $date,
            $location,
            $lat,
            $lng,
            $status_report
        );

        if ($stmt->execute()) {
            header("Location: villager_dashboard.php?success_submit=1");
                exit();
        } else {
            echo "<script>alert('Error submitting report: " . htmlspecialchars($stmt->error) . "');</script>";
        }

        $stmt->close();
    }
}


//sos form
if (isset($_POST['sosconfirm'])) {
    $lat = $_POST['sos_latitude'];
    $lng = $_POST['sos_longitude'];
    $sos_status = 'Sent';

    if (empty($lat) || empty($lng)) {

        $status = "error";
        $message = "Please select location on map";
    } else {

        $stmt = $conn->prepare("
        INSERT INTO sos_villager (villager_id, ketua_id, latitude, longitude, sos_status)
        VALUES (?, ?, ?, ?, ?)
    ");

        $empty_ketua = ''; // if you don’t have ketua selected
        $stmt->bind_param("iidds", $villager_id, $empty_ketua, $lat, $lng, $sos_status);

        if ($stmt->execute()) {
            header("Location: villager_dashboard.php?success_sos=1");
                exit();
        } else {
            echo "<script>alert('Error sending SOS: " . htmlspecialchars($stmt->error) . "');</script>";
        }

        $stmt->close();
    }
}


// get all pin data for map display
$pinreport_sql = "SELECT r.latitude, r.longitude, r.report_title, r.report_type, r.report_status,
                u.user_name AS submitted_by
                FROM villager_report r
                JOIN tbl_users u ON r.villager_id = u.user_id 
                WHERE r.report_status = 'Pending'";
$pinreport_result = mysqli_query($conn, $pinreport_sql);
$pinreports = [];
while ($row = mysqli_fetch_assoc($pinreport_result)) {
    $row['type'] = 'report';
    $pinreports[] = $row;
}

$sos_sql = "SELECT s.latitude, s.longitude, s.sos_status, u.user_name AS sent_by
            FROM sos_villager s
            JOIN tbl_users u ON s.villager_id = u.user_id WHERE s.sos_status = 'Sent'";

$sos_result = mysqli_query($conn, $sos_sql);
$sos = [];
while ($row = mysqli_fetch_assoc($sos_result)) {
    $row['type'] = 'sos';
    $sos[] = $row;
}

$allPins = array_merge($pinreports, $sos);
$pinreports_json = json_encode($allPins);

?>



<!DOCTYPE html>
<html lang="en">



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villager Dashboard</title>

    <link rel="stylesheet" href="../../css/style_villager_dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        /* reportformvillager */
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

        .reportformvillager {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .reportformvillager h2 {
            text-align: center;
            margin: 0 auto;
        }

        .reportformvillager label {
            display: block;
            margin-bottom: 5px;
        }

        .reportformvillager input,
        .reportformvillager select,
        .reportformvillager textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;

        }

        .reportformvillager .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .reportformvillager .btn:hover {
            background-color: #45a049;
        }

        .reportformvillager .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }

        /*sos form*/
        #sosform {
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

        .modal-content {
            background: red;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        /* pop message*/
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
</head>

<body>
    <div class="dashboard">
        <!-- Sidebar / Drawer -->
        <div class="sidebar">
            <h2>Village </h2>
            <ul>
                <li><a href="villager_dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="villager_report_list.php"><i class="fa fa-flag"></i> Submit Report,Emergency / Complaint</a></li>
                <li><a href="villager_announce_list.php"><i class="fa fa-bell"></i> Announcement / Alerts</a></li>

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
                <h1>Welcome, <?php echo htmlspecialchars($username); ?> from <?php echo htmlspecialchars($kampung_id); ?> <?php echo htmlspecialchars($kampung_name); ?></h1>


                <!-- Dashboard content -->
                <div class="content">
                    <!-- Submit report -->
                    <div class="card">
                        <h3>Submit Emergency/Complaint</h3>
                        <p>Report any incidents or complaints here.</p>
                        <button class="btn" onclick="openForm()">Submit Report</button></a>

                    </div>

                    <!-- Alerts / Notifications -->
                    <div class="card">
                        <h3>Announcement / Alerts</h3>
                        <p>Receive system alerts and announcement .</p>
                        <a href="villager_announce_list.php"><button>View Alerts</button></a>
                    </div>


                    <!-- SOS / Quick Emergency -->
                    <div class="card sos-card">
                        <h3>SOS / Quick Emergency</h3>
                        <p>Send a quick SOS alert to authorities.</p>
                        <button class="sos-button" onclick="openSOSForm()">Send SOS</button>
                    </div>

                    <!-- Map placeholder -->
                    <div class="card">
                        <h3>Incident Map</h3>
                        <p>View location of incidents on the map.</p>
                        <div id="incident-map" class="map-placeholder" onclick="openFullMap()"></div>
                    </div>

                </div>
            </div>

            <!-- reportform -->
            <div id="reportform">
                <form action="" method="POST" class="reportformvillager">

                    <div class="form-card">
                        <span class="close" onclick="closeForm()">&times;</span>
                        <h2>Submit Report</h2>

                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">


                        <label>Report Title</label>
                        <input type="text" name="title" required>

                        <label>Report Type</label>
                        <select name="report_type">
                            <option>Road Damage</option>
                            <option>Flood</option>
                            <option>Power Failure</option>
                            <option>Other</option>
                        </select>

                        <label>Description</label>
                        <textarea type="text" name="description" rows="4" required></textarea>

                        <label>Phone</label>
                        <input type="tel"
                            name="phone"
                            placeholder="0123456789"
                            minlength="10"
                            maxlength="12"
                            pattern="[0-9]+"
                            oninput="allowOnlyNumbers(this)"
                            required>

                        <label>Date</label>
                        <input type="date" name="date" required>

                        <label>Location</label>
                        <input type="text" name="location" placeholder="GPS / Address">

                        <label>Map </label>
                        <button type="button" onclick="openMapPicker('report')">📍 Pick Location on Map</button>

                        <p id="locationText" style="font-size:13px;color:green;"></p>

                        <label>Ketua Kampung</label>
                        <select name="ketua_kampung" required>
                            <option value="">Select Ketua Kampung</option>

                            <?php while ($rowketua = mysqli_fetch_assoc($resultketua)): ?>
                                <option value="<?= htmlspecialchars($rowketua['user_id']) ?>">
                                    <?= htmlspecialchars($rowketua['user_name']) ?>
                                </option>
                            <?php endwhile; ?>

                        </select>

                        <button class="btn" name="submitreport">Submit Report</button>
                    </div>
                </form>



            </div>

            <!-- SOS Modal -->
            <div id="sosform" class="modal">
                <form action="" method="POST">
                    <div class="modal-content">
                        <input type="hidden" name="sos_latitude" id="sos_latitude">
                        <input type="hidden" name="sos_longitude" id="sos_longitude">

                        <span class="close" onclick="closeSOSForm()">&times;</span>
                        <h2 style="color: #fff;">SOS Alert</h2>
                        <p style="color: white">Are you sure you want to send an SOS alert?
                            (all ketua will receive the sos)</p>

                        <label>Map </label>
                        <button type="button" onclick="openMapPicker('sos')">📍 Pick Location on Map</button>

                        <button class="btn" name="sosconfirm">Yes, Send SOS</button>
                        <button class="btn" onclick="closeSOSForm()">Cancel</button>
                    </div>
                </form>

                

            </div>

        </div>

        <!-- Map Modal -->
        <div id="mapModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999;">
            <div style="background:#fff; width:90%; max-width:600px; height:400px; margin:50px auto; padding:10px;">
                <h3>Click on map to select location</h3>
                <div id="map" style="height:300px;"></div>
                <button onclick="closeMap()">Done</button>
            </div>
        </div>

        <!-- Fullscreen Map Modal -->
        <div id="fullMapModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999;">
            <div style="position:relative; width:100%; height:100%;">
                <span style="position:absolute; top:10px; right:20px; font-size:30px; color:white; cursor:pointer; z-index:9000;" onclick="closeFullMap()">&times;</span>
                <div id="fullIncidentMap" style="width:100%; height:100%;"></div>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="modal-overlay">
                <div class="modal-box <?= $status === 'success' ? 'success' : 'error' ?>">
                    <div class="modal-icon">
                        <?= $status === 'success' ? '✔' : '❌' ?>
                    </div>
                    <p><?= htmlspecialchars($message) ?></p>
                    <button onclick="closePopup()">OK</button>
                </div>
            </div>
        <?php endif; ?>
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

    function allowOnlyNumbers(input) {
        input.value = input.value.replace(/[^0-9]/g, '');
    }

    // SOS Modal
    var sosform = document.getElementById("sosform");

    function openSOSForm() {
        sosform.style.display = "flex";
    }

    function closeSOSForm() {
        sosform.style.display = "none";
    }
</script>

<!-- Map Script -->
<script>
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

    // Pins from PHP
    var pins = <?php echo $pinreports_json; ?>;

    // ---- Incident Map on dashboard ----
    let incidentMap = L.map('incident-map').setView([6.4432, 100.2056], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(incidentMap);

    // Add pins
    pins.forEach(function(pin) {
        if (pin.latitude && pin.longitude) {
            let icon = pin.type === 'report' ? greenIcon : redIcon;
            let popup = pin.type === 'report' ?
                `<b>Report: ${pin.report_type}</b><br>Title: ${pin.report_title}<br>Status: ${pin.report_status}<br>Submitted by: ${pin.submitted_by}` :
                `<b>SOS Alert</b><br>Status: ${pin.sos_status}<br>Sent by: ${pin.sent_by}`;

            L.marker([pin.latitude, pin.longitude], {
                    icon: icon
                })
                .addTo(incidentMap)
                .bindPopup(popup);
        }
    });

    // ---- Fullscreen Map ----
    let fullMap; // global variable
    function openFullMap() {
        const modal = document.getElementById('fullMapModal');
        modal.style.display = 'block';

        setTimeout(() => {
            // Remove old map if exists
            if (fullMap) fullMap.remove();

            // Initialize map
            fullMap = L.map('fullIncidentMap');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(fullMap);

            // Add all pins
            pins.forEach(function(pin) {
                if (pin.latitude && pin.longitude) {
                    let icon = pin.type === 'report' ? greenIcon : redIcon;
                    let popup = pin.type === 'report' ?
                        `<b>Report: ${pin.report_type}</b><br>Title: ${pin.report_title}<br>Status: ${pin.report_status}<br>Submitted by: ${pin.submitted_by}` :
                        `<b>SOS Alert</b><br>Status: ${pin.sos_status}<br>Sent by: ${pin.sent_by}`;

                    L.marker([pin.latitude, pin.longitude], {
                            icon: icon
                        })
                        .addTo(fullMap)
                        .bindPopup(popup);
                }
            });

            // Zoom to fit all pins
            let group = L.featureGroup(pins.map(pin => L.marker([pin.latitude, pin.longitude])));
            fullMap.fitBounds(group.getBounds().pad(0.2));

            // Fix map size
            fullMap.invalidateSize();
        }, 100); // small delay ensures modal is visible
    }

    function closeFullMap() {
        document.getElementById('fullMapModal').style.display = 'none';
        if (fullMap) fullMap.remove();
    }

    // ---- Map Picker for Report / SOS ----
    let mapPicker, reportMarker, sosMarker;

    function openMapPicker(type) {
        document.getElementById("mapModal").style.display = "block";

        setTimeout(() => {
            if (mapPicker) mapPicker.remove();

            mapPicker = L.map('map').setView([6.4432, 100.2056], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(mapPicker);

            mapPicker.on('click', function(e) {
                let lat = e.latlng.lat;
                let lng = e.latlng.lng;

                if (type === 'report') {
                    if (reportMarker) reportMarker.setLatLng(e.latlng);
                    else reportMarker = L.marker(e.latlng, {
                        icon: greenIcon
                    }).addTo(mapPicker);

                    document.getElementById("latitude").value = lat;
                    document.getElementById("longitude").value = lng;

                } else if (type === 'sos') {
                    if (sosMarker) sosMarker.setLatLng(e.latlng);
                    else sosMarker = L.marker(e.latlng, {
                        icon: redIcon
                    }).addTo(mapPicker);

                    document.getElementById("sos_latitude").value = lat;
                    document.getElementById("sos_longitude").value = lng;
                }
            });

            mapPicker.invalidateSize();
        }, 100);
    }

    function closeMap() {
        document.getElementById("mapModal").style.display = "none";
    }

    function closePopup() {
        document.querySelector('.modal-overlay').style.display = 'none';
    }
</script>


</html>