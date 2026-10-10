<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin"]);

$page_title = "Register patient";
$page_subheading = "Add a new patient record";
$active = "patients";

$errors = [];
$data = ["name" => "", "age" => "", "gender" => "Male", "phone" => "", "address" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($data as $key => $value) {
        $data[$key] = trim($_POST[$key]);
    }

    if ($data["name"] == "") {
        $errors[] = "Name is required.";
    }
    if ($data["age"] == "" || !is_numeric($data["age"]) || $data["age"] < 0 || $data["age"] > 150) {
        $errors[] = "Age must be a number between 0 and 150.";
    }
    if ($data["phone"] == "") {
        $errors[] = "Phone is required.";
    }

    if (!$errors) {
        run_query($conn,
            "INSERT INTO patients (name, age, gender, phone, address) VALUES (?, ?, ?, ?, ?)",
            [$data["name"], $data["age"], $data["gender"], $data["phone"], $data["address"]]);
        set_message("Patient registered.");
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
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save patient</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
