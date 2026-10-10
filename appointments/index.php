<?php
require_once __DIR__ . "/../includes/auth.php";

$page_title = "Appointments";
$active = "appointments";

$status = isset($_GET["status"]) ? $_GET["status"] : "";
$allowed = ["pending", "done", "cancelled"];

$sql = "SELECT * FROM appointment_details WHERE 1 = 1";
$params = [];

// A doctor sees only his own appointments, a patient only his own.
if (is_doctor()) {
    $sql .= " AND doctor_id = ?";
    $params[] = $current_user["doctor_id"];
} elseif (is_patient()) {
    $sql .= " AND patient_id = ?";
    $params[] = $current_user["patient_id"];
}

if (in_array($status, $allowed)) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY scheduled_for DESC";
$appointments = fetch_all($conn, $sql, $params);

$page_subheading = count($appointments) . " appointment(s)" . ($status ? " with status " . $status : "");
require_once __DIR__ . "/../includes/header.php";
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div class="btn-group btn-group-sm" role="group">
    <a href="index.php" class="btn <?php echo $status == "" ? "btn-primary" : "btn-outline-secondary"; ?>">All</a>
    <a href="?status=pending" class="btn <?php echo $status == "pending" ? "btn-primary" : "btn-outline-secondary"; ?>">Pending</a>
    <a href="?status=done" class="btn <?php echo $status == "done" ? "btn-primary" : "btn-outline-secondary"; ?>">Done</a>
    <a href="?status=cancelled" class="btn <?php echo $status == "cancelled" ? "btn-primary" : "btn-outline-secondary"; ?>">Cancelled</a>
  </div>
  <?php if (!is_patient()) { ?>
  <a href="add.php" class="btn btn-primary"><i class="bi bi-calendar-plus me-1"></i>Book appointment</a>
  <?php } ?>
</div>

<div class="table-card">
  <table class="table">
    <thead>
      <tr><th>Patient</th><th>Doctor</th><th>When</th><th>Reason</th><th>Status</th><th class="text-end">Action</th></tr>
    </thead>
    <tbody>
      <?php if ($appointments) { foreach ($appointments as $a) { ?>
      <tr>
        <td class="fw-medium"><?php echo e($a["patient_name"]); ?></td>
        <td>
          Dr. <?php echo e($a["doctor_name"]); ?>
          <div class="text-muted small"><?php echo e($a["specialization"]); ?></div>
        </td>
        <td>
          <?php echo show_date($a["scheduled_for"], "d M Y"); ?>
          <div class="text-muted small"><?php echo show_date($a["scheduled_for"], "h:i A"); ?></div>
        </td>
        <td><?php echo $a["reason"] ? e($a["reason"]) : "-"; ?></td>
        <td><?php echo status_badge($a["status"]); ?></td>
        <td class="text-end">
          <a href="view.php?id=<?php echo $a["appointment_id"]; ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-folder2-open me-1"></i>Open
          </a>
        </td>
      </tr>
      <?php } } else { ?>
      <tr><td colspan="6"><div class="empty-state"><i class="bi bi-calendar-x"></i>No appointments found.</div></td></tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
