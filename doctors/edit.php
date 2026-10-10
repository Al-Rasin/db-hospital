<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin"]);

$page_title = "Edit doctor";
$page_subheading = "Update the doctor record";
$active = "doctors";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$data = fetch_one($conn, "SELECT * FROM doctors WHERE doctor_id = ?", [$id]);

if (!$data) {
    set_message("Doctor not found.", "danger");
    header("Location: index.php");
    exit;
}

$departments = fetch_all($conn, "SELECT * FROM departments ORDER BY name");
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data["name"]           = trim($_POST["name"]);
    $data["specialization"] = trim($_POST["specialization"]);
    $data["phone"]          = trim($_POST["phone"]);
    $data["room_number"]    = trim($_POST["room_number"]);
    $data["fee"]            = trim($_POST["fee"]);
    $data["dept_id"]        = trim($_POST["dept_id"]);

    if ($data["name"] == "") {
        $errors[] = "Name is required.";
    }
    if ($data["specialization"] == "") {
        $errors[] = "Specialization is required.";
    }
    if (!is_numeric($data["fee"])) {
        $errors[] = "Fee must be a number.";
    }

    if (!$errors) {
        $dept = $data["dept_id"] == "" ? null : $data["dept_id"];
        run_query($conn,
            "UPDATE doctors SET name = ?, specialization = ?, phone = ?, room_number = ?, fee = ?, dept_id = ?
             WHERE doctor_id = ?",
            [$data["name"], $data["specialization"], $data["phone"], $data["room_number"], $data["fee"], $dept, $id]);
        set_message("Doctor updated.");
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
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update doctor</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
