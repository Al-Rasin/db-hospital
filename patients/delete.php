<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin"]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}
$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

// The foreign keys use ON DELETE CASCADE, so appointments of this patient go too.
run_query($conn, "DELETE FROM patients WHERE patient_id = ?", [$id]);

set_message("Patient deleted.");
header("Location: index.php");
exit;
?>
