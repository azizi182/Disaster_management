<?php
session_start();
include "../../dbconnect.php";

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["user_role"] !== "pejabatdaerah"
) {
    header("Location: ../login.php");
    exit();
}

$report_id = (int) $_GET["penghulu_report_id"];

$sql = "
    DELETE FROM penghulu_report
    WHERE penghulu_report_id = '$report_id'
";
mysqli_query($conn, $sql);

header("Location: pejabatdaerah_penghulu_report_list.php");

exit();
