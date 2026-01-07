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
$feedback = "Report rejected by $user_name (Penghulu)";

$sql = "
    UPDATE penghulu_report
    SET report_status = 'Rejected',
        report_feedback = '$feedback'
    WHERE penghulu_report_id = '$report_id'
";

mysqli_query($conn, $sql);

header("Location: pejabatdaerah_penghulu_report_list.php?rejected=1");

exit();
