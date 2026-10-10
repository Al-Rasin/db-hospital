<?php
require_once __DIR__ . "/../includes/auth.php";

$page_title = "Bills";
$active = "bills";

$status = isset($_GET["status"]) ? $_GET["status"] : "";

$sql = "SELECT b.*, a.scheduled_for, p.name AS patient_name, d.name AS doctor_name
        FROM bills b
        JOIN appointments a ON a.appointment_id = b.appointment_id
        JOIN patients p ON p.patient_id = a.patient_id
        JOIN doctors  d ON d.doctor_id  = a.doctor_id
        WHERE 1 = 1";
$params = [];

if (is_doctor()) {
    $sql .= " AND a.doctor_id = ?";
    $params[] = $current_user["doctor_id"];
} elseif (is_patient()) {
    $sql .= " AND a.patient_id = ?";
    $params[] = $current_user["patient_id"];
}

if ($status == "paid" || $status == "unpaid") {
    $sql .= " AND b.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY b.created_at DESC";
$bills = fetch_all($conn, $sql, $params);

$total = 0;
$due = 0;
foreach ($bills as $b) {
    $total += $b["total_amount"];
    if ($b["status"] == "unpaid") {
        $due += $b["total_amount"];
    }
}

$page_subheading = count($bills) . " bill(s), " . money($due) . " unpaid";
require_once __DIR__ . "/../includes/header.php";
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div class="btn-group btn-group-sm" role="group">
    <a href="index.php" class="btn <?php echo $status == "" ? "btn-primary" : "btn-outline-secondary"; ?>">All</a>
    <a href="?status=unpaid" class="btn <?php echo $status == "unpaid" ? "btn-primary" : "btn-outline-secondary"; ?>">Unpaid</a>
    <a href="?status=paid" class="btn <?php echo $status == "paid" ? "btn-primary" : "btn-outline-secondary"; ?>">Paid</a>
  </div>
  <span class="text-muted small">Total billed: <strong><?php echo money($total); ?></strong></span>
</div>

<div class="table-card">
  <table class="table">
    <thead>
      <tr><th>Bill</th><th>Patient</th><th>Doctor</th><th>Visit date</th><th>Amount</th><th>Status</th><th class="text-end">Action</th></tr>
    </thead>
    <tbody>
      <?php if ($bills) { foreach ($bills as $b) { ?>
      <tr>
        <td class="fw-medium">#<?php echo $b["bill_id"]; ?></td>
        <td><?php echo e($b["patient_name"]); ?></td>
        <td>Dr. <?php echo e($b["doctor_name"]); ?></td>
        <td><?php echo show_date($b["scheduled_for"], "d M Y"); ?></td>
        <td><?php echo money($b["total_amount"]); ?></td>
        <td>
          <span class="badge rounded-pill text-bg-<?php echo $b["status"] == "paid" ? "success" : "warning"; ?>">
            <?php echo ucfirst($b["status"]); ?>
          </span>
        </td>
        <td class="text-end">
          <a href="view.php?id=<?php echo $b["bill_id"]; ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-receipt me-1"></i>Open
          </a>
        </td>
      </tr>
      <?php } } else { ?>
      <tr><td colspan="7"><div class="empty-state"><i class="bi bi-receipt"></i>No bills found.</div></td></tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
