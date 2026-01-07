<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'penghulu') {
    header('Location: ../login.php');
    exit();
}
$penghulu_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
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


$kampung_ids = array_column($kampung_list, 'kampung_id'); // e.g., [1,2]

if (count($kampung_ids) > 0) {
    // 1. Make placeholders
    $placeholders = implode(',', array_fill(0, count($kampung_ids), '?'));

    // 2. Prepare SQL with placeholders
    $sql = "
        SELECT
            vr.*,
            v.user_name AS villager_name,
            k.user_name AS ketua_name,
            tk.kampung_name
        FROM villager_report vr
        JOIN tbl_users v ON vr.villager_id = v.user_id
        JOIN tbl_users k ON vr.ketua_id = k.user_id
        LEFT JOIN user_kampung uk ON v.user_id = uk.user_id
        LEFT JOIN tbl_kampung tk ON uk.kampung_id = tk.kampung_id
        WHERE tk.kampung_id IN ($placeholders)
        ORDER BY vr.report_date ASC
    ";

    $stmt = $conn->prepare($sql);

    // 3. Bind parameters dynamically
    // Create string of types, all 'i' for integer
    $types = str_repeat('i', count($kampung_ids));

    // bind_param requires references
    $stmt->bind_param($types, ...$kampung_ids);

    // 4. Execute and get result
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // No kampung assigned, return empty result
    $result = false;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title> Reports List From Villager</title>

    <link rel="stylesheet" href="../../css/style_villager_dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #1e40af;
            color: white;
        }

        .status-pending {
            color: orange;
            font-weight: bold;
        }

        .status-approved {
            color: green;
            font-weight: bold;
        }

        .status-rejected {
            color: red;
            font-weight: bold;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 15px;
            text-decoration: none;
            color: #1e40af;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="dashboard">


        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Penghulu - <?php echo $username; ?></h2>
            <ul>
                <li><a href="penghulu_dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href=""><i class="fa-solid fa-city"></i> Monitor All Villages - Review Issues - Notify Ketua Kampung</a></li>
                <li><a href="penghulu_ketua_report_list.php"><i class="fa-solid fa-file-lines"></i> Reports from Ketua Kampung</a></li>
                <li><a href="#"><i class="fa fa-comments"></i> Report to Pejabat Daerah</a></li>
                <li>
                    <a href="javascript:void(0)" onclick="openFullMap()">
                        <i class="fa-solid fa-map-location-dot"></i> Incident Map
                    </a>
                </li>

                <li><a href="../../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main -->
        <div class="main">
            <div class="header">
                <h1>Report List From Villager</h1>
                <p>Logged in as: <?= htmlspecialchars($username) ?> </p>
            </div>

            <div class="table-container">
                <a href="penghulu_dashboard.php" class="back-btn">← Back to Dashboard</a>

                <table>
                    <tr>
                        <th>No</th>
                        <th>Name Villager</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Kampung</th>
                        <th>View Map</th>

                        <th>Ketua Kampung</th>
                        <th>Status</th>
                        <th>Feedback</th>

                    </tr>

                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $i = 1;
                        while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['villager_name']) ?></td>
                                <td><?= htmlspecialchars($row['report_title']) ?></td>

                                <td><?= htmlspecialchars($row['report_type']) ?></td>
                                <td><?= htmlspecialchars($row['report_desc']) ?></td>
                                <td><?= htmlspecialchars($row['report_date']) ?></td>
                                <td><?= htmlspecialchars($row['report_location']) ?></td>
                                <td><?= htmlspecialchars($row['kampung_name']) ?></td>

                                <td>
                                    <button onclick="viewMap(
                                            '<?= $row['latitude'] ?>', 
                                            '<?= $row['longitude'] ?>'
                                            )">
                                        📍 View Map
                                    </button>
                                </td>
                                <td><?= htmlspecialchars($row['ketua_name']) ?></td>
                                <td class="status-<?= strtolower($row['report_status']) ?>">
                                    <?= htmlspecialchars($row['report_status']) ?>
                                </td>
                                <td>
                                    <button onclick="showFeedback(
                                            '<?= htmlspecialchars(addslashes($row['report_title'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['report_feedback'])) ?>',
                                            '<?= $row['report_status'] ?>'
                                        )">
                                        View
                                    </button>

                                </td>


                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No reports submitted yet</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

    </div>
    <!-- Feedback Modal -->
    <div id="feedbackModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div style="background:#fff; padding:20px; border-radius:8px; width:400px;">
            <h3 id="f_title"></h3>
            <p><b>Status:</b> <span id="f_status"></span></p>
            <p><b>Feedback:</b></p>
            <p id="f_feedback"></p>
            <button onclick="closeFeedback()">Close</button>
        </div>
    </div>

    <!-- map script -->
    <div id="mapModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; justify-content:center; align-items:center;">
        <div style="background:#fff; width:90%; max-width:600px; padding:10px; border-radius:8px;">
            <h3>Incident Location</h3>
            <div id="viewMap" style="height:350px;"></div>
            <button onclick="closeMap()">Close</button>
        </div>
    </div>



    <script>
        function showFeedback(title, feedback, status) {
            document.getElementById("f_title").innerText = title;
            document.getElementById("f_feedback").innerText = feedback || "No feedback";
            document.getElementById("f_status").innerText = status;
            document.getElementById("feedbackModal").style.display = "flex";
        }

        function closeFeedback() {
            document.getElementById("feedbackModal").style.display = "none";
        }

        //map
        let viewMapObj;
        let viewMarker;

        function viewMap(lat, lng) {
            document.getElementById("mapModal").style.display = "flex";

            setTimeout(() => {
                if (viewMapObj) {
                    viewMapObj.remove();
                }

                viewMapObj = L.map('viewMap').setView([lat, lng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(viewMapObj);

                viewMarker = L.marker([lat, lng]).addTo(viewMapObj)
                    .bindPopup("Incident Location")
                    .openPopup();

            }, 200);
        }

        function closeMap() {
            document.getElementById("mapModal").style.display = "none";
        }


        //full map
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
    </script>


</body>

</html>