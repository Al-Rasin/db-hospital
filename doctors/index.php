<?php
require_once __DIR__ . "/../includes/auth.php";

$page_title = "Doctors";
$active = "doctors";

$dept_id = isset($_GET["dept"]) ? (int) $_GET["dept"] : 0;

$sql = "SELECT d.*, dp.name AS dept_name
        FROM doctors d
        LEFT JOIN departments dp ON dp.dept_id = d.dept_id";
$params = [];
if ($dept_id) {
    $sql .= " WHERE d.dept_id = ?";
    $params[] = $dept_id;
}
$sql .= " ORDER BY d.name";

$doctors = fetch_all($conn, $sql, $params);
$departments = fetch_all($conn, "SELECT * FROM departments ORDER BY name");

$page_subheading = count($doctors) . " doctor(s) on staff";
require_once __DIR__ . "/../includes/header.php";
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <form method="get" class="d-flex gap-2">
    <select name="dept" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="0">All departments</option>
      <?php foreach ($departments as $dp) { ?>
        <option value="<?php echo $dp["dept_id"]; ?>" <?php echo $dept_id == $dp["dept_id"] ? "selected" : ""; ?>>
          <?php echo e($dp["name"]); ?>
        </option>
      <?php } ?>
    </select>
  </form>
  <?php if (is_admin()) { ?>
  <a href="add.php" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Add doctor</a>
  <?php } ?>
</div>

<div class="row g-3">
  <?php if ($doctors) { foreach ($doctors as $d) { ?>
  <div class="col-md-6 col-xl-4">
    <div class="card h-100">
      <div class="card-body d-flex gap-3">
        <div class="hms-avatar" style="width:44px;height:44px;font-size:1rem;flex-shrink:0;">
          <?php echo e(strtoupper(substr($d["name"], 0, 1))); ?>
        </div>
        <div class="min-w-0 flex-grow-1">
          <div class="fw-semibold">Dr. <?php echo e($d["name"]); ?></div>
          <div class="text-muted small mb-2"><?php echo e($d["specialization"]); ?></div>
          <div class="small"><i class="bi bi-diagram-3 text-muted me-1"></i><?php echo $d["dept_name"] ? e($d["dept_name"]) : "-"; ?></div>
          <div class="small"><i class="bi bi-telephone text-muted me-1"></i><?php echo e($d["phone"]); ?></div>
          <div class="small"><i class="bi bi-door-closed text-muted me-1"></i>Room <?php echo $d["room_number"] ? e($d["room_number"]) : "-"; ?></div>
          <div class="small"><i class="bi bi-cash text-muted me-1"></i>Fee <?php echo money($d["fee"]); ?></div>
          <?php if (is_admin()) { ?>
          <div class="mt-2">
            <a href="edit.php?id=<?php echo $d["doctor_id"]; ?>" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="post" action="delete.php" class="d-inline"
                  onsubmit="return confirm('Delete this doctor with all their appointments?');">
              <input type="hidden" name="id" value="<?php echo $d["doctor_id"]; ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <?php } } else { ?>
  <div class="col-12">
    <div class="table-card">
      <div class="empty-state"><i class="bi bi-person-badge"></i>No doctors found.</div>
    </div>
  </div>
  <?php } ?>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
