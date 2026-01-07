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

$report_id = (int) $_POST["penghulu_report_id"];
$status = $_POST["report_status"];
$feedback = mysqli_real_escape_string($conn, $_POST["feedback"]);

$sql = "
    UPDATE penghulu_report
    SET report_status = '$status',
        report_feedback = '$feedback'
    WHERE penghulu_report_id = '$report_id'
";

mysqli_query($conn, $sql);

header("Location: pejabatdaerah_penghulu_report_list.php?approved=1");

exit();
