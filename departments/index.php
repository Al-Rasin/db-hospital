<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin"]);

$page_title = "Departments";
$active = "departments";

// Left join so a department with no doctor still shows with a zero count.
$departments = fetch_all($conn,
    "SELECT dp.*, COUNT(d.doctor_id) AS doctor_count
     FROM departments dp
     LEFT JOIN doctors d ON d.dept_id = dp.dept_id
     GROUP BY dp.dept_id, dp.name, dp.description
     ORDER BY dp.name");

$page_subheading = count($departments) . " department(s)";
require_once __DIR__ . "/../includes/header.php";
?>

<div class="d-flex justify-content-end mb-3">
  <a href="add.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add department</a>
</div>

<div class="table-card">
  <table class="table">
    <thead>
      <tr><th>Name</th><th>Description</th><th>Doctors</th><th class="text-end">Action</th></tr>
    </thead>
    <tbody>
      <?php if ($departments) { foreach ($departments as $dp) { ?>
      <tr>
        <td class="fw-medium"><?php echo e($dp["name"]); ?></td>
        <td><?php echo $dp["description"] ? e($dp["description"]) : "-"; ?></td>
        <td><span class="badge text-bg-light"><?php echo $dp["doctor_count"]; ?></span></td>
        <td class="text-end">
          <a href="edit.php?id=<?php echo $dp["dept_id"]; ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil"></i>
          </a>
          <form method="post" action="delete.php" class="d-inline"
                onsubmit="return confirm('Delete this department? Doctors will lose their department assignment.');">
            <input type="hidden" name="id" value="<?php echo $dp["dept_id"]; ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
        </td>
      </tr>
      <?php } } else { ?>
      <tr><td colspan="4"><div class="empty-state"><i class="bi bi-diagram-3"></i>No departments yet.</div></td></tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
