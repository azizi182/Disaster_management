<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ketuakampung') {
    header("Location: ../login.php");
    exit();
}

$sos_id = $_GET['sos_id'];
$ketua_id = $_SESSION['user_id'];

$sql = "
UPDATE sos_villager
SET sos_status = 'Resolved',
    ketua_id = '$ketua_id'
WHERE sos_id = '$sos_id'
";

mysqli_query($conn, $sql);

header("Location: ketua_report_list.php");
exit();
