<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin"]);

$page_title = "Add department";
$page_subheading = "Create a new hospital department";
$active = "departments";

$errors = [];
$data = ["name" => "", "description" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data["name"]        = trim($_POST["name"]);
    $data["description"] = trim($_POST["description"]);

    if ($data["name"] == "") {
        $errors[] = "Name is required.";
    } else {
        $exists = fetch_one($conn, "SELECT dept_id FROM departments WHERE name = ?", [$data["name"]]);
        if ($exists) {
            $errors[] = "A department with this name already exists.";
        }
    }

    if (!$errors) {
        run_query($conn, "INSERT INTO departments (name, description) VALUES (?, ?)",
            [$data["name"], $data["description"]]);
        set_message("Department added.");
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
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save department</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
