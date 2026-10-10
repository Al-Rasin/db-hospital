<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(["admin"]);

$page_title = "Reports";
$page_subheading = "Summary queries over the hospital data";
$active = "reports";

// Appointments and earning per doctor (GROUP BY with joins).
$by_doctor = fetch_all($conn,
    "SELECT d.doctor_id, d.name, d.specialization,
            COUNT(a.appointment_id) AS total_appointments,
            SUM(CASE WHEN a.status = 'done' THEN 1 ELSE 0 END) AS done_count,
            IFNULL(SUM(b.total_amount), 0) AS earning
     FROM doctors d
     LEFT JOIN appointments a ON a.doctor_id = d.doctor_id
     LEFT JOIN bills b ON b.appointment_id = a.appointment_id
     GROUP BY d.doctor_id, d.name, d.specialization
     ORDER BY total_appointments DESC, d.name");

// Patients per department.
$by_dept = fetch_all($conn,
    "SELECT dp.name AS dept_name,
            COUNT(DISTINCT d.doctor_id) AS doctor_count,
            COUNT(DISTINCT a.patient_id) AS patient_count
     FROM departments dp
     LEFT JOIN doctors d ON d.dept_id = dp.dept_id
     LEFT JOIN appointments a ON a.doctor_id = d.doctor_id
     GROUP BY dp.dept_id, dp.name
     ORDER BY patient_count DESC");

// Patients who visited most often (HAVING clause).
$frequent = fetch_all($conn,
    "SELECT p.name, p.phone, COUNT(a.appointment_id) AS visits
     FROM patients p
     JOIN appointments a ON a.patient_id = p.patient_id
     GROUP BY p.patient_id, p.name, p.phone
     HAVING COUNT(a.appointment_id) >= 2
     ORDER BY visits DESC, p.name
     LIMIT 10");

// Appointments of the last 7 days.
$daily = fetch_all($conn,
    "SELECT DATE(scheduled_for) AS day, COUNT(*) AS total
     FROM appointments
     WHERE scheduled_for >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
     GROUP BY DATE(scheduled_for)
     ORDER BY day DESC");

$money_total = fetch_one($conn,
    "SELECT IFNULL(SUM(total_amount), 0) AS billed,
            IFNULL(SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END), 0) AS collected
     FROM bills");

require_once __DIR__ . "/../includes/header.php";
?>

<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card stat-card h-100">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="stat-value"><?php echo money($money_total["billed"]); ?></div>
          <div class="stat-label">Total billed</div>
        </div>
        <i class="bi bi-receipt stat-icon"></i>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card stat-card is-today h-100">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="stat-value"><?php echo money($money_total["collected"]); ?></div>
          <div class="stat-label">Collected</div>
        </div>
        <i class="bi bi-cash-coin stat-icon"></i>
      </div>
    </div>
  </div>
</div>

<div class="table-card mb-4">
  <div class="px-3 py-2 border-bottom">
    <strong class="small text-uppercase text-muted">Appointments and earning per doctor</strong>
  </div>
  <table class="table">
    <thead>
      <tr><th>Doctor</th><th>Specialization</th><th>Total</th><th>Completed</th><th class="text-end">Billed</th></tr>
    </thead>
    <tbody>
      <?php foreach ($by_doctor as $row) { ?>
      <tr>
        <td class="fw-medium">Dr. <?php echo e($row["name"]); ?></td>
        <td><?php echo e($row["specialization"]); ?></td>
        <td><?php echo $row["total_appointments"]; ?></td>
        <td><?php echo $row["done_count"]; ?></td>
        <td class="text-end"><?php echo money($row["earning"]); ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="table-card h-100">
      <div class="px-3 py-2 border-bottom">
        <strong class="small text-uppercase text-muted">Department wise load</strong>
      </div>
      <table class="table">
        <thead><tr><th>Department</th><th>Doctors</th><th>Patients</th></tr></thead>
        <tbody>
          <?php foreach ($by_dept as $row) { ?>
          <tr>
            <td class="fw-medium"><?php echo e($row["dept_name"]); ?></td>
            <td><?php echo $row["doctor_count"]; ?></td>
            <td><?php echo $row["patient_count"]; ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="table-card h-100">
      <div class="px-3 py-2 border-bottom">
        <strong class="small text-uppercase text-muted">Most frequent patients</strong>
      </div>
      <table class="table">
        <thead><tr><th>Patient</th><th>Phone</th><th>Visits</th></tr></thead>
        <tbody>
          <?php if ($frequent) { foreach ($frequent as $row) { ?>
          <tr>
            <td class="fw-medium"><?php echo e($row["name"]); ?></td>
            <td><?php echo e($row["phone"]); ?></td>
            <td><?php echo $row["visits"]; ?></td>
          </tr>
          <?php } } else { ?>
          <tr><td colspan="3"><div class="empty-state"><i class="bi bi-people"></i>No visit recorded.</div></td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-12">
    <div class="table-card">
      <div class="px-3 py-2 border-bottom">
        <strong class="small text-uppercase text-muted">Appointments in the last 7 days</strong>
      </div>
      <table class="table">
        <thead><tr><th>Date</th><th>Appointments</th></tr></thead>
        <tbody>
          <?php if ($daily) { foreach ($daily as $row) { ?>
          <tr>
            <td><?php echo show_date($row["day"], "d M Y"); ?></td>
            <td><?php echo $row["total"]; ?></td>
          </tr>
          <?php } } else { ?>
          <tr><td colspan="2"><div class="empty-state"><i class="bi bi-calendar-x"></i>Nothing in this range.</div></td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
