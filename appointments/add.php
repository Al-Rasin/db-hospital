<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin", "doctor"]);

$page_title = "Book appointment";
$page_subheading = "Assign a patient to a doctor at a given time";
$active = "appointments";

$patients = fetch_all($conn, "SELECT patient_id, name, phone FROM patients ORDER BY name");
$doctors  = fetch_all($conn, "SELECT doctor_id, name, specialization FROM doctors ORDER BY name");

$errors = [];
$data = [
    "patient_id"    => isset($_GET["patient_id"]) ? (int) $_GET["patient_id"] : "",
    "doctor_id"     => is_doctor() ? $current_user["doctor_id"] : "",
    "scheduled_for" => "",
    "reason"        => "",
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data["patient_id"]    = trim($_POST["patient_id"]);
    $data["doctor_id"]     = trim($_POST["doctor_id"]);
    $data["scheduled_for"] = trim($_POST["scheduled_for"]);
    $data["reason"]        = trim($_POST["reason"]);

    if ($data["patient_id"] == "") {
        $errors[] = "Select a patient.";
    }
    if ($data["doctor_id"] == "") {
        $errors[] = "Select a doctor.";
    }
    if (is_doctor() && $data["doctor_id"] != $current_user["doctor_id"]) {
        $errors[] = "You can only book appointments for yourself.";
    }
    if ($data["scheduled_for"] == "") {
        $errors[] = "Select date and time.";
    }

    // The same doctor cannot have two appointments at the same moment.
    if (!$errors) {
        $clash = fetch_one($conn,
            "SELECT appointment_id FROM appointments
             WHERE doctor_id = ? AND scheduled_for = ? AND status <> 'cancelled'",
            [$data["doctor_id"], str_replace("T", " ", $data["scheduled_for"]) . ":00"]);
        if ($clash) {
            $errors[] = "This doctor already has an appointment at that time.";
        }
    }

    if (!$errors) {
        $when = str_replace("T", " ", $data["scheduled_for"]) . ":00";
        run_query($conn,
            "INSERT INTO appointments (patient_id, doctor_id, scheduled_for, reason) VALUES (?, ?, ?, ?)",
            [$data["patient_id"], $data["doctor_id"], $when, $data["reason"]]);
        set_message("Appointment booked.");
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
            <label class="form-label">Patient <span class="text-danger">*</span></label>
            <select name="patient_id" class="form-select" required>
              <option value="">Select patient</option>
              <?php foreach ($patients as $p) { ?>
                <option value="<?php echo $p["patient_id"]; ?>" <?php echo $data["patient_id"] == $p["patient_id"] ? "selected" : ""; ?>>
                  <?php echo e($p["name"]) . " (" . e($p["phone"]) . ")"; ?>
                </option>
              <?php } ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Doctor <span class="text-danger">*</span></label>
            <select name="doctor_id" class="form-select" required <?php echo is_doctor() ? "disabled" : ""; ?>>
              <option value="">Select doctor</option>
              <?php foreach ($doctors as $d) { ?>
                <option value="<?php echo $d["doctor_id"]; ?>" <?php echo $data["doctor_id"] == $d["doctor_id"] ? "selected" : ""; ?>>
                  Dr. <?php echo e($d["name"]) . " - " . e($d["specialization"]); ?>
                </option>
              <?php } ?>
            </select>
            <?php if (is_doctor()) { ?>
              <input type="hidden" name="doctor_id" value="<?php echo (int) $current_user["doctor_id"]; ?>">
              <div class="form-text">Appointments you book are for yourself.</div>
            <?php } ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Date and time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="scheduled_for" class="form-control"
                   value="<?php echo e($data["scheduled_for"]); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Reason</label>
            <input type="text" name="reason" class="form-control" value="<?php echo e($data["reason"]); ?>">
          </div>
          <div class="d-flex gap-2 pt-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-calendar-check me-1"></i>Book</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
