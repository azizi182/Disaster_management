<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'villager') {
    header('Location: ../login.php');
    exit();
}

$villager_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];

function getAnnouncements($conn, $type)
{
    $sql = "
        SELECT 
        a.*, 
        u.user_name AS published_by
        FROM ketua_announce a
        JOIN tbl_users u ON a.ketua_id = u.user_id
        WHERE a.announce_type = '$type'
        ORDER BY a.announce_date ASC
    ";
    return mysqli_query($conn, $sql);
}

$alerts     = getAnnouncements($conn, 'alert');
$events     = getAnnouncements($conn, 'event');
$infos      = getAnnouncements($conn, 'info');
$community  = getAnnouncements($conn, 'community');

// Function to get header color based on announcement type
function renderTable($result)
{
    if (mysqli_num_rows($result) == 0) {
        echo "<p>No announcements available.</p>";
        return;
    }

    echo "
    <table>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Date</th>
            <th>Location</th>
            <th>Published By</th>
        </tr>
    ";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "
        <tr>
            <td>{$row['announce_title']}</td>
            <td>{$row['announce_desc']}</td>
            <td>{$row['announce_date']}</td>
            <td>{$row['announce_location']}</td>
            <td>{$row['published_by']}</td>
        </tr>
        ";
    }

    echo "</table>";
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Announcement List - villager</title>

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

        .back-btn {
            display: inline-block;
            margin-bottom: 15px;
            text-decoration: none;
            color: #1e40af;
            font-weight: bold;
        }

        /* for each announce card */

        .main-content {
            flex: 1;
            padding: 25px;
            display: flex;
            flex-direction: column;
            /* 🔥 vertical */
            gap: 25px;
            /* space between cards */
        }

        .announce-card {
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
        }

        .announce-header {
            padding: 12px;
            color: white;
            font-weight: bold;
        }

        .alert {
            background: #dc2626;
        }

        /* red */
        .event {
            background: #2563eb;
        }

        /* blue */
        .info {
            background: #059669;
        }

        /* green */
        .community {
            background: #7c3aed;
        }

        /* purple */

        .announce-body {
            background: white;
            padding: 15px;
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

                <li><a href="#"><i class="fa-solid fa-triangle-exclamation"></i> SOS</a></li>
                <li><a href="#"><i class="fa-solid fa-map-location-dot"></i> Incident Map</a></li>
                <li><a href="../../logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main -->
        <div class="main-content">

            <div class="announce-card">
                <div class="announce-header alert">🚨 Alerts</div>
                <div class="announce-body">
                    <?php renderTable($alerts); ?>
                </div>
            </div>

            <div class="announce-card">
                <div class="announce-header event">📅 Events</div>
                <div class="announce-body">
                    <?php renderTable($events); ?>
                </div>
            </div>

            <div class="announce-card">
                <div class="announce-header info">ℹ️ Information</div>
                <div class="announce-body">
                    <?php renderTable($infos); ?>
                </div>
            </div>

            <div class="announce-card">
                <div class="announce-header community">👥 Community Activities</div>
                <div class="announce-body">
                    <?php renderTable($community); ?>
                </div>
            </div>

        </div>

    </div>

</body>


</html>