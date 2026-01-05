<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'penghulu') {
    header('Location: ../login.php');
    exit();
}

$report_id = (int) $_GET['kt_report_id'];

$sql = "
    DELETE FROM ketua_report
    WHERE kt_report_id = '$report_id'
";

mysqli_query($conn, $sql);

header("Location: penghulu_ketua_report_list.php?deleted=1");
exit();
