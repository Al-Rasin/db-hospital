<?php
// Database connection for XAMPP (MySQL).
// Default XAMPP user is "root" with an empty password.

// Report errors as return values instead of exceptions, so the checks below work.
mysqli_report(MYSQLI_REPORT_OFF);

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "hospital_db";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
