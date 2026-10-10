<?php
require_once __DIR__ . "/includes/auth.php";

$page_title = "Dashboard";
$page_subheading = "Overview of the hospital today";
$active = "dashboard";

// Counts shown in the four cards. Doctors and patients only see their own rows.
if (is_doctor()) {
    $doctor_id = $current_user["doctor_id"];
    $patient_count = count_rows($conn,
        "SELECT COUNT(DISTINCT patient_id) FROM appointments WHERE doctor_id = ?", [$doctor_id]);
    $pending_count = count_rows($conn,
        "SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'pending'", [$doctor_id]);
    $today_count = count_rows($conn,
        "SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND DATE(scheduled_for) = CURDATE()", [$doctor_id]);
    $recent = fetch_all($conn,
        "SELECT * FROM appointment_details WHERE doctor_id = ? ORDER BY scheduled_for DESC LIMIT 5", [$doctor_id]);
} elseif (is_patient()) {
    $patient_id = $current_user["patient_id"];
    $patient_count = count_rows($conn,
        "SELECT COUNT(*) FROM appointments WHERE patient_id = ?", [$patient_id]);
    $pending_count = count_rows($conn,
        "SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND status = 'pending'", [$patient_id]);
    $today_count = count_rows($conn,
        "SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND DATE(scheduled_for) = CURDATE()", [$patient_id]);
    $recent = fetch_all($conn,
        "SELECT * FROM appointment_details WHERE patient_id = ? ORDER BY scheduled_for DESC LIMIT 5", [$patient_id]);
} else {
    $patient_count = count_rows($conn, "SELECT COUNT(*) FROM patients");
    $pending_count = count_rows($conn, "SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
    $today_count = count_rows($conn, "SELECT COUNT(*) FROM appointments WHERE DATE(scheduled_for) = CURDATE()");
    $recent = fetch_all($conn, "SELECT * FROM appointment_details ORDER BY scheduled_for DESC LIMIT 5");
}

$doctor_count = count_rows($conn, "SELECT COUNT(*) FROM doctors");

require_once __DIR__ . "/includes/header.php";
?>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card stat-card h-100">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="stat-value"><?php echo $patient_count; ?></div>
          <div class="stat-label"><?php echo is_patient() ? "My visits" : "Patients"; ?></div>
        </div>
        <i class="bi bi-people stat-icon"></i>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card stat-card is-doctors h-100">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="stat-value"><?php echo $doctor_count; ?></div>
          <div class="stat-label">Doctors</div>
        </div>
        <i class="bi bi-person-badge stat-icon"></i>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card stat-card is-pending h-100">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="stat-value"><?php echo $pending_count; ?></div>
          <div class="stat-label">Pending appointments</div>
        </div>
        <i class="bi bi-hourglass-split stat-icon"></i>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card stat-card is-today h-100">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="stat-value"><?php echo $today_count; ?></div>
          <div class="stat-label">Scheduled today</div>
        </div>
        <i class="bi bi-calendar-day stat-icon"></i>
      </div>
    </div>
  </div>
</div>

<?php if (!is_patient()) { ?>
<div class="d-flex flex-wrap gap-2 mb-4">
  <?php if (is_admin()) { ?>
    <a href="patients/add.php" class="btn btn-primary btn-sm">
      <i class="bi bi-person-plus me-1"></i>Register patient
    </a>
    <a href="doctors/add.php" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-person-badge me-1"></i>Add doctor
    </a>
  <?php } ?>
  <a href="appointments/add.php" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-calendar-plus me-1"></i>Book appointment
  </a>
</div>
<?php } ?>

<div class="table-card">
  <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
    <strong class="small text-uppercase text-muted">Recent appointments</strong>
    <a href="appointments/index.php" class="small text-decoration-none">View all</a>
  </div>
  <table class="table">
    <thead>
      <tr><th>Patient</th><th>Doctor</th><th>When</th><th>Status</th></tr>
    </thead>
    <tbody>
      <?php if ($recent) { foreach ($recent as $a) { ?>
      <tr>
        <td>
          <a href="appointments/view.php?id=<?php echo $a["appointment_id"]; ?>" class="text-decoration-none fw-medium">
            <?php echo e($a["patient_name"]); ?>
          </a>
        </td>
        <td>Dr. <?php echo e($a["doctor_name"]); ?></td>
        <td><?php echo show_date($a["scheduled_for"]); ?></td>
        <td><?php echo status_badge($a["status"]); ?></td>
      </tr>
      <?php } } else { ?>
      <tr><td colspan="4">
        <div class="empty-state"><i class="bi bi-calendar-x"></i>No appointments booked yet.</div>
      </td></tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
