<?php
session_start();
include '../../dbconnect.php';

$message = "";
$status = "";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'penghulu') {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['success_reportpejabatdaerah'])) {
    $status = "success";
    $message = "Report submitted successfully!";
}

$penghulu_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$role = $_SESSION['user_role'];

// Fetch kampung info for PENGHULU
$kampung_list = [];

$stmt = $conn->prepare("
    SELECT k.kampung_id, k.kampung_name
    FROM user_kampung uk
    JOIN tbl_kampung k ON uk.kampung_id = k.kampung_id
    WHERE uk.user_id = ?
");
$stmt->bind_param("i", $penghulu_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $kampung_list[] = $row;
}

$stmt->close();


//map
// Villager reports
$kampung_ids = array_column($kampung_list, 'kampung_id');

$reports = [];
if (!empty($kampung_ids)) {
    // Create placeholders like ?,?,?
    $placeholders = implode(',', array_fill(0, count($kampung_ids), '?'));
    $report_sql = "SELECT r.latitude, r.longitude, r.report_title, r.report_type, r.report_status,
        u.user_name AS submitted_by
        FROM villager_report r
        JOIN tbl_users u ON r.villager_id = u.user_id
        JOIN user_kampung uk ON u.user_id = uk.user_id
        WHERE r.report_status = 'Pending' AND uk.kampung_id IN  ($placeholders)";

    $stmt = $conn->prepare($report_sql);

    // Bind kampung_ids dynamically
    $types = str_repeat('i', count($kampung_ids)); // all integers
    $stmt->bind_param($types, ...$kampung_ids);

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row['type'] = 'report';
        $reports[] = $row;
    }

    $stmt->close();
}

