# Clinix — Clinic Appointment & Patient Management System

**Developed by:**

* Gerald Alen M. Cervantes
* Zelwyn Kael Escober

---

## About the System

Clinix is a simple and user-friendly clinic management system built using **PHP, MySQL (phpMyAdmin), and Bootstrap 5**. It is designed to help clinics manage appointments, patient records, and schedules efficiently.

The system includes three main user portals:

* **Patient** – for booking and managing appointments
* **Doctor** – for handling schedules and patient records
* **Admin/Receptionist** – for overall management and monitoring

---

## System Requirements

To run this system, you will need:

* XAMPP / WAMP / MAMP (or any Apache + PHP 8+ + MySQL setup)
* A web browser (Chrome, Edge, etc.)

---

## Installation Guide

Follow these steps to set up the system:

1. Copy the `clinix` folder into your server's web directory:

   * XAMPP: `C:\xampp\htdocs\clinix`
   * WAMP: `C:\wamp64\www\clinix`

2. Open your XAMPP/WAMP control panel and start:

   * Apache
   * MySQL

3. Go to **phpMyAdmin**:

   * Open: `http://localhost/phpmyadmin`

4. Import the database:

   * Click **Import**
   * Select the file `clinix.sql`
   * Click **Go**

   This will automatically create the database and tables with sample data.

5. Configure the database connection:

   * Open `config/db.php`
   * Update if needed:

   ```php
   $DB_HOST = 'localhost';
   $DB_NAME = 'clinix';
   $DB_USER = 'root';
   $DB_PASS = '';
   ```

6. Run the system:

   * Open your browser and go to:
     `http://localhost/clinix/`

---

## Demo Accounts

You can log in using the following accounts:

**Password for all accounts:** `password123`

| Role         | Email                                                           |
| ------------ | --------------------------------------------------------------- |
| Admin        | [admin@clinix.com](mailto:admin@clinix.com)                     |
| Doctor       | [maria.santos@clinix.com](mailto:maria.santos@clinix.com)       |
| Doctor       | [juan.delacruz@clinix.com](mailto:juan.delacruz@clinix.com)     |
| Receptionist | [ana.reyes@clinix.com](mailto:ana.reyes@clinix.com)             |
| Patient      | [pedro.gonzales@example.com](mailto:pedro.gonzales@example.com) |

New users can also register as a patient on the login page.

---

## Folder Structure

```
clinix/
├── clinix.sql
├── config/
│   ├── db.php
│   └── auth.php
├── includes/
├── assets/css/style.css
├── index.php
├── register.php
├── logout.php
├── patient/
├── doctor/
└── admin/
```

---

## Key Features

* Secure login and registration (with encrypted passwords)
* Role-based access (Patient, Doctor, Admin)
* Appointment booking based on doctor availability
* Patient medical records tracking
* Doctor schedule management
* Admin dashboard with reports and analytics (Chart.js)
* Clean and responsive design using Bootstrap 5

---

## Important Notes

Before using this system in a real environment:

* Change all default passwords
* Set a strong database password in `db.php`
* Use HTTPS for security
* You may add features like SMS or email notifications for appointments

---

## Final Thoughts

Clinix was created to make clinic operations easier and more organized. It simplifies appointment scheduling, improves record management, and provides a smooth experience for both staff and patients.

We hope this system helps demonstrate how technology can improve healthcare services.
