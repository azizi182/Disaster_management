<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'villager') {
    header('Location: ../login.php');
    exit();
}

$villager_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];

$kampung_id = '';
$kampung_name = '';

$stmt = $conn->prepare("SELECT kampung_id FROM tbl_users WHERE user_id = ?");
$stmt->bind_param("i", $villager_id);
$stmt->execute();
$stmt->bind_result($kampung_id);
$stmt->fetch();
$stmt->close();

if (!empty($kampung_id)) {
    $stmt = $conn->prepare("SELECT kampung_name FROM tbl_kampung WHERE kampung_id = ?");
    $stmt->bind_param("i", $kampung_id);
    $stmt->execute();
    $stmt->bind_result($kampung_name);
    $stmt->fetch();
    $stmt->close();
}

// Fetch reports for this villager ONLY
$sql = "
    SELECT 
        vr.*,
        u.user_name AS ketua_name
    FROM villager_report vr
    JOIN tbl_users u ON vr.ketua_id = u.user_id
    WHERE vr.villager_id = '$villager_id'
    ORDER BY vr.report_date ASC
";


$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Reports - villager</title>

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
            <h2>Village - <?php echo $username; ?></h2>
            <ul>
                <li><a href="villager_dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="villager_report_list.php"><i class="fa fa-flag"></i> Submit Report,Emergency / Complaint</a></li>
                <li><a href="villager_announce_list.php"><i class="fa fa-bell"></i> Announcement / Alerts</a></li>

                
                <li><a href="#"><i class="fa-solid fa-map-location-dot"></i> Incident Map</a></li>
                <li><a href="../../logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main -->
        <div class="main">
            <div class="header">
                <h1>My Reports</h1>
                <p>Logged in as: <?= htmlspecialchars($username) ?></p>
            </div>

            <div class="table-container">
                <a href="villager_dashboard.php" class="back-btn">← Back to Dashboard</a>

                <table>
                    <tr>
                        <th>Id</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Location</th>
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
                                <td><?= htmlspecialchars($row['report_title']) ?></td>
                                <td><?= htmlspecialchars($row['report_type']) ?></td>
                                <td><?= htmlspecialchars($row['report_desc']) ?></td>
                                <td><?= htmlspecialchars($row['report_date']) ?></td>
                                <td><?= htmlspecialchars($row['report_location']) ?></td>
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
    </script>

    <!-- map script -->
    <div id="mapModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; justify-content:center; align-items:center;">
        <div style="background:#fff; width:90%; max-width:600px; padding:10px; border-radius:8px;">
            <h3>Incident Location</h3>
            <div id="viewMap" style="height:350px;"></div>
            <button onclick="closeMap()">Close</button>
        </div>
    </div>


</body>

<script>
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
</script>


</html>