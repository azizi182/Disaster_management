<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'villager') {
    header('Location: ../login.php');
    exit();
}

$villager_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];
$role = $_SESSION['user_role'];

$sqlketua = "SELECT * FROM tbl_users WHERE user_role = 'ketuakampung' ";
$resultketua = mysqli_query($conn, $sqlketua);

//insert report to database 
if (isset($_POST['submitreport'])) {

    $title = $_POST['title'];
    $report_type = $_POST['report_type'];
    $description = $_POST['description'];
    $phone = $_POST['phone'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $ketua_id = $_POST['ketua_kampung'];
    $status = 'Pending';

    if (!ctype_digit($phone)) {
        header("Location: villager_dashboard.php?error=phone");
        exit();
    }

    $sqlinsertreport = "INSERT INTO `villager_report`(`villager_id`, `ketua_id`,  `report_title`, `report_type`, `report_desc`, `report_phone`, `report_date`, `report_location`, `report_status`) 
            VALUES ('$villager_id','$ketua_id', '$title', '$report_type', '$description', '$phone', '$date', '$location', '$status')";

    if (mysqli_query($conn, $sqlinsertreport)) {
        header("Location: villager_dashboard.php?success=1");
        exit();
    } else {
        echo "<script>alert('Error submitting report: " . mysqli_error($conn) . "');</script>";
    }
}

?>



<!DOCTYPE html>
<html lang="en">



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villager Dashboard</title>

    <link rel="stylesheet" href="../../css/style_villager_dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* reportformvillager */
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

        .reportformvillager {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .reportformvillager h2 {
            text-align: center;
            margin: 0 auto;
        }

        .reportformvillager label {
            display: block;
            margin-bottom: 5px;
        }

        .reportformvillager input,
        .reportformvillager select,
        .reportformvillager textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;

        }

        .reportformvillager .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .reportformvillager .btn:hover {
            background-color: #45a049;
        }

        .reportformvillager .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <!-- Sidebar / Drawer -->
        <div class="sidebar">
            <h2>Village </h2>
            <ul>
                <li><a href="villager_dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                <li><a href="villager_report_list.php"><i class="fa fa-flag"></i> Submit Report,Emergency / Complaint</a></li>
                <li><a href="#"><i class="fa fa-bell"></i> Alerts & Notifications</a></li>

                <li><a href="#"><i class="fa-solid fa-triangle-exclamation"></i> SOS</a></li>
                <li><a href="#"><i class="fa-solid fa-map-location-dot"></i> Incident Map</a></li>
                <li><a href="../../logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main content -->
        <div class="main">
            <!-- Header -->
            <div class="header">
                <h1>Welcome, <?php echo $username, $villager_id, $role; ?></h1>
            </div>

            <!-- Dashboard content -->
            <div class="content">
                <!-- Submit report -->
                <div class="card">
                    <h3>Submit Emergency/Complaint</h3>
                    <p>Report any incidents or complaints here.</p>
                    <button class="btn" onclick="openForm()">Submit Report</button></a>

                </div>

                <!-- Alerts / Notifications -->
                <div class="card">
                    <h3>Alerts & Notifications</h3>
                    <p>Receive system alerts and instructions.</p>
                    <button>View Alerts</button>
                </div>

                <!-- Map placeholder -->
                <div class="card">
                    <h3>Incident Map</h3>
                    <p>View location of incidents on the map.</p>
                    <div class="map-placeholder">Map area (Google Maps API later)</div>
                </div>

                <!-- SOS / Quick Emergency -->
                <div class="card sos-card">
                    <h3>SOS / Quick Emergency</h3>
                    <p>Send a quick SOS alert to authorities.</p>
                    <button class="sos-button">Send SOS</button>
                </div>
            </div>
        </div>

        <!-- reportform -->
        <div id="reportform">
            <form action="" method="POST" class="reportformvillager">

                <div class="form-card">
                    <span class="close" onclick="closeForm()">&times;</span>
                    <h2>Submit Report</h2>

                    <label>Report Title</label>
                    <input type="text" name="title" required>

                    <label>Report Type</label>
                    <select name="report_type">
                        <option>Road Damage</option>
                        <option>Flood</option>
                        <option>Power Failure</option>
                        <option>Other</option>
                    </select>

                    <label>Description</label>
                    <textarea type="text" name="description" rows="4" required></textarea>

                    <label>Phone</label>
                    <input type="tel"
                        name="phone"
                        placeholder="0123456789"
                        minlength="10"
                        maxlength="12"
                        pattern="[0-9]+"
                        oninput="allowOnlyNumbers(this)"
                        required>

                    <label>Date</label>
                    <input type="date" name="date" required>

                    <label>Location</label>
                    <input type="text" name="location" placeholder="GPS / Address">

                    <label>Ketua Kampung</label>
                    <select name="ketua_kampung" required>
                        <option value="">Select Ketua Kampung</option>

                        <?php while ($rowketua = mysqli_fetch_assoc($resultketua)): ?>
                            <option value="<?= htmlspecialchars($rowketua['user_id']) ?>">
                                <?= htmlspecialchars($rowketua['user_name']) ?>
                            </option>
                        <?php endwhile; ?>

                    </select>

                    <button class="btn" name="submitreport">Submit Report</button>
                </div>
            </form>

            <?php if (isset($_GET['success'])): ?>
                <script>
                    alert("Report submitted successfully!");
                </script>
            <?php endif; ?>





        </div>
    </div>

    </div>
</body>

<script>
    var reportform = document.getElementById("reportform");

    function openForm() {
        reportform.style.display = "flex";
    }

    function closeForm() {
        reportform.style.display = "none";
    }

    function allowOnlyNumbers(input) {
        input.value = input.value.replace(/[^0-9]/g, '');
    }
</script>

</html>