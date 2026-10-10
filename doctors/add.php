<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin"]);

$page_title = "Add doctor";
$page_subheading = "Register a doctor on the hospital staff";
$active = "doctors";

$departments = fetch_all($conn, "SELECT * FROM departments ORDER BY name");
$errors = [];
$data = ["name" => "", "specialization" => "", "phone" => "", "room_number" => "", "fee" => "500", "dept_id" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($data as $key => $value) {
        $data[$key] = trim($_POST[$key]);
    }

    if ($data["name"] == "") {
        $errors[] = "Name is required.";
    }
    if ($data["specialization"] == "") {
        $errors[] = "Specialization is required.";
    }
    if ($data["phone"] == "") {
        $errors[] = "Phone is required.";
    }
    if (!is_numeric($data["fee"])) {
        $errors[] = "Fee must be a number.";
    }

    if (!$errors) {
        $dept = $data["dept_id"] == "" ? null : $data["dept_id"];
        run_query($conn,
            "INSERT INTO doctors (name, specialization, phone, room_number, fee, dept_id) VALUES (?, ?, ?, ?, ?, ?)",
            [$data["name"], $data["specialization"], $data["phone"], $data["room_number"], $data["fee"], $dept]);
        set_message("Doctor added.");
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
            <label class="form-label">Specialization <span class="text-danger">*</span></label>
            <input type="text" name="specialization" class="form-control" value="<?php echo e($data["specialization"]); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Department</label>
            <select name="dept_id" class="form-select">
              <option value="">Not assigned</option>
              <?php foreach ($departments as $dp) { ?>
                <option value="<?php echo $dp["dept_id"]; ?>" <?php echo $data["dept_id"] == $dp["dept_id"] ? "selected" : ""; ?>>
                  <?php echo e($dp["name"]); ?>
                </option>
              <?php } ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone <span class="text-danger">*</span></label>
            <input type="text" name="phone" class="form-control" value="<?php echo e($data["phone"]); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Room number</label>
            <input type="text" name="room_number" class="form-control" value="<?php echo e($data["room_number"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Consultation fee</label>
            <input type="number" step="0.01" name="fee" class="form-control" value="<?php echo e($data["fee"]); ?>">
          </div>
          <div class="d-flex gap-2 pt-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save doctor</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