// SOS alerts
if (!empty($kampung_ids)) {
    $placeholders = implode(',', array_fill(0, count($kampung_ids), '?'));
    $sos_sql = "SELECT s.latitude, s.longitude, s.sos_status, u.user_name AS sent_by
FROM sos_villager s
JOIN tbl_users u ON s.villager_id = u.user_id
JOIN user_kampung uk ON u.user_id = uk.user_id
WHERE s.sos_status = 'Sent' AND uk.kampung_id IN ($placeholders)";

    $stmt = $conn->prepare($sos_sql);
    $types = str_repeat('i', count($kampung_ids));
    $stmt->bind_param($types, ...$kampung_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $sos = [];
    while ($row = $result->fetch_assoc()) {
        $row['type'] = 'sos';
        $sos[] = $row;
    }

    $stmt->close();
} else {
    $sos = [];
}


// Combine
$allPins = array_merge($reports, $sos);
$pinreports_json = json_encode($allPins);


// Fetch Pejabat Daerah for notification form
$resultPejabatdaerah = $conn->query("SELECT user_id, user_name FROM tbl_users WHERE user_role='pejabatdaerah' ORDER BY user_name ASC");

// Handle report submission to Pejabat Daerah
if (isset($_POST["submit_to_pejabatdaerah"])) {
    $title = htmlspecialchars(trim($_POST["pd_title"]));
    $desc = htmlspecialchars(trim($_POST["pd_desc"]));
    $location = htmlspecialchars(trim($_POST["pd_location"]));
    $pejabatdaerah_id = intval($_POST["pejabatdaerah_id"]);
    $status = "Pending";

    if (!empty($title) && !empty($desc) && !empty($location) && $pejabatdaerah_id > 0) {

        if (empty($pejabatdaerah_id)) {
            echo "<script>alert('Please select a Pejabat Daerah.');</script>";
            exit();
        } else if (!preg_match("/^[a-zA-Z0-9_ ]{3,50}$/", $title)) {
            $status = "error";
            $message = "Invalid title";
        } else if (!preg_match("/^[a-zA-Z0-9_ ]{3,50}$/", $desc)) {
            $status = "error";
            $message = "Invalid title";
        } else if (!preg_match("/^[a-zA-Z0-9_ ]{3,50}$/", $location)) {
            $status = "error";
            $message = "Invalid title";
        } else {
            $stmt = $conn->prepare("INSERT INTO penghulu_report (penghulu_id, pejabat_daerah_id, 
        report_title, report_desc, report_location, report_status) 
        VALUES (?, ?, ?, ?, ?, ?)");

            $stmt->bind_param(
                "iissss",
                $penghulu_id,
                $pejabatdaerah_id,
                $title,
                $desc,
                $location,
                $status
            );
            if ($stmt->execute()) {
                header("Location: penghulu_dashboard.php?success_reportpejabatdaerah=1");
                exit();
            } else {
                echo "<script>alert('Error publishing report: " . $conn->error . "');</script>";
            }
            $stmt->close();
        }
    } else {
        $status = "error";
        $message = "All fields are required.";
    }
}
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

    #notificationformpenghulu {
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

    .notificationformpenghulu {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 400px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .notificationformpenghulu h2 {
        text-align: center;
        margin: 0 auto;
    }

    .notificationformpenghulu label {
        display: block;
        margin-bottom: 5px;
    }

    .notificationformpenghulu input,
    .notificationformpenghulu select,
    .notificationformpenghulu textarea {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;

    }

    .notificationformpenghulu .btn {
        background-color: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;

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

<body>
    <div class="dashboard">

        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Penghulu</h2>
            <ul>
                <li><a href="#"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="penghulu_report_list.php"><i class="fa-solid fa-city"></i> Monitor All Villages - Review Issues - Notify Ketua Kampung</a></li>
                <li><a href="penghulu_ketua_report_list.php"><i class="fa-solid fa-file-lines"></i> Reports from Ketua Kampung</a></li>
                <li><a href="#"><i class="fa fa-comments"></i> Report to Pejabat Daerah</a></li>
                <li>
                    <a href="javascript:void(0)" onclick="openFullMap()">
                        <i class="fa-solid fa-map-location-dot"></i> Incident Map
                    </a>
                </li>

                <li><a href="../../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main -->
        <main class="main">

            <!-- Header -->
            <div class="header">
                <h1>Welcome, <?php echo htmlspecialchars($username); ?> from
                    <?php
                    foreach ($kampung_list as $k) {
                        echo htmlspecialchars($k['kampung_name']) . ' ';
                    }
                    ?>
                </h1>

                <p>Digital Village Management Dashboard (DVMD)</p>
            </div>

            <!-- Content -->
            <section class="content">


                <!-- Monitor villages -->
                <div class="card">
                    <h3>Monitor Village Status</h3>
                    <p>Track safety, emergencies, and village conditions, .</p>
                    <a href="penghulu_report_list.php"><button>Monitor Villages</button></a>
                </div>

                <!-- Review issues -->
                <div class="card">
                    <h3>Reports from Ketua Kampung</h3>
                    <p>Review Reported Issues, Analyze incidents escalated by Ketua Kampung , Send directives or alerts to Ketua Kampung..</p>
                    <a href="penghulu_ketua_report_list.php"><button>Review Issues</button></a>
                </div>

                <!-- Report to Pejabat Daerah -->
                <div class="card critical">
                    <h3>Report to Pejabat Daerah</h3>
                    <p>Escalate critical issues for district action.</p>
                    <button class="danger-btn" onclick="openPejabatdaerahForm()">Submit Report</button>
                </div>

                <!-- Map / Incident Location -->
                <div class="card">
                    <h3>Incident Map</h3>
                    <p>Identify incident points using GPS/maps.</p>
                    <div id="incident-map" class="map-placeholder" onclick="openFullMap()"></div>
                </div>
    </div>

    </section>
    </main>

    </div>
    <!-- Pejabat Daerah Notification Form -->


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


    <!-- Pejabat Daerah Notification Form -->
    <div id="notificationformpenghulu" style="display:none;">
        <form method="POST" action="" class="notificationformpenghulu">
            <div class="form-card">
                <span class="close" onclick="closePejabatdaerahForm()">&times;</span>
                <h2>Report to Pejabat Daerah</h2>
                <label>Report Title</label>
                <input type="text" name="pd_title" required>

                <label>Description</label>
                <textarea name="pd_desc" required></textarea>

                <label>Location</label>
                <input type="text" name="pd_location" required>

                <label>Penghulu</label>
                <select name="pejabatdaerah_id" required>
                    <option value="">Select Pejabat Daerah</option>
                    <?php while (
                        $rowP = mysqli_fetch_assoc($resultPejabatdaerah)
                    ): ?>
                        <option value="<?= htmlspecialchars(
                                            $rowP["user_id"],
                                        ) ?>">
                            <?= htmlspecialchars(
                                $rowP["user_name"],
                            ) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button class="btn" name="submit_to_pejabatdaerah">Submit</button>
                <button type="button" class="btn" onclick="closePejabatdaerahForm()">Cancel</button>
        </form>
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
</body>



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


    // form pejabat daerah
    var notificationformpenghulu = document.getElementById("notificationformpenghulu");

    function openPejabatdaerahForm() {
        notificationformpenghulu.style.display = "flex";
    }

    function closePejabatdaerahForm() {
        notificationformpenghulu.style.display = "none";
    }
    function closePopup() {
        document.querySelector('.modal-overlay').style.display = 'none';
    }
</script>

</html>