<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin"]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}
$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

run_query($conn, "DELETE FROM doctors WHERE doctor_id = ?", [$id]);

set_message("Doctor deleted.");
header("Location: index.php");
exit;
?>
