<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'penghulu') {
    header('Location: ../login.php');
    exit();
}

$report_id = (int) $_POST['kt_report_id'];
$status = $_POST['report_status'];
$feedback = mysqli_real_escape_string($conn, $_POST['feedback']);

$sql = "
    UPDATE ketua_report
    SET report_status = '$status',
        report_feedback = '$feedback'
    WHERE kt_report_id = '$report_id'
";

mysqli_query($conn, $sql);

header("Location: penghulu_ketua_report_list.php?approved=1");
exit();
