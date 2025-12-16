<?php
session_start();
include '../../dbconnect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ketuakampung') {
    header('Location: ../login.php');
    exit();
}

$report_id = (int) $_GET['report_id'];

$sql = "
    DELETE FROM villager_report
    WHERE report_id = '$report_id'
";

mysqli_query($conn, $sql);

header("Location: ketua_report_list.php");
exit();
