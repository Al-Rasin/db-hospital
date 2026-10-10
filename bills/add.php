<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin", "doctor"]);

$page_title = "Create bill";
$page_subheading = "Generate the bill of a visit";
$active = "bills";

$appointment_id = isset($_GET["appointment_id"]) ? (int) $_GET["appointment_id"] : 0;

$appointment = fetch_one($conn,
    "SELECT a.*, p.name AS patient_name, d.name AS doctor_name, d.fee
     FROM appointments a
     JOIN patients p ON p.patient_id = a.patient_id
     JOIN doctors  d ON d.doctor_id  = a.doctor_id
     WHERE a.appointment_id = ?", [$appointment_id]);

if (!$appointment) {
    set_message("Appointment not found.", "danger");
    header("Location: ../appointments/index.php");
    exit;
}

$old = fetch_one($conn, "SELECT bill_id FROM bills WHERE appointment_id = ?", [$appointment_id]);
if ($old) {
    set_message("This visit already has a bill.", "warning");
    header("Location: view.php?id=" . $old["bill_id"]);
    exit;
}

if ($appointment["status"] !== "done") {
    set_message("A bill can only be created after the appointment is done.", "warning");
    header("Location: ../appointments/view.php?id=" . $appointment_id);
    exit;
}

$errors = [];
$data = ["consultation_fee" => $appointment["fee"], "test_fee" => "0", "medicine_fee" => "0", "status" => "unpaid"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($data as $key => $value) {
        $data[$key] = trim($_POST[$key]);
    }

    foreach (["consultation_fee", "test_fee", "medicine_fee"] as $field) {
        if (!is_numeric($data[$field]) || $data[$field] < 0) {
            $errors[] = "All fees must be zero or a positive number.";
            break;
        }
    }

    if (!$errors) {
        run_query($conn,
            "INSERT INTO bills (appointment_id, consultation_fee, test_fee, medicine_fee, status)
             VALUES (?, ?, ?, ?, ?)",
            [$appointment_id, $data["consultation_fee"], $data["test_fee"], $data["medicine_fee"], $data["status"]]);
        set_message("Bill created.");
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
        <p class="text-muted small mb-3">
          Visit of <strong><?php echo e($appointment["patient_name"]); ?></strong>
          with Dr. <?php echo e($appointment["doctor_name"]); ?>
          on <?php echo show_date($appointment["scheduled_for"]); ?>.
        </p>

        <?php if ($errors) { ?>
          <div class="alert alert-danger py-2 small">
            <?php foreach ($errors as $err) { echo "<div>" . e($err) . "</div>"; } ?>
          </div>
        <?php } ?>

        <form method="post">
          <div class="mb-3">
            <label class="form-label">Consultation fee</label>
            <input type="number" step="0.01" name="consultation_fee" class="form-control" value="<?php echo e($data["consultation_fee"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Test fee</label>
            <input type="number" step="0.01" name="test_fee" class="form-control" value="<?php echo e($data["test_fee"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Medicine fee</label>
            <input type="number" step="0.01" name="medicine_fee" class="form-control" value="<?php echo e($data["medicine_fee"]); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="unpaid" <?php echo $data["status"] == "unpaid" ? "selected" : ""; ?>>Unpaid</option>
              <option value="paid" <?php echo $data["status"] == "paid" ? "selected" : ""; ?>>Paid</option>
            </select>
          </div>
          <div class="form-text mb-3">The total is calculated by the database from the three fees.</div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create bill</button>
            <a href="../appointments/view.php?id=<?php echo $appointment_id; ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
