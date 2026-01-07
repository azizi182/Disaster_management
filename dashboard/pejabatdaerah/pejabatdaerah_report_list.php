<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pejabatdaerah') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$role = $_SESSION['user_role'];

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
    $row['type'] = 'report';
    $reports[] = $row;
}

// SOS alerts
// change to penghulu reports?
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

$username = $_SESSION['user_name'];

// Fetch reports for this villager ONLY
$sql = "
    SELECT
        vr.*,
        v.user_name AS villager_name,
        k.user_name AS ketua_name
    FROM villager_report vr
    JOIN tbl_users v ON vr.villager_id = v.user_id
    JOIN tbl_users k ON vr.ketua_id = k.user_id
    ORDER BY vr.report_date ASC
";


$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title> Reports List From Villager</title>
    <link rel="stylesheet" href="../../css/style_villager_dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <aside class="sidebar">
            <h2>Pejabat Daerah</h2>
            <ul>
                <li><a href="pejabatdaerah_dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href=""><i class="fa-solid fa-city"></i> Monitor All Villages </a></li>
                <li><a href="pejabatdaerah_penghulu_report_list.php"><i class="fa-solid fa-file-lines"></i> Reports From Penghulu</a></li>
                <li><a href="../../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main -->
        <div class="main">
            <!--header-->
            <div class="header">
                <h1>Report List From Villager</h1>
                <p>Logged in as: <?= htmlspecialchars($username) ?></p>
            </div>

            <!--report list table-->
            <div class="table-container">
                <a href="pejabatdaerah_dashboard.php" class="back-btn">← Back to Dashboard</a>

                <table>
                    <tr>
                        <th>Id</th>
                        <th>Villager</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Location</th>
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
                                <td><?= htmlspecialchars($row['villager_name']) ?></td>
                                <td><?= htmlspecialchars($row['report_type']) ?></td>
                                <td><?= htmlspecialchars($row['report_desc']) ?></td>
                                <td><?= htmlspecialchars($row['report_date']) ?></td>
                                <td><?= htmlspecialchars($row['report_location']) ?></td>
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



</body>



</html>