<?php
// Every protected page includes this file first.
// It starts the session, loads the database and blocks visitors who are not logged in.

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/functions.php";

// BASE is the path back to the project root, so links work from any sub folder.
$in_root = realpath(dirname($_SERVER["SCRIPT_FILENAME"])) === realpath(dirname(__DIR__));
define("BASE", $in_root ? "." : "..");

if (empty($_SESSION["user_id"])) {
    header("Location: " . BASE . "/login.php");
    exit;
}

$current_user = [
    "user_id"    => $_SESSION["user_id"],
    "username"   => $_SESSION["username"],
    "full_name"  => $_SESSION["full_name"],
    "role"       => $_SESSION["role"],
    "doctor_id"  => $_SESSION["doctor_id"],
    "patient_id" => $_SESSION["patient_id"],
];

function is_admin()
{
    return $_SESSION["role"] == "admin";
}

function is_doctor()
{
    return $_SESSION["role"] == "doctor";
}

function is_patient()
{
    return $_SESSION["role"] == "patient";
}

// Stop the page if the logged in user does not have one of the given roles.
function require_role($roles)
{
    if (!in_array($_SESSION["role"], $roles)) {
        set_message("You are not allowed to open that page.", "danger");
        header("Location: " . BASE . "/index.php");
        exit;
    }
}
?>
