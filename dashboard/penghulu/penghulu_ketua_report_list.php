<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'penghulu') {
    header('Location: ../login.php');
    exit();
}

$penghulu_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];

// 
$sql = "
    SELECT 
        r.*,
        k.user_name AS ketua_name
    FROM ketua_report r
    JOIN tbl_users k ON r.ketua_id = k.user_id
    WHERE r.penghulu_id = '$penghulu_id'
    ORDER BY 
        CASE r.report_status
            WHEN 'Pending' THEN 1
            ELSE 2
        END,
        r.created_at ASC
";

$result = mysqli_query($conn, $sql);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title> Reports List From Ketua Kampung</title>

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

        /* reportformpenghulu */
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

        .reportformpenghulu {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .reportformpenghulu h2 {
            text-align: center;
            margin: 0 auto;
        }

        .reportformpenghulu label {
            display: block;
            margin-bottom: 5px;
        }

        .reportformpenghulu input,
        .reportformpenghulu select,
        .reportformpenghulu textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;

        }

        .reportformpenghulu .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:disabled {
            background-color: #9ca3af !important;
            /* grey */
            color: #ffffff;
            cursor: not-allowed;
            opacity: 0.7;
        }
    </style>
</head>

<body>
    <div class="dashboard">

        <div class="sidebar">
            <h2>Penghulu - <?php echo $username; ?></h2>
            <ul>
                <li><a href="penghulu_dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="penghulu_report_list.php"><i class="fa-solid fa-city"></i> Monitor All Villages - Review Issues - Notify Ketua Kampung</a></li>
                <li><a href="#"><i class="fa-solid fa-file-lines"></i> Reports from Ketua Kampung</a></li>
                <li><a href="#"><i class="fa fa-comments"></i> Report to Pejabat Daerah</a></li>
                <li>
                    <a href="javascript:void(0)" onclick="openFullMap()">
                        <i class="fa-solid fa-map-location-dot"></i> Incident Map
                    </a>
                </li>
                <li><a href="../../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <h1>Reports from Ketua Kampung</h1>
                <p>Digital Village Management Dashboard (DVMD)</p>
            </div>


            <div class="table-container">
                <table>
                    <tr>
                        <th>No</th>
                        <th>Ketua Kampung</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>


                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $i = 1;
                        while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['ketua_name']) ?></td>
                                <td><?= htmlspecialchars($row['report_title']) ?></td>
                                <td><?= htmlspecialchars($row['report_desc']) ?></td>
                                <td><?= htmlspecialchars($row['report_location']) ?></td>
                                <td class="status-<?= strtolower($row['report_status']) ?>">
                                    <?= htmlspecialchars($row['report_status']) ?>
                                </td>
                                <td><?= htmlspecialchars($row['created_at']) ?></td>
                                <td>
                                    <?php if ($row['report_status'] === 'Pending'): ?>
                                        <button class="btn btn-success"
                                            onclick="openForm(
                                            <?= $row['kt_report_id'] ?>,
                                            '<?= htmlspecialchars(addslashes($row['report_title'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['ketua_name'])) ?>'
                                        )">
                                            Approve
                                        </button>

                                        <button class="btn btn-danger"
                                            onclick="rejectReport(<?= $row['kt_report_id'] ?>)">
                                            Reject
                                        </button>

                                        <button class="btn btn-warning"
                                            onclick="deleteReport(<?= $row['kt_report_id'] ?>)">
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
        <?php if (isset($_GET['rejected'])): ?>
            <script>
                alert("Rejected successfully!");
            </script>
        <?php endif; ?>
        <?php if (isset($_GET['approved'])): ?>
            <script>
                alert("Approved successfully!");
            </script>
        <?php endif; ?>
        <?php if (isset($_GET['delete'])): ?>
            <script>
                alert("Deleted successfully!");
            </script>
        <?php endif; ?>


        <!-- reportform -->
        <div id="reportform">
            <form method="POST" action="penghulu_approve_report.php" class="reportformpenghulu">

                <div class="form-card">
                    <span class="close" onclick="closeForm()">&times;</span>
                    <h2>Submit Feedback</h2>

                    <input type="hidden" name="kt_report_id" id="kt_report_id">
                    <input type="hidden" name="report_status" value="Approved">

                    <label>Report Title</label>
                    <input type="text" id="report_title" readonly>

                    <label>Report by</label>
                    <input type="text" id="ketua_name" readonly>

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

</body>

<script>
    var reportform = document.getElementById("reportform");

    function openForm(reportId, title, ketua) {
        reportform.style.display = "flex";
        document.getElementById("kt_report_id").value = reportId;
        document.getElementById("report_title").value = title;
        document.getElementById("ketua_name").value = ketua
    }

    function closeForm() {
        reportform.style.display = "none";
    }

    function deleteReport(reportId) {
        if (confirm("Are you sure you want to delete this report?")) {
            window.location.href = "penghulu_delete_report.php?kt_report_id=" + reportId;
        }
    }

    function rejectReport(reportId) {
        if (confirm("Are you sure you want to reject this report?")) {
            window.location.href = "penghulu_reject_report.php?kt_report_id=" + reportId;
        }
    }
</script>

</html>