<?php
require_once __DIR__ . "/../includes/auth.php";

$active = "bills";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$bill = fetch_one($conn,
    "SELECT b.*, a.appointment_id, a.scheduled_for, a.patient_id, a.doctor_id, a.reason,
            p.name AS patient_name, p.phone AS patient_phone,
            d.name AS doctor_name, d.specialization
     FROM bills b
     JOIN appointments a ON a.appointment_id = b.appointment_id
     JOIN patients p ON p.patient_id = a.patient_id
     JOIN doctors  d ON d.doctor_id  = a.doctor_id
     WHERE b.bill_id = ?", [$id]);

if (!$bill) {
    set_message("Bill not found.", "danger");
    header("Location: index.php");
    exit;
}

if (is_doctor() && $bill["doctor_id"] != $current_user["doctor_id"]) {
    set_message("That bill is not yours.", "danger");
    header("Location: index.php");
    exit;
}
if (is_patient() && $bill["patient_id"] != $current_user["patient_id"]) {
    set_message("That bill is not yours.", "danger");
    header("Location: index.php");
    exit;
}

// Marking a bill as paid.
if ($_SERVER["REQUEST_METHOD"] == "POST" && is_admin()) {
    run_query($conn, "UPDATE bills SET status = 'paid' WHERE bill_id = ?", [$id]);
    set_message("Bill marked as paid.");
    header("Location: view.php?id=" . $id);
    exit;
}

$page_title = "Bill #" . $bill["bill_id"];
$page_subheading = "Payment details of the visit";
require_once __DIR__ . "/../includes/header.php";
?>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <h6 class="text-uppercase text-muted small mb-0">Bill #<?php echo $bill["bill_id"]; ?></h6>
          <span class="badge rounded-pill text-bg-<?php echo $bill["status"] == "paid" ? "success" : "warning"; ?>">
            <?php echo ucfirst($bill["status"]); ?>
          </span>
        </div>

        <table class="table detail-table table-borderless">
          <tr><th>Patient</th><td><?php echo e($bill["patient_name"]); ?> (<?php echo e($bill["patient_phone"]); ?>)</td></tr>
          <tr><th>Doctor</th><td>Dr. <?php echo e($bill["doctor_name"]); ?> - <?php echo e($bill["specialization"]); ?></td></tr>
          <tr><th>Visit</th><td><?php echo show_date($bill["scheduled_for"]); ?></td></tr>
          <tr><th>Issued</th><td><?php echo show_date($bill["created_at"]); ?></td></tr>
        </table>

        <table class="table">
          <thead><tr><th>Item</th><th class="text-end">Amount</th></tr></thead>
          <tbody>
            <tr><td>Consultation</td><td class="text-end"><?php echo money($bill["consultation_fee"]); ?></td></tr>
            <tr><td>Tests</td><td class="text-end"><?php echo money($bill["test_fee"]); ?></td></tr>
            <tr><td>Medicine</td><td class="text-end"><?php echo money($bill["medicine_fee"]); ?></td></tr>
            <tr class="fw-bold"><td>Total</td><td class="text-end"><?php echo money($bill["total_amount"]); ?></td></tr>
          </tbody>
        </table>

        <div class="d-flex gap-2">
          <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
          <a href="../appointments/view.php?id=<?php echo $bill["appointment_id"]; ?>" class="btn btn-outline-primary">
            <i class="bi bi-folder2-open me-1"></i>Open visit
          </a>
          <?php if (is_admin() && $bill["status"] == "unpaid") { ?>
          <form method="post" class="d-inline">
            <button type="submit" class="btn btn-success"><i class="bi bi-cash me-1"></i>Mark as paid</button>
          </form>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
