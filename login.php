<?php
session_start();
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/includes/functions.php";

if (!empty($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $user = fetch_one($conn,
        "SELECT * FROM users WHERE username = ? AND password = ?",
        [$username, $password]);

    if ($user) {
        $_SESSION["user_id"]    = $user["user_id"];
        $_SESSION["username"]   = $user["username"];
        $_SESSION["full_name"]  = $user["full_name"];
        $_SESSION["role"]       = $user["role"];
        $_SESSION["doctor_id"]  = $user["doctor_id"];
        $_SESSION["patient_id"] = $user["patient_id"];
        header("Location: index.php");
        exit;
    }
    $error = "Wrong username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Log in - Hospital MS</title>
  <link rel="stylesheet" href="assets/vendor/bootstrap.min.css">
  <link rel="stylesheet" href="assets/vendor/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">

<div class="card login-card shadow-lg">
  <div class="card-body p-4">
    <div class="login-logo"><i class="bi bi-hospital"></i></div>
    <h5 class="text-center mb-1">Hospital Management System</h5>
    <p class="text-center text-muted small mb-4">Sign in to continue</p>

    <?php if ($error) { ?>
      <div class="alert alert-danger py-2 small">
        <i class="bi bi-exclamation-triangle me-1"></i> <?php echo e($error); ?>
      </div>
    <?php } ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" class="form-control" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-box-arrow-in-right me-1"></i> Log in
      </button>
    </form>

    <hr class="my-4">
    <p class="text-muted small mb-1">Demo accounts</p>
    <ul class="text-muted small mb-0 ps-3">
      <li>admin / admin123</li>
      <li>karim / doctor123 (doctor)</li>
      <li>rakib / patient123 (patient)</li>
    </ul>
  </div>
</div>

</body>
</html>
