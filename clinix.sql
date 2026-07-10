-- =========================================================
-- Clinix : Clinic Appointment and Patient Management System
-- Import this file in phpMyAdmin (or run via MySQL CLI)
-- =========================================================

CREATE DATABASE IF NOT EXISTS clinix CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clinix;

-- ---------------------------------------------------------
-- Users table: patients, doctors, receptionists, admin
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient','doctor','receptionist','admin') NOT NULL DEFAULT 'patient',
    phone VARCHAR(30) DEFAULT NULL,
    age INT DEFAULT NULL,
    gender ENUM('Male','Female','Other') DEFAULT NULL,
    medical_history TEXT DEFAULT NULL,
    specialization VARCHAR(120) DEFAULT NULL,   -- used only for doctors
    status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Doctor weekly schedule
-- ---------------------------------------------------------
CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    slot_minutes INT NOT NULL DEFAULT 30,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Appointments
-- ---------------------------------------------------------
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    status ENUM('Pending','Confirmed','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Medical records (created after consultation)
-- ---------------------------------------------------------
CREATE TABLE medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    patient_id INT NOT NULL,
    diagnosis TEXT DEFAULT NULL,
    prescription TEXT DEFAULT NULL,
    lab_results TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- Seed data
-- Default password for ALL seeded accounts below is: password123
-- (hashed with PHP password_hash / BCRYPT)
-- =========================================================
-- Password for ALL accounts below is: password123
INSERT INTO users (full_name, email, password, role, phone, specialization) VALUES
('System Administrator', 'admin@clinix.com', '$2b$12$Jwq0B7G/HrlqyRYnJKtvyOOQfZLk5atODQUAYd90DV8PdQllC8fUO', 'admin', '0900000000', NULL),
('Dr. Maria Santos', 'maria.santos@clinix.com', '$2b$12$Jwq0B7G/HrlqyRYnJKtvyOOQfZLk5atODQUAYd90DV8PdQllC8fUO', 'doctor', '0911111111', 'General Medicine'),
('Dr. Juan Dela Cruz', 'juan.delacruz@clinix.com', '$2b$12$Jwq0B7G/HrlqyRYnJKtvyOOQfZLk5atODQUAYd90DV8PdQllC8fUO', 'doctor', '0922222222', 'Pediatrics'),
('Ana Reyes', 'ana.reyes@clinix.com', '$2b$12$Jwq0B7G/HrlqyRYnJKtvyOOQfZLk5atODQUAYd90DV8PdQllC8fUO', 'receptionist', '0933333333', NULL),
('Pedro Gonzales', 'pedro.gonzales@example.com', '$2b$12$Jwq0B7G/HrlqyRYnJKtvyOOQfZLk5atODQUAYd90DV8PdQllC8fUO', 'patient', '0944444444', NULL);

UPDATE users SET age = 34, gender = 'Male', medical_history = 'No known allergies.' WHERE email = 'pedro.gonzales@example.com';

INSERT INTO schedules (doctor_id, day_of_week, start_time, end_time, slot_minutes) VALUES
(2, 'Monday', '08:00:00', '12:00:00', 30),
(2, 'Wednesday', '08:00:00', '12:00:00', 30),
(2, 'Friday', '13:00:00', '17:00:00', 30),
(3, 'Tuesday', '09:00:00', '15:00:00', 30),
(3, 'Thursday', '09:00:00', '15:00:00', 30);
