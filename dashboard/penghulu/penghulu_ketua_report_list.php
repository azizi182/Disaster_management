<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'penghulu') {
    header('Location: ../login.php');
    exit();
}

$villager_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];

// Fetch reports for this villager ONLY
$sql = "
    SELECT 
        r.*,
        k.user_name AS ketua_name
    FROM ketua_report r
    JOIN tbl_users k ON r.ketua_id = k.user_id
    ORDER BY r.created_at DESC
";


$result = mysqli_query($conn, $sql);
// Handle Approve, Reject, Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kt_report_id = $_POST['kt_kt_report_id'];

    // Approve
    if (isset($_POST['approve'])) {
        $message = mysqli_real_escape_string($conn, $_POST['approve_msg'] ?? '');
        mysqli_query($conn, "UPDATE ketua_report SET report_status='Approved' WHERE kt_report_id='$kt_report_id'");

        // Optional: Insert message to Ketua
        if (!empty($message)) {
            $report = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ketua_id FROM ketua_report WHERE kt_report_id='$kt_report_id'"));
            $ketua_id = $report['ketua_id'];
            mysqli_query($conn, "INSERT INTO report_messages (kt_report_id, sender_role, receiver_id, message, created_at)
                                VALUES ('$kt_report_id', 'penghulu', '$ketua_id', '$message', NOW())");
        }
        header("Location: penghulu_ketua_report_list.php");
        exit();
    }

    // Reject
    if (isset($_POST['reject'])) {
        mysqli_query($conn, "UPDATE ketua_report SET report_status='Rejected' WHERE kt_report_id='$kt_report_id'");
        header("Location: penghulu_ketua_report_list.php");
        exit();
    }

    // Delete
    if (isset($_POST['delete'])) {
        mysqli_query($conn, "DELETE FROM ketua_report WHERE kt_report_id='$kt_report_id'");
        header("Location: penghulu_ketua_report_list.php");
        exit();
    }
}

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
                <li><a href="#"><i class="fa fa-comments"></i> Communicate with Pejabat Daerah</a></li>
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

                    <?php $i = 1;
                    while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['ketua_name']) ?></td>
                            <td><?= htmlspecialchars($row['report_title']) ?></td>
                            <td><?= htmlspecialchars($row['report_desc']) ?></td>
                            <td><?= htmlspecialchars($row['report_location']) ?></td>
                            <td><?= htmlspecialchars($row['report_status']) ?></td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                            <td>
                                <!-- Approve -->
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="kt_report_id" value="<?= $row['kt_report_id'] ?>">
                                    <input type="text" name="approve_msg" placeholder="Message to Ketua" style="width:150px;">
                                    <button type="submit" name="approve" style="background:green;color:white;">Approve</button>
                                </form>

                                <!-- Reject -->
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="kt_report_id" value="<?= $row['kt_report_id'] ?>">
                                    <button type="submit" name="reject" style="background:orange;color:white;">Reject</button>
                                </form>

                                <!-- Delete -->
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="kt_report_id" value="<?= $row['kt_report_id'] ?>">
                                    <button type="submit" name="delete" style="background:red;color:white;">Delete</button>
                                </form>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

        </div>
    </div>

</body>

</html>