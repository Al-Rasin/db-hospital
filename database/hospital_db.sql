-- Hospital Management System
-- Database Lab Project
-- Import this file in phpMyAdmin (XAMPP) before running the project.

DROP DATABASE IF EXISTS hospital_db;
CREATE DATABASE hospital_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE hospital_db;

-- ---------------------------------------------------------------
-- 1. departments
-- ---------------------------------------------------------------
CREATE TABLE departments (
    dept_id     INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(200) DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- 2. doctors
-- ---------------------------------------------------------------
CREATE TABLE doctors (
    doctor_id      INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    phone          VARCHAR(20)  NOT NULL,
    room_number    VARCHAR(10)  DEFAULT NULL,
    fee            DECIMAL(8,2) NOT NULL DEFAULT 500.00,
    dept_id        INT DEFAULT NULL,
    CONSTRAINT fk_doctor_dept FOREIGN KEY (dept_id)
        REFERENCES departments(dept_id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- 3. patients
-- ---------------------------------------------------------------
CREATE TABLE patients (
    patient_id  INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    age         INT NOT NULL,
    gender      ENUM('Male','Female','Other') NOT NULL,
    phone       VARCHAR(20) NOT NULL,
    address     VARCHAR(200) DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- 4. users  (login table, one row per person who can log in)
-- ---------------------------------------------------------------
CREATE TABLE users (
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50) NOT NULL UNIQUE,
    password   VARCHAR(50) NOT NULL,
    full_name  VARCHAR(100) NOT NULL,
    role       ENUM('admin','doctor','patient') NOT NULL,
    doctor_id  INT DEFAULT NULL,
    patient_id INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_doctor FOREIGN KEY (doctor_id)
        REFERENCES doctors(doctor_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_user_patient FOREIGN KEY (patient_id)
        REFERENCES patients(patient_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- 5. appointments
-- ---------------------------------------------------------------
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id     INT NOT NULL,
    doctor_id      INT NOT NULL,
    scheduled_for  DATETIME NOT NULL,
    reason         VARCHAR(200) DEFAULT NULL,
    status         ENUM('pending','done','cancelled') NOT NULL DEFAULT 'pending',
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_app_patient FOREIGN KEY (patient_id)
        REFERENCES patients(patient_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_app_doctor FOREIGN KEY (doctor_id)
        REFERENCES doctors(doctor_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_app_date ON appointments(scheduled_for);

-- ---------------------------------------------------------------
-- 6. prescriptions  (one per appointment)
-- ---------------------------------------------------------------
CREATE TABLE prescriptions (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id  INT NOT NULL UNIQUE,
    diagnosis       VARCHAR(200) NOT NULL,
    medicines       TEXT NOT NULL,
    notes           TEXT,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pres_app FOREIGN KEY (appointment_id)
        REFERENCES appointments(appointment_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- 7. bills
-- ---------------------------------------------------------------
CREATE TABLE bills (
    bill_id          INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id   INT NOT NULL UNIQUE,
    consultation_fee DECIMAL(8,2) NOT NULL DEFAULT 0,
    test_fee         DECIMAL(8,2) NOT NULL DEFAULT 0,
    medicine_fee     DECIMAL(8,2) NOT NULL DEFAULT 0,
    total_amount     DECIMAL(10,2) AS (consultation_fee + test_fee + medicine_fee) STORED,
    status           ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bill_app FOREIGN KEY (appointment_id)
        REFERENCES appointments(appointment_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- View used by the reports page
-- ---------------------------------------------------------------
CREATE VIEW appointment_details AS
SELECT a.appointment_id,
       a.scheduled_for,
       a.status,
       a.reason,
       p.patient_id,
       p.name  AS patient_name,
       p.age,
       p.gender,
       p.phone AS patient_phone,
       d.doctor_id,
       d.name  AS doctor_name,
       d.specialization,
       dp.name AS department_name
FROM appointments a
JOIN patients p   ON p.patient_id = a.patient_id
JOIN doctors  d   ON d.doctor_id  = a.doctor_id
LEFT JOIN departments dp ON dp.dept_id = d.dept_id;

-- ---------------------------------------------------------------
-- Trigger: writing a prescription closes the appointment
-- ---------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER trg_prescription_done
AFTER INSERT ON prescriptions
FOR EACH ROW
BEGIN
    UPDATE appointments
    SET status = 'done'
    WHERE appointment_id = NEW.appointment_id;
END$$
DELIMITER ;

-- ---------------------------------------------------------------
-- Stored procedure: total money billed for one doctor
-- ---------------------------------------------------------------
DELIMITER $$
CREATE PROCEDURE doctor_earning(IN p_doctor_id INT, OUT p_total DECIMAL(12,2))
BEGIN
    SELECT IFNULL(SUM(b.total_amount), 0) INTO p_total
    FROM bills b
    JOIN appointments a ON a.appointment_id = b.appointment_id
    WHERE a.doctor_id = p_doctor_id AND b.status = 'paid';
END$$
DELIMITER ;

-- ===============================================================
-- Sample data
-- ===============================================================
INSERT INTO departments (name, description) VALUES
('Medicine',  'General medicine and internal care'),
('Cardiology','Heart related treatment'),
('Orthopedics','Bone and joint treatment'),
('Pediatrics','Child care'),
('Gynecology','Womens health');

INSERT INTO doctors (name, specialization, phone, room_number, fee, dept_id) VALUES
('Abdul Karim',   'Cardiologist',   '01711000001', '201', 800.00, 2),
('Nusrat Jahan',  'Gynecologist',   '01711000002', '305', 700.00, 5),
('Sohel Rana',    'Orthopedic',     '01711000003', '110', 600.00, 3),
('Farhana Akter', 'Child Specialist','01711000004','402', 500.00, 4),
('Mizanur Rahman','Medicine',       '01711000005', '101', 500.00, 1);

INSERT INTO patients (name, age, gender, phone, address) VALUES
('Rakib Hasan',   28, 'Male',   '01811000001', 'Mirpur 10, Dhaka'),
('Sadia Islam',   34, 'Female', '01811000002', 'Uttara Sector 7, Dhaka'),
('Jamal Uddin',   56, 'Male',   '01811000003', 'Sylhet Sadar'),
('Tanvir Ahmed',  19, 'Male',   '01811000004', 'Chattogram'),
('Mehjabin Chowdhury', 41, 'Female', '01811000005', 'Dhanmondi, Dhaka'),
('Arif Hossain',   7, 'Male',   '01811000006', 'Rajshahi');

INSERT INTO users (username, password, full_name, role, doctor_id, patient_id) VALUES
('admin',  'admin123', 'System Administrator', 'admin',  NULL, NULL),
('karim',  'doctor123','Abdul Karim',          'doctor', 1,    NULL),
('nusrat', 'doctor123','Nusrat Jahan',         'doctor', 2,    NULL),
('rakib',  'patient123','Rakib Hasan',         'patient',NULL, 1),
('sadia',  'patient123','Sadia Islam',         'patient',NULL, 2);

INSERT INTO appointments (patient_id, doctor_id, scheduled_for, reason, status) VALUES
(1, 1, '2026-09-22 10:30:00', 'Chest pain',        'pending'),
(2, 2, '2026-09-22 12:00:00', 'Routine check up',  'pending'),
(3, 5, '2026-09-20 09:00:00', 'High fever',        'done'),
(4, 3, '2026-09-19 16:00:00', 'Knee injury',       'done'),
(5, 1, '2026-09-18 11:00:00', 'Blood pressure',    'cancelled'),
(6, 4, '2026-09-23 10:00:00', 'Cold and cough',    'pending');

INSERT INTO prescriptions (appointment_id, diagnosis, medicines, notes) VALUES
(3, 'Viral fever', 'Napa 500mg - 1+1+1\nFexo 120mg - 0+0+1', 'Drink plenty of water. Come back after 5 days.'),
(4, 'Ligament strain', 'Tufnil 200mg - 1+0+1\nCalbo D - 1+0+0', 'No heavy work for two weeks.');

INSERT INTO bills (appointment_id, consultation_fee, test_fee, medicine_fee, status) VALUES
(3, 500.00, 800.00, 350.00, 'paid'),
(4, 600.00, 1200.00, 450.00, 'unpaid');
