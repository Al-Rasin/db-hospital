<?php
// Top part of every page. Set $page_title, $page_heading and $page_subheading before including it.
$page_title = isset($page_title) ? $page_title : "Dashboard";
$page_heading = isset($page_heading) ? $page_heading : $page_title;
$page_subheading = isset($page_subheading) ? $page_subheading : "";
$active = isset($active) ? $active : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e($page_title); ?> - Hospital MS</title>
  <link rel="stylesheet" href="<?php echo BASE; ?>/assets/vendor/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo BASE; ?>/assets/vendor/bootstrap-icons.css">
  <link rel="stylesheet" href="<?php echo BASE; ?>/assets/css/style.css">
</head>
<body>

<div class="hms-shell">

  <aside class="hms-sidebar">
    <a class="hms-brand" href="<?php echo BASE; ?>/index.php">
      <i class="bi bi-hospital"></i><span>Hospital MS</span>
    </a>
    <nav class="hms-nav">
      <a href="<?php echo BASE; ?>/index.php" class="<?php echo $active == 'dashboard' ? 'active' : ''; ?>">
        <i class="bi bi-speedometer2"></i><span>Dashboard</span>
      </a>
      <?php if (!is_patient()) { ?>
      <a href="<?php echo BASE; ?>/patients/index.php" class="<?php echo $active == 'patients' ? 'active' : ''; ?>">
        <i class="bi bi-people"></i><span>Patients</span>
      </a>
      <?php } ?>
      <a href="<?php echo BASE; ?>/doctors/index.php" class="<?php echo $active == 'doctors' ? 'active' : ''; ?>">
        <i class="bi bi-person-badge"></i><span>Doctors</span>
      </a>
      <?php if (is_admin()) { ?>
      <a href="<?php echo BASE; ?>/departments/index.php" class="<?php echo $active == 'departments' ? 'active' : ''; ?>">
        <i class="bi bi-diagram-3"></i><span>Departments</span>
      </a>
      <?php } ?>
      <a href="<?php echo BASE; ?>/appointments/index.php" class="<?php echo $active == 'appointments' ? 'active' : ''; ?>">
        <i class="bi bi-calendar-check"></i><span>Appointments</span>
      </a>
      <a href="<?php echo BASE; ?>/bills/index.php" class="<?php echo $active == 'bills' ? 'active' : ''; ?>">
        <i class="bi bi-receipt"></i><span>Bills</span>
      </a>
      <?php if (is_admin()) { ?>
      <a href="<?php echo BASE; ?>/reports/index.php" class="<?php echo $active == 'reports' ? 'active' : ''; ?>">
        <i class="bi bi-bar-chart"></i><span>Reports</span>
      </a>
      <?php } ?>
    </nav>
  </aside>

  <div class="hms-main">

    <header class="hms-topbar">
      <div>
        <h1 class="page-title"><?php echo e($page_heading); ?></h1>
        <p class="page-subtitle"><?php echo e($page_subheading); ?></p>
      </div>
      <div class="hms-user">
        <div class="hms-avatar"><?php echo e(strtoupper(substr($current_user["full_name"], 0, 1))); ?></div>
        <span class="d-none d-sm-inline">
          <?php echo e($current_user["full_name"]); ?>
          <small class="text-muted">(<?php echo e($current_user["role"]); ?>)</small>
        </span>
        <a class="btn btn-sm btn-outline-secondary ms-2" href="<?php echo BASE; ?>/logout.php">
          <i class="bi bi-box-arrow-right"></i> Log out
        </a>
      </div>
    </header>

    <main class="hms-content">
      <?php show_message(); ?>
