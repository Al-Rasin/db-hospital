<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin", "doctor"]);

$page_title = "Reschedule appointment";
$page_subheading = "Change the doctor, date or reason";
$active = "appointments";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$data = fetch_one($conn, "SELECT * FROM appointments WHERE appointment_id = ?", [$id]);

if (!$data) {
    set_message("Appointment not found.", "danger");
    header("Location: index.php");
    exit;
}

$doctors = fetch_all($conn, "SELECT doctor_id, name, specialization FROM doctors ORDER BY name");
$errors = [];
$when_value = date("Y-m-d\TH:i", strtotime($data["scheduled_for"]));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data["doctor_id"] = trim($_POST["doctor_id"]);
    $when_value        = trim($_POST["scheduled_for"]);
    $data["reason"]    = trim($_POST["reason"]);
    $data["status"]    = trim($_POST["status"]);

    if ($when_value == "") {
        $errors[] = "Select date and time.";
    }

    if (!$errors) {
        $when = str_replace("T", " ", $when_value) . ":00";
        $clash = fetch_one($conn,
            "SELECT appointment_id FROM appointments
             WHERE doctor_id = ? AND scheduled_for = ? AND status <> 'cancelled' AND appointment_id <> ?",
            [$data["doctor_id"], $when, $id]);
        if ($clash) {
            $errors[] = "This doctor already has an appointment at that time.";
        } else {
            run_query($conn,
                "UPDATE appointments SET doctor_id = ?, scheduled_for = ?, reason = ?, status = ?
                 WHERE appointment_id = ?",
                [$data["doctor_id"], $when, $data["reason"], $data["status"], $id]);
            set_message("Appointment updated.");
            header("Location: view.php?id=" . $id);
            exit;
        }
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
            <label class="form-label">Doctor <span class="text-danger">*</span></label>
            <select name="doctor_id" class="form-select" required>
              <?php foreach ($doctors as $d) { ?>
                <option value="<?php echo $d["doctor_id"]; ?>" <?php echo $data["doctor_id"] == $d["doctor_id"] ? "selected" : ""; ?>>
                  Dr. <?php echo e($d["name"]) . " - " . e($d["specialization"]); ?>
                </option>
              <?php } ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Date and time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="scheduled_for" class="form-control" value="<?php echo e($when_value); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Reason</label>
            <input type="text" name="reason" class="form-control" value="<?php echo e($data["reason"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <?php foreach (["pending", "done", "cancelled"] as $s) { ?>
                <option value="<?php echo $s; ?>" <?php echo $data["status"] == $s ? "selected" : ""; ?>><?php echo ucfirst($s); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="d-flex gap-2 pt-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save changes</button>
            <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
