<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin"]);

$page_title = "Edit department";
$page_subheading = "Update the department";
$active = "departments";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$data = fetch_one($conn, "SELECT * FROM departments WHERE dept_id = ?", [$id]);

if (!$data) {
    set_message("Department not found.", "danger");
    header("Location: index.php");
    exit;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data["name"]        = trim($_POST["name"]);
    $data["description"] = trim($_POST["description"]);

    if ($data["name"] == "") {
        $errors[] = "Name is required.";
    }

    if (!$errors) {
        run_query($conn, "UPDATE departments SET name = ?, description = ? WHERE dept_id = ?",
            [$data["name"], $data["description"], $id]);
        set_message("Department updated.");
        header("Location: index.php");
        exit;
    }
}

require_once __DIR__ . "/../includes/header.php";
?>

<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <?php if ($errors) { ?>
          <div class="alert alert-danger py-2 small">
            <?php foreach ($errors as $err) { echo "<div>" . e($err) . "</div>"; } ?>
          </div>
        <?php } ?>

        <form method="post">
          <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="<?php echo e($data["name"]); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="2" class="form-control"><?php echo e($data["description"]); ?></textarea>
          </div>
          <div class="d-flex gap-2 pt-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update department</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
