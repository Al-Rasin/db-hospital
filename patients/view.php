<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin", "doctor"]);

$active = "patients";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$patient = fetch_one($conn, "SELECT * FROM patients WHERE patient_id = ?", [$id]);

if (!$patient) {
    set_message("Patient not found.", "danger");
    header("Location: index.php");
    exit;
}

$history = fetch_all($conn,
    "SELECT a.appointment_id, a.scheduled_for, a.status,
            d.name AS doctor_name, d.specialization,
            b.bill_id, b.total_amount, b.status AS bill_status
     FROM appointments a
     JOIN doctors d ON d.doctor_id = a.doctor_id
     LEFT JOIN bills b ON b.appointment_id = a.appointment_id
     WHERE a.patient_id = ?
     ORDER BY a.scheduled_for DESC", [$id]);

$page_title = $patient["name"];
$page_subheading = "Patient record and visit history";
require_once __DIR__ . "/../includes/header.php";
?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="text-uppercase text-muted small mb-3">Details</h6>
        <table class="table detail-table table-borderless mb-0">
          <tr><th>Age</th><td><?php echo (int) $patient["age"]; ?> years</td></tr>
          <tr><th>Gender</th><td><?php echo e($patient["gender"]); ?></td></tr>
          <tr><th>Phone</th><td><?php echo e($patient["phone"]); ?></td></tr>
          <tr><th>Address</th><td><?php echo $patient["address"] ? e($patient["address"]) : "-"; ?></td></tr>
          <tr><th>Registered</th><td><?php echo show_date($patient["created_at"], "d M Y"); ?></td></tr>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="table-card h-100">
      <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
        <strong class="small text-uppercase text-muted">Appointment history</strong>
        <a href="../appointments/add.php?patient_id=<?php echo $id; ?>" class="btn btn-sm btn-outline-primary">
          <i class="bi bi-calendar-plus me-1"></i>Book
        </a>
      </div>
      <table class="table">
        <thead><tr><th>Doctor</th><th>When</th><th>Status</th><th>Bill</th></tr></thead>
        <tbody>
          <?php if ($history) { foreach ($history as $a) { ?>
          <tr>
            <td>
              <a href="../appointments/view.php?id=<?php echo $a["appointment_id"]; ?>" class="text-decoration-none fw-medium">
                Dr. <?php echo e($a["doctor_name"]); ?>
              </a>
              <div class="text-muted small"><?php echo e($a["specialization"]); ?></div>
            </td>
            <td><?php echo show_date($a["scheduled_for"]); ?></td>
            <td><?php echo status_badge($a["status"]); ?></td>
            <td>
              <?php if ($a["bill_id"]) { ?>
                <a href="../bills/view.php?id=<?php echo $a["bill_id"]; ?>" class="text-decoration-none">
                  <?php echo money($a["total_amount"]); ?>
                </a>
                <span class="badge text-bg-<?php echo $a["bill_status"] == "paid" ? "success" : "warning"; ?> ms-1">
                  <?php echo ucfirst($a["bill_status"]); ?>
                </span>
              <?php } else { ?>
                <span class="text-muted small">—</span>
              <?php } ?>
            </td>
          </tr>
          <?php } } else { ?>
          <tr><td colspan="4"><div class="empty-state"><i class="bi bi-calendar-x"></i>No visits recorded.</div></td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<a href="index.php" class="btn btn-outline-secondary mt-3"><i class="bi bi-arrow-left me-1"></i>Back to patients</a>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
