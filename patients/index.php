<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin", "doctor"]);

$page_title = "Patients";
$active = "patients";

$query = isset($_GET["q"]) ? trim($_GET["q"]) : "";

$bill_sql = "SELECT p.*,
        COUNT(CASE WHEN b.status = 'unpaid' THEN 1 END) AS unpaid_bills,
        IFNULL(SUM(CASE WHEN b.status = 'unpaid' THEN b.total_amount ELSE 0 END), 0) AS unpaid_total
     FROM patients p
     LEFT JOIN appointments a ON a.patient_id = p.patient_id
     LEFT JOIN bills b ON b.appointment_id = a.appointment_id";

if ($query != "") {
    $patients = fetch_all($conn,
        $bill_sql . " WHERE p.name LIKE ? OR p.phone LIKE ? GROUP BY p.patient_id ORDER BY p.name",
        ["%$query%", "%$query%"]);
} else {
    $patients = fetch_all($conn, $bill_sql . " GROUP BY p.patient_id ORDER BY p.name");
}

$page_subheading = count($patients) . " patient(s) found";
require_once __DIR__ . "/../includes/header.php";
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <form method="get" class="d-flex gap-2" style="max-width: 420px; flex: 1;">
    <div class="input-group">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" name="q" value="<?php echo e($query); ?>" class="form-control" placeholder="Search by name or phone">
      <?php if ($query != "") { ?>
        <a href="index.php" class="btn btn-outline-secondary" title="Clear"><i class="bi bi-x-lg"></i></a>
      <?php } ?>
    </div>
    <button class="btn btn-outline-primary">Search</button>
  </form>
  <?php if (is_admin()) { ?>
  <a href="add.php" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Register patient</a>
  <?php } ?>
</div>

<div class="table-card">
  <table class="table">
    <thead>
      <tr><th>Name</th><th>Age</th><th>Gender</th><th>Phone</th><th>Unpaid bills</th><th class="text-end">Action</th></tr>
    </thead>
    <tbody>
      <?php if ($patients) { foreach ($patients as $p) { ?>
      <tr>
        <td class="fw-medium"><?php echo e($p["name"]); ?></td>
        <td><?php echo (int) $p["age"]; ?></td>
        <td><?php echo e($p["gender"]); ?></td>
        <td><i class="bi bi-telephone text-muted me-1"></i><?php echo e($p["phone"]); ?></td>
        <td>
          <?php if ($p["unpaid_bills"] > 0) { ?>
            <span class="badge text-bg-warning"><?php echo $p["unpaid_bills"]; ?> unpaid</span>
            <div class="text-muted small"><?php echo money($p["unpaid_total"]); ?></div>
          <?php } else { ?>
            <span class="text-muted small">—</span>
          <?php } ?>
        </td>
        <td class="text-end">
          <a href="view.php?id=<?php echo $p["patient_id"]; ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-eye me-1"></i>View
          </a>
          <?php if (is_admin()) { ?>
          <a href="edit.php?id=<?php echo $p["patient_id"]; ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil"></i>
          </a>
          <form method="post" action="delete.php" class="d-inline"
                onsubmit="return confirm('Delete this patient with all their appointments?');">
            <input type="hidden" name="id" value="<?php echo $p["patient_id"]; ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
          <?php } ?>
        </td>
      </tr>
      <?php } } else { ?>
      <tr><td colspan="6">
        <div class="empty-state"><i class="bi bi-people"></i>No patient found.</div>
      </td></tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
