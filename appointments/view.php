<?php
require_once __DIR__ . "/../includes/auth.php";

$active = "appointments";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$appointment = fetch_one($conn,
    "SELECT a.*, p.name AS patient_name, p.age, p.gender, p.phone AS patient_phone,
            d.name AS doctor_name, d.specialization, d.fee, d.room_number
     FROM appointments a
     JOIN patients p ON p.patient_id = a.patient_id
     JOIN doctors  d ON d.doctor_id  = a.doctor_id
     WHERE a.appointment_id = ?", [$id]);

if (!$appointment) {
    set_message("Appointment not found.", "danger");
    header("Location: index.php");
    exit;
}

// A doctor or patient may only open his own appointment.
if (is_doctor() && $appointment["doctor_id"] != $current_user["doctor_id"]) {
    set_message("That appointment is not yours.", "danger");
    header("Location: index.php");
    exit;
}
if (is_patient() && $appointment["patient_id"] != $current_user["patient_id"]) {
    set_message("That appointment is not yours.", "danger");
    header("Location: index.php");
    exit;
}

$prescription = fetch_one($conn, "SELECT * FROM prescriptions WHERE appointment_id = ?", [$id]);
$bill = fetch_one($conn, "SELECT * FROM bills WHERE appointment_id = ?", [$id]);

$can_write = is_admin() || is_doctor();
$errors = [];

// Saving a prescription. The database trigger marks the appointment as done.
if ($_SERVER["REQUEST_METHOD"] == "POST" && $can_write) {
    $diagnosis = trim($_POST["diagnosis"]);
    $medicines = trim($_POST["medicines"]);
    $notes     = trim($_POST["notes"]);

    if ($diagnosis == "") {
        $errors[] = "Diagnosis is required.";
    }
    if ($medicines == "") {
        $errors[] = "Write at least one medicine.";
    }

    if (!$errors) {
        if ($prescription) {
            run_query($conn,
                "UPDATE prescriptions SET diagnosis = ?, medicines = ?, notes = ? WHERE appointment_id = ?",
                [$diagnosis, $medicines, $notes, $id]);
            set_message("Prescription updated.");
        } else {
            run_query($conn,
                "INSERT INTO prescriptions (appointment_id, diagnosis, medicines, notes) VALUES (?, ?, ?, ?)",
                [$id, $diagnosis, $medicines, $notes]);
            set_message("Prescription saved. The appointment is now done.");
        }
        header("Location: view.php?id=" . $id);
        exit;
    }
}

$page_title = "Appointment";
$page_subheading = $appointment["patient_name"] . " with Dr. " . $appointment["doctor_name"];
require_once __DIR__ . "/../includes/header.php";
?>

<div class="row g-3">

  <div class="col-lg-5">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <h6 class="text-uppercase text-muted small mb-0">Visit details</h6>
          <?php echo status_badge($appointment["status"]); ?>
        </div>
        <table class="table detail-table table-borderless mb-0">
          <tr>
            <th>Patient</th>
            <td>
              <?php if (!is_patient()) { ?>
                <a href="../patients/view.php?id=<?php echo $appointment["patient_id"]; ?>" class="text-decoration-none fw-medium">
                  <?php echo e($appointment["patient_name"]); ?>
                </a>
              <?php } else { ?>
                <span class="fw-medium"><?php echo e($appointment["patient_name"]); ?></span>
              <?php } ?>
              <div class="text-muted small">
                <?php echo (int) $appointment["age"]; ?> yrs ·
                <?php echo e($appointment["gender"]); ?> ·
                <?php echo e($appointment["patient_phone"]); ?>
              </div>
            </td>
          </tr>
          <tr>
            <th>Doctor</th>
            <td>Dr. <?php echo e($appointment["doctor_name"]); ?>
              <div class="text-muted small">
                <?php echo e($appointment["specialization"]); ?> · Room <?php echo e($appointment["room_number"]); ?>
              </div>
            </td>
          </tr>
          <tr><th>When</th><td><?php echo show_date($appointment["scheduled_for"]); ?></td></tr>
          <tr><th>Reason</th><td><?php echo $appointment["reason"] ? e($appointment["reason"]) : "-"; ?></td></tr>
          <tr>
            <th>Bill</th>
            <td>
              <?php if ($bill) { ?>
                <?php echo money($bill["total_amount"]); ?>
                <span class="badge text-bg-<?php echo $bill["status"] == "paid" ? "success" : "warning"; ?>">
                  <?php echo e($bill["status"]); ?>
                </span>
                <a href="../bills/view.php?id=<?php echo $bill["bill_id"]; ?>" class="small ms-1">open</a>
              <?php } elseif ($can_write) { ?>
                <a href="../bills/add.php?appointment_id=<?php echo $id; ?>" class="small">Create bill</a>
              <?php } else { ?>
                Not generated yet
              <?php } ?>
            </td>
          </tr>
        </table>
      </div>
    </div>

    <div class="d-flex gap-2 mt-3">
      <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
      <?php if ($can_write && $appointment["status"] == "pending") { ?>
        <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary">
          <i class="bi bi-pencil me-1"></i>Reschedule
        </a>
        <a href="cancel.php?id=<?php echo $id; ?>" class="btn btn-outline-danger"
           onclick="return confirm('Cancel this appointment?');">
          <i class="bi bi-x-circle me-1"></i>Cancel appointment
        </a>
      <?php } ?>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-body">
        <h6 class="text-uppercase text-muted small mb-3">
          <i class="bi bi-file-medical me-1"></i>
          <?php echo $prescription ? "Prescription" : "Write prescription"; ?>
        </h6>

        <?php if ($errors) { ?>
          <div class="alert alert-danger py-2 small">
            <?php foreach ($errors as $err) { echo "<div>" . e($err) . "</div>"; } ?>
          </div>
        <?php } ?>

        <?php if ($can_write) { ?>
          <?php if (!$prescription) { ?>
            <p class="text-muted small">Saving a prescription marks this appointment as <strong>Done</strong>.</p>
          <?php } ?>
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Diagnosis <span class="text-danger">*</span></label>
              <input type="text" name="diagnosis" class="form-control"
                     value="<?php echo e($prescription ? $prescription["diagnosis"] : ""); ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Medicines <span class="text-danger">*</span></label>
              <textarea name="medicines" rows="4" class="form-control" required><?php echo e($prescription ? $prescription["medicines"] : ""); ?></textarea>
              <div class="form-text">One medicine per line.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Notes</label>
              <textarea name="notes" rows="2" class="form-control"><?php echo e($prescription ? $prescription["notes"] : ""); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i><?php echo $prescription ? "Update prescription" : "Save prescription"; ?>
            </button>
          </form>
        <?php } elseif ($prescription) { ?>
          <table class="table detail-table table-borderless mb-0">
            <tr><th>Diagnosis</th><td><?php echo e($prescription["diagnosis"]); ?></td></tr>
            <tr><th>Medicines</th><td><?php echo nl2br(e($prescription["medicines"])); ?></td></tr>
            <tr><th>Notes</th><td><?php echo $prescription["notes"] ? nl2br(e($prescription["notes"])) : "-"; ?></td></tr>
          </table>
        <?php } else { ?>
          <div class="empty-state"><i class="bi bi-file-medical"></i>No prescription written yet.</div>
        <?php } ?>

        <?php if ($prescription) { ?>
          <p class="text-muted small mt-3 mb-0">
            <i class="bi bi-clock-history me-1"></i>Written <?php echo show_date($prescription["created_at"]); ?>
          </p>
        <?php } ?>
      </div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
