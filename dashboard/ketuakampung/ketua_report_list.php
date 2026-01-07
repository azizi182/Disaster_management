<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ketuakampung') {
    header('Location: ../login.php');
    exit();
}

$ketua_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$message = "";
$status = "";

// 2. CHECK FOR SUCCESS URL PARAMETERS (From redirects)
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $status = "success";
    $message = "Report approved and feedback submitted successfully!";
}


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
$stmt->bind_param("i", $ketua_id);
$stmt->execute();
$stmt->bind_result($kampung_id, $kampung_name);
$stmt->fetch();
$stmt->close();

// 3. HANDLE FORM SUBMISSION (Approve Report)
if (isset($_POST['submitreport'])) {
    $report_id = (int) $_POST['report_id'];
    $rpt_status = "Approved"; // Fixed status
    $raw_feedback = $_POST['feedback'];

    // --- VALIDATION LOGIC ---
    $pattern = "/^[a-zA-Z0-9 ,.-]{3,100}$/";

    if (!preg_match($pattern, $raw_feedback)) {
        // Validation Failed -> Set Error Modal
        $status = "error";
        $message = "Feedback format is invalid.";
    } else {
        // Validation Passed -> Update DB
        $feedback = mysqli_real_escape_string($conn, $raw_feedback);

        $sql = "UPDATE villager_report
                SET report_status = '$rpt_status',
                    report_feedback = '$feedback'
                WHERE report_id = '$report_id'";

        if (mysqli_query($conn, $sql)) {
            // Success -> Redirect to clear form and show success modal
            header("Location: ketua_report_list.php?success=1");
            exit();
        } else {
            // DB Error -> Show Error Modal
            $status = "error";
            $message = "Database Error: " . mysqli_error($conn);
        }
    }
}

// Fetch reports for this villager ONLY
$sql = "
    SELECT
        rpt.*,
        u.user_name AS villager_name
    FROM villager_report rpt
    JOIN tbl_users u ON rpt.villager_id = u.user_id
    WHERE rpt.ketua_id = '$ketua_id'
   ORDER BY 
        CASE rpt.report_status
            WHEN 'Pending' THEN 1
            ELSE 2
        END,
        rpt.report_date ASC
";


$result = mysqli_query($conn, $sql);

// Function to get header color based on announcement type

$sqlsos =  "SELECT 
        s.*,
        u.user_name AS villager_name
    FROM sos_villager s
    JOIN tbl_users u ON s.villager_id = u.user_id
    WHERE s.sos_status = 'Sent'
    ORDER BY s.created_at ASC";
$resultsos = mysqli_query($conn, $sqlsos);
$sosList = mysqli_fetch_all($resultsos, MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ketua Reports</title>

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

        button:disabled {
            background-color: #9ca3af !important;
            /* grey */
            color: #ffffff;
            cursor: not-allowed;
            opacity: 0.7;
        }


        /* reportformketua */
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

        .reportformketua {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .reportformketua h2 {
            text-align: center;
            margin: 0 auto;
        }

        .reportformketua label {
            display: block;
            margin-bottom: 5px;
        }

        .reportformketua input,
        .reportformketua select,
        .reportformketua textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;

        }

        .reportformketua .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .table-soscontainer {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            margin-bottom: 50px;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0% {
                box-shadow: 0 0 10px red;
            }

            50% {
                box-shadow: 0 0 30px red;
            }

            100% {
                box-shadow: 0 0 10px red;
            }
        }

        .table-soscontainer th {
            background: #AF1E1EFF;
            color: whibte;
        }

        #sosMap {
            height: 400px;
            /* fixed height */
            width: 100%;
            border-radius: 10px;
            margin-bottom: 25px;
            /* GAP between map & table */
            border: 2px solid #e5e7eb;
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
</head>

<body>
    <div class="dashboard">


        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Ketua Kampung - <?php echo $username; ?></h2>
            <ul>
                <li><a href="ketuakampung_dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="ketua_report_list.php"><i class="fa fa-edit"></i> Monitor Village Reports - Notify Village</a></li>
                <li><a href="ketua_annoucment_list.php"><i class="fa fa-calendar-plus"></i> Announcement for villagers</a></li>

                <li>
                    <a href="javascript:void(0)" onclick="openFullMap()">
                        <i class="fa-solid fa-map-location-dot"></i> Incident Map
                    </a>
                </li>
                <li><a href="../../logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main -->
        <div class="main">
            <div class="header">
                <h1>My Reports</h1>
                <p>Logged in as: <?= htmlspecialchars($username) ?> from <?= htmlspecialchars($kampung_name) ?></p>
            </div>

            <div class="table-soscontainer">
                <h2 style="color:white;">🚨 SOS Incident Map</h2>
                <div id="sosMap"></div>

                <table>
                    <tr>
                        <th>No</th>
                        <th>Villager</th>

                        <th>Status</th>
                        <th>Time</th>

                        <th>Action</th>
                    </tr>

                    <?php if (count($sosList) > 0): ?>
                        <?php $i = 1;
                        foreach ($sosList as $sos): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($sos['villager_name']) ?></td>
                                <td><b><?= htmlspecialchars($sos['sos_status']) ?></b></td>
                                <td><?= htmlspecialchars($sos['created_at']) ?></td>
                                <td>
                                    <button onclick="resolveSOS(<?= $sos['sos_id'] ?>)">Resolve</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No SOS alerts</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>



            <div class="table-container">
                <a href="ketuakampung_dashboard.php" class="back-btn">← Back to Dashboard</a>

                <table>
                    <tr>
                        <th>No</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>View Map</th>
                        <th>Penduduk</th>
                        <th>Status</th>
                        <th>Action</th>
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
                                <td><?= htmlspecialchars($row['villager_name'])  ?></td>
                                <td class="status-<?= strtolower($row['report_status']) ?>">
                                    <?= htmlspecialchars($row['report_status']) ?>
                                </td>
                                <td>
                                    <?php if ($row['report_status'] === 'Pending'): ?>
                                        <button class="btn btn-success"
                                            onclick="openForm(
                                            <?= $row['report_id'] ?>,
                                            '<?= htmlspecialchars(addslashes($row['report_title'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['villager_name'])) ?>'
                                        )">
                                            Approve
                                        </button>

                                        <button class="btn btn-danger"
                                            onclick="rejectReport(<?= $row['report_id'] ?>)">
                                            Reject
                                        </button>

                                        <button class="btn btn-warning"
                                            onclick="deleteReport(<?= $row['report_id'] ?>)">
                                            Delete
                                        </button>

                                    <?php else: ?>
                                        <button class="btn" disabled>Approve</button>
                                        <button class="btn" disabled>Reject</button>
                                        <button class="btn" disabled>Delete</button>
                                    <?php endif; ?>
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


        <!-- reportform -->
        <div id="reportform">
            <form method="POST" action="" class="reportformketua">

                <div class="form-card">
                    <span class="close" onclick="closeForm()" style="float:right; cursor:pointer; font-size:20px;">&times;</span>
                    <h2>Submit Feedback</h2>

                    <input type="hidden" name="report_id" id="report_id">
                    

                    <label>Report Title</label>
                    <input type="text" id="report_title" readonly>

                    <label>Report by</label>
                    <input type="text" id="villager_name" readonly>

                    <label>Feedback</label>
                    <textarea type="text" name="feedback" rows="4" required placeholder="Enter feedback (Letters, numbers, commas, dots, dashes only)"></textarea>


                    <button class="btn" name="submitreport">Confirm Approval</button>
                </div>
            </form>

            <?php if (!empty($message)): ?>
                <div class="modal-overlay">
                    <div class="modal-box <?= $status === 'success' ? 'success' : 'error' ?>">
                        <div class="modal-icon">
                            <?= $status === 'success' ? '✔' : '❌' ?>
                        </div>
                        <p><?= htmlspecialchars($message) ?></p>
                        <button onclick="closeModal()">OK</button>
                    </div>
                </div>

                <?php if ($status === 'success'): ?>
                    <script>
                        if (window.history.replaceState) {
                            const url = new URL(window.location);
                            url.searchParams.delete('success');
                            window.history.replaceState(null, '', url);
                        }
                    </script>
                <?php endif; ?>
            <?php endif; ?>

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

    <!-- sos map script -->
    <script>
        var redIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        const sosData = <?= json_encode($sosList); ?>;

        let sosMap = L.map('sosMap').setView([6.43782726, 100.19387055], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(sosMap);

        sosData.forEach(sos => {
            if (!sos.latitude || !sos.longitude) return;

            L.marker(
                    [parseFloat(sos.latitude), parseFloat(sos.longitude)], {
                        icon: redIcon
                    } // ✅ THIS LINE IS THE KEY
                )
                .addTo(sosMap)
                .bindPopup(`
            <b>🚨 SOS Alert</b><br>
            <b>Villager:</b> ${sos.villager_name}<br>
            <b>Message:</b> ${sos.sos_msg ?? 'N/A'}<br>
            <b>Time:</b> ${sos.created_at}
        `);
        });
    </script>

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

    function openForm(reportId, title, villager) {
        reportform.style.display = "flex";
        document.getElementById("report_id").value = reportId;
        document.getElementById("report_title").value = title;
        document.getElementById("villager_name").value = villager
    }

    function closeForm() {
        reportform.style.display = "none";
    }

    function closeModal() { document.querySelector('.modal-overlay').style.display = 'none'; }

    function deleteReport(reportId) {
        if (confirm("Are you sure you want to delete this report?")) {
            window.location.href = "delete_report.php?report_id=" + reportId;
        }
    }

    function rejectReport(reportId) {
        if (confirm("Are you sure you want to reject this report?")) {
            window.location.href = "reject_report.php?report_id=" + reportId;
        }
    }
    //sos
    function resolveSOS(sosId) {
        if (confirm("Mark this SOS as resolved?")) {
            window.location.href = "resolve_sos.php?sos_id=" + sosId;
        }
    }

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

    // Close full map modal
    function closeFullMap() {
        document.getElementById('fullMapModal').style.display = 'none';
        if (window.fullMap) window.fullMap.remove();
    }
</script>

</html>