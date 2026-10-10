<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin", "doctor"]);

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$appointment = fetch_one($conn, "SELECT doctor_id FROM appointments WHERE appointment_id = ?", [$id]);

if (!$appointment) {
    set_message("Appointment not found.", "danger");
    header("Location: index.php");
    exit;
}

if (is_doctor() && $appointment["doctor_id"] != $current_user["doctor_id"]) {
    set_message("That appointment is not yours.", "danger");
    header("Location: index.php");
    exit;
}

run_query($conn, "UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ?", [$id]);

set_message("Appointment cancelled.", "warning");
header("Location: view.php?id=" . $id);
exit;
?>
