<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin"]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}
$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

// doctors.dept_id uses ON DELETE SET NULL, so the doctors stay but lose the department.
run_query($conn, "DELETE FROM departments WHERE dept_id = ?", [$id]);

set_message("Department deleted.");
header("Location: index.php");
exit;
?>
