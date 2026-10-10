<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin"]);

$page_title = "Edit patient";
$page_subheading = "Update the patient record";
$active = "patients";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$data = fetch_one($conn, "SELECT * FROM patients WHERE patient_id = ?", [$id]);

if (!$data) {
    set_message("Patient not found.", "danger");
    header("Location: index.php");
    exit;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data["name"]    = trim($_POST["name"]);
    $data["age"]     = trim($_POST["age"]);
    $data["gender"]  = trim($_POST["gender"]);
    $data["phone"]   = trim($_POST["phone"]);
    $data["address"] = trim($_POST["address"]);

    if ($data["name"] == "") {
        $errors[] = "Name is required.";
    }
    if ($data["age"] == "" || !is_numeric($data["age"])) {
        $errors[] = "Age must be a number.";
    }
    if ($data["phone"] == "") {
        $errors[] = "Phone is required.";
    }

    if (!$errors) {
        run_query($conn,
            "UPDATE patients SET name = ?, age = ?, gender = ?, phone = ?, address = ? WHERE patient_id = ?",
            [$data["name"], $data["age"], $data["gender"], $data["phone"], $data["address"], $id]);
        set_message("Patient updated.");
        header("Location: view.php?id=" . $id);
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
            <label class="form-label">Age <span class="text-danger">*</span></label>
            <input type="number" name="age" class="form-control" value="<?php echo e($data["age"]); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Gender <span class="text-danger">*</span></label>
            <select name="gender" class="form-select">
              <?php foreach (["Male", "Female", "Other"] as $g) { ?>
                <option value="<?php echo $g; ?>" <?php echo $data["gender"] == $g ? "selected" : ""; ?>><?php echo $g; ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone <span class="text-danger">*</span></label>
            <input type="text" name="phone" class="form-control" value="<?php echo e($data["phone"]); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" rows="2" class="form-control"><?php echo e($data["address"]); ?></textarea>
          </div>
          <div class="d-flex gap-2 pt-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update patient</button>
            <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
