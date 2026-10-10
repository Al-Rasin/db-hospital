# Hospital Management System

Database Lab project. PHP + MySQL, runs on XAMPP.

## Requirements

- XAMPP (Apache + MySQL)
- A browser

## How to run

1. Copy the whole `dblab` folder inside the XAMPP web folder.
   - Windows: `C:\xampp\htdocs\dblab`
   - macOS: `/Applications/XAMPP/htdocs/dblab`
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open <http://localhost/phpmyadmin> and go to the **Import** tab.
4. Choose the file `database/hospital_db.sql` and press **Go**.
   It creates the database `hospital_db` with all tables and sample data.
5. Open <http://localhost/dblab/login.php> in the browser.

If the MySQL user or password of your XAMPP is different, change it in
`config/database.php`.

## Login accounts

| Username | Password   | Role    |
|----------|------------|---------|
| admin    | admin123   | Admin   |
| karim    | doctor123  | Doctor  |
| nusrat   | doctor123  | Doctor  |
| rakib    | patient123 | Patient |
| sadia    | patient123 | Patient |

Passwords are kept as plain text to make the lab demonstration easy.

## What each role can do

| Role    | Access |
|---------|--------|
| Admin   | Everything: patients, doctors, departments, appointments, bills, reports |
| Doctor  | His own appointments, writes prescriptions, creates bills, sees patients |
| Patient | His own appointments, prescriptions and bills |

## Modules

| Module       | Pages |
|--------------|-------|
| Login        | login.php, logout.php |
| Dashboard    | index.php (counts + recent appointments) |
| Patients     | list with search, add, edit, view with visit history, delete |
| Doctors      | list with department filter, add, edit, delete |
| Departments  | list with doctor count, add, edit, delete |
| Appointments | list with status filter, book, view, reschedule, cancel, prescription |
| Bills        | list with status filter, create from a visit, view, mark as paid |
| Reports      | earning per doctor, department load, frequent patients, last 7 days |

## Database

Database name: `hospital_db`

Tables

1. `departments` - dept_id, name, description
2. `doctors` - doctor_id, name, specialization, phone, room_number, fee, dept_id
3. `patients` - patient_id, name, age, gender, phone, address, created_at
4. `users` - user_id, username, password, full_name, role, doctor_id, patient_id
5. `appointments` - appointment_id, patient_id, doctor_id, scheduled_for, reason, status
6. `prescriptions` - prescription_id, appointment_id, diagnosis, medicines, notes
7. `bills` - bill_id, appointment_id, consultation_fee, test_fee, medicine_fee, total_amount, status

Relations

```
departments 1 ------ M doctors
doctors     1 ------ M appointments
patients    1 ------ M appointments
appointments 1 ----- 1 prescriptions
appointments 1 ----- 1 bills
users        1 ----- 1 doctors / patients  (a login row points to one of them)
```

Database features used

- Primary keys, foreign keys with `ON DELETE CASCADE` and `ON DELETE SET NULL`
- `ENUM` for gender, appointment status and bill status
- A generated column: `bills.total_amount` is calculated by MySQL from the three fees
- A view: `appointment_details` joins appointments, patients, doctors and departments
- A trigger: `trg_prescription_done` sets the appointment to `done` when a prescription is inserted
- A stored procedure: `doctor_earning(doctor_id, OUT total)` returns the paid amount of a doctor
- Reports use JOIN, LEFT JOIN, GROUP BY, HAVING and aggregate functions

Calling the procedure from phpMyAdmin:

```sql
CALL doctor_earning(1, @total);
SELECT @total;
```

## ER diagram

`docs/erd.png` (and `docs/erd.svg`) shows the seven tables with their primary
keys, foreign keys and relations.

## Folder structure

```
dblab/
  config/database.php      mysqli connection
  includes/
    auth.php               session check and role helpers
    functions.php          query helpers, escaping, badges
    header.php footer.php  the page layout with the sidebar
  database/hospital_db.sql the full database with sample data
  index.php login.php logout.php
  patients/ doctors/ departments/ appointments/ bills/ reports/
  docs/                    ER diagram (png and svg)
  assets/css assets/vendor Bootstrap 5 and icons kept locally
```

All queries use prepared statements (`mysqli_prepare`) so the input of a form
cannot break the SQL.
