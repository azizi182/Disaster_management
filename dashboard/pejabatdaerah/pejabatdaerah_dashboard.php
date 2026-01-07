<?php
session_start();

include "../../dbconnect.php";

if (isset($_GET['initiateaid_success'])) {
    $status = "success";
    $message = "Aid Command Issued successfully!";
}

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["user_role"] !== "pejabatdaerah"
) {
    header("Location: ../login.php");
    exit();
}

$pejabatdaerah_id = $_SESSION["user_id"];
$penghulu_role = "penghulu";

//todo; change to preparestatement

$stmt = $conn->prepare(
    "SELECT user_id, user_name FROM tbl_users WHERE user_role = ? ",
);
$stmt->bind_param("s", $penghulu_role);
$stmt->execute();
$result = $stmt->get_result();
$dataPenghulu = $result->fetch_all(MYSQLI_ASSOC);

//initiate aid distribution, send to penghulu
if (isset($_POST["initiateaid"])) {
    //aid distribution POST form data
    $penghulu_id = $_POST["penghulu_id"];
    $type = $_POST["aid_type"];
    $title = $_POST["aid_title"];
    $description = $_POST["aid_description"];
    $date = $_POST["aid_date"];
    $location = $_POST["aid_location"];

    //pattern of characters to check
    $pattern = "/^[a-zA-Z0-9 ,.-]{3,100}$/";

    // Check specific inputs
    $inputs = [
        "Title" => $title,
        "Description" => $description,
        "location" => $location,
    ];

    //if any of text inputs is invalid, error popup displayed
    $validation_error = false;
    foreach ($inputs as $name => $value) {
        if (!preg_match($pattern, $value)) {
            $status = "error";
            $message = "$name format is invalid. Only letters, numbers, spaces, commas, dots, and dashes allowed.";
            $validation_error = true;
            break;
        }
    }

    // Only proceed query if validation passed.
    if (!$validation_error) {
        $stmt = $conn->prepare(
            "INSERT INTO `pejabatdaerah_aid_distribution`
        (`pejabatdaerah_id`, `aid_type`, `penghulu_id` , `distribution_title`, `distribution_desc`, `distribution_date`, `distribution_location`)
        VALUES (?,?,?,?,?,?,?);",
        );
        $stmt->bind_param(
            "isissss",
            $pejabatdaerah_id,
            $type,
            $penghulu_id,
            $title,
            $description,
            $date,
            $location,
        );

        if ($stmt->execute()) {
            // Success: Redirect to clear POST data and show success message
            header(
                "Location: pejabatdaerah_dashboard.php?initiateaid_success=1",
            );
            exit();
        } else {
            // DB Error
            $status = "error";
            $message = "Error submitting report: " . mysqli_error($conn);
        }
    }
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["user_name"];
$role = $_SESSION["user_role"];

//map
// penghulu reports
$report_sql = "SELECT r.latitude, r.longitude, r.report_title, r.report_type, r.report_status,
                u.user_name AS submitted_by
                FROM villager_report r
                JOIN tbl_users u ON
                r.villager_id = u.user_id
                WHERE r.report_status = 'Pending'";
$report_result = mysqli_query($conn, $report_sql);
$reports = [];
while ($row = mysqli_fetch_assoc($report_result)) {
    $row["type"] = "report";
    $reports[] = $row;
}

//Alerts on map
$sos_sql = "SELECT s.latitude, s.longitude, s.sos_status, u.user_name AS sent_by
            FROM sos_villager s
            JOIN tbl_users u ON s.villager_id = u.user_id
            WHERE s.sos_status = 'Sent'";
$sos_result = mysqli_query($conn, $sos_sql);
$sos = [];
while ($row = mysqli_fetch_assoc($sos_result)) {
    $row["type"] = "sos";
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
    <title>Pejabat Daerah Dashboard - DVMD</title>
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

    #disastercommandform {
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

    .notificationformpejabatdaerah {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 400px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .notificationformpejabatdaerah h2 {
        text-align: center;
        margin: 0 auto;
    }

    .notificationformpejabatdaerah label {
        display: block;
        margin-bottom: 5px;
    }

    .notificationformpejabatdaerah input,
    .notificationformpejabatdaerah select,
    .notificationformpejabatdaerah textarea {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;

    }

    .notificationformpejabatdaerah .btn {
        background-color: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    #aiddistributionform {
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

    .aiddistributionform {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 400px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .aiddistributionform h2 {
        text-align: center;
        margin: 0 auto;
    }

    .aiddistributionform label {
        display: block;
        margin-bottom: 5px;
    }

    .aiddistributionform input,
    .aiddistributionform select,
    .aiddistributionform textarea {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;

    }

    .aiddistributionform .btn {
        background-color: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

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
            <h2>Pejabat Daerah</h2>
            <ul>
                <li><a href="pejabatdaerah_dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="pejabatdaerah_report_list.php"><i class="fa-solid fa-city"></i> Monitor All Villages</a></li>
                <li><a href="pejabatdaerah_penghulu_report_list.php"><i class="fa-solid fa-file-lines"></i> Reports From Penghulu </a></li>
                <li><a href="../../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main -->
        <main class="main">

            <!-- Header -->
            <div class="header">
                <h1>Welcome, <?php echo $username; ?> !</h1>
                <p>Digital Village Management Dashboard (DVMD)</p>
            </div>

            <!-- Content -->
            <section class="content">

                <!-- Full village access -->
                <div class="card">
                    <h3>Access and Monitor All Villages</h3>
                    <p>View and manage report for all villages in the district.</p>
                    <a href="pejabatdaerah_report_list.php"><button>View Villages</button></a>
                </div>

                <!-- Aid distribution -->
                <div class="card">
                    <h3>Aid Distribution Management</h3>
                    <p>Initiate and track aid distribution to affected areas.</p>
                    <a onclick="openaiddistributionForm()"><button>Manage Aid</button></a>
                </div>


                <!-- Reports from Penghulu -->
                <div class="card">
                    <h3> Reports From Penghulu</h3>
                    <p>communicate with Penghulu and Review reports received from Penghulu.</p>
                    <!--create pejabatdaerah-penghulu report list-->
                    <a href="pejabatdaerah_penghulu_report_list.php"><button>View Reports</button></a>
                </div>


                <!-- Map -->
                <div class="card">
                    <h3>Incident Map</h3>
                    <p>Track emergencies using GPS/maps.</p>
                    <div id="incident-map" class="map-placeholder" onclick="openFullMap()"></div>
                </div>



            </section>
        </main>
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

    <div id="disastercommandform">
        <form method="POST" action="" class="notificationformpejabatdaerah">

            <div class="form-card">
                <span class="close" onclick="closeForm()">&times;</span>
                <h2>Issue Commands</h2>

                <label>Type</label>
                <select name="announcement_type" required>
                    <option value="">Select Command Type</option>
                    <option value="event">Evacuate</option>
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




    </div>

    <div id="aiddistributionform" style="display:none;">
        <form method="POST" action="" class="aiddistributionform">

            <div class="form-card">
                <span class="close" onclick="closeaiddistributionForm()">&times;</span>
                <h2>Initiate Aid Distribution</h2>

                <label>Aid Type</label>
                <select name="aid_type" required>
                    <option value="">Select Aid Type</option>
                    <option value="transportation">Transportation</option>
                    <option value="water">Water</option>
                    <option value="necessity">Necessity</option>
                    <option value="clothes">clothes</option>
                </select>
                <label>Penghulu</label>
                <select name="penghulu_id" required>
                    <option value="">Select Penghulu</option>
                    <?php foreach ($dataPenghulu as $rowPenghulu): ?>
                        <option value="<?= htmlspecialchars(
                                            $rowPenghulu["user_id"],
                                        ) ?>">
                            <?= htmlspecialchars(
                                $rowPenghulu["user_name"],
                            ) ?>
                        </option>
                    <?php endforeach; ?>
                </select>


                <label>Title</label>
                <input type="text" name="aid_title" required>

                <label>Description</label>
                <textarea name="aid_description" required></textarea>

                <label>Date</label>
                <input type="date" name="aid_date" required>

                <label>Location</label>
                <input type="text" name="aid_location" placeholder="GPS / Address">

                <button class="btn" name="initiateaid">Confirm Publish</button>
            </div>
        </form>



    </div>

    <?php if (!empty($message)): ?>
        <div class="modal-overlay">
            <div class="modal-box <?= $status === "success"
                                        ? "success"
                                        : "error" ?>">
                <div class="modal-icon">
                    <?= $status === "success" ? "✔" : "❌" ?>
                </div>
                <p><?= htmlspecialchars($message) ?></p>
                <button onclick="closeModal()">OK</button>
            </div>
        </div>

        <?php if ($status === "success"): ?>
            <script>
                // Optional: Remove the 'success' query param from URL without refreshing so the modal doesn't show again on manual refresh
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('success');
                    url.searchParams.delete('success_reportpenghulu');
                    window.history.replaceState(null, '', url);
                }
            </script>
        <?php endif; ?>
    <?php endif; ?>

</body>

<!-- Map Script -->
<script>
    var disastercommandform = document.getElementById("disastercommandform");
    var aiddistributionform = document.getElementById("aiddistributionform");


    function openForm() {
        disastercommandform.style.display = "flex";
    }

    function closeForm() {
        disastercommandform.style.display = "none";
    }

    function openaiddistributionForm() {
        aiddistributionform.style.display = "flex";
    }

    function closeaiddistributionForm() {
        aiddistributionform.style.display = "none";
    }


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

    function closeModal() {
        document.querySelector('.modal-overlay').style.display = 'none';
    }
</script>

</html>