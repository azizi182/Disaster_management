<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ketuakampung') {
    header('Location: ../login.php');
    exit();
}

$ketua_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];

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
    </style>
</head>

<body>
    <div class="dashboard">


        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Ketua Kampung - <?php echo $username; ?></h2>
            <ul>
                <li><a href="ketuakampung_dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="#"><i class="fa fa-edit"></i> Monitor Village Reports - Notify Village</a></li>
                <li><a href="#"><i class="fa fa-calendar-plus"></i> Announcement for villagers</a></li>
                <li><a href="#"><i class="fa fa-comments"></i> Communicate with Penghulu</a></li>
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
                                <td><?= htmlspecialchars($row['villager_name']) ?></td>
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
            <form method="POST" action="approve_report.php" class="reportformketua">

                <div class="form-card">
                    <span class="close" onclick="closeForm()">&times;</span>
                    <h2>Submit Feedback</h2>

                    <input type="hidden" name="report_id" id="report_id">
                    <input type="hidden" name="report_status" value="Approved">

                    <label>Report Title</label>
                    <input type="text" id="report_title" readonly>

                    <label>Report by</label>
                    <input type="text" id="villager_name" readonly>

                    <label>Feedback</label>
                    <textarea type="text" name="feedback" rows="4" required></textarea>


                    <button class="btn" name="submitreport">Submit Feedback</button>
                </div>
            </form>

            <?php if (isset($_GET['success'])): ?>
                <script>
                    alert("Report submitted successfully!");
                </script>
            <?php endif; ?>

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

    //map
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