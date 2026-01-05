<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'penghulu') {
    header('Location: ../login.php');
    exit();
}

$report_id = (int) $_GET['kt_report_id'];
$user_name = $_SESSION['user_name']; 

$feedback = "Report rejected by $user_name ( Penghulu )";

$sql = "
    UPDATE ketua_report
    SET 
        report_status = 'Rejected',
        report_feedback = '$feedback'
    WHERE kt_report_id = '$report_id'
";

mysqli_query($conn, $sql);

header("Location: penghulu_ketua_report_list.php?rejected=1");
exit();
