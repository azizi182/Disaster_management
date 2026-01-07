<?php
session_start();
include '../../dbconnect.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ketuakampung') {
    header('Location: ../login.php');
    exit();
}

// 2. Get Data from POST
$report_id = (int) $_POST['report_id'];
$status = $_POST['report_status'];
$raw_feedback = $_POST['feedback'];


// 3. --- VALIDATION LOGIC ---
// Pattern: Alphanumeric, spaces, commas, dots, dashes. 3-100 characters.
$pattern = "/^[a-zA-Z0-9 ,.-]{3,100}$/";

if (!preg_match($pattern, $raw_feedback)) {
    // Validation Failed!
    $error_msg = "Feedback format is invalid.";
    
    // Redirect back to the list page with the error message
    header("Location: ketua_report_list.php?error=" . urlencode($error_msg));
    exit(); // Stop script here
}

// 4. Update Database (Only runs if validation passed)
$feedback = mysqli_real_escape_string($conn, $raw_feedback); // Sanitize for SQL
$safe_status = mysqli_real_escape_string($conn, $status);

$sql = "
    UPDATE villager_report
    SET report_status = '$status',
        report_feedback = '$feedback'
    WHERE report_id = '$report_id'
";
if (mysqli_query($conn, $sql)) {
    // Success
    header("Location: ketua_report_list.php?success=1");
} else {
    // Database Error
    $db_error = mysqli_error($conn);
    header("Location: ketua_report_list.php?error=" . urlencode($db_error));
}

exit();
?>