# Clinix — Clinic Appointment & Patient Management System

A full PHP + MySQL (phpMyAdmin) + Bootstrap 5 system with three portals:
Patient, Doctor, and Admin/Receptionist.

## 1. Requirements
- XAMPP / WAMP / MAMP (or any Apache + PHP 8+ + MySQL stack)
- A browser

## 2. Setup

1. Copy the entire `clinix` folder into your server's web root:
   - XAMPP: `C:\xampp\htdocs\clinix` (Windows) or `/Applications/XAMPP/htdocsclinix` (Mac)
   - WAMP: `C:\wamp64\www\clinix`
2. Start **Apache** and **MySQL** from your XAMPP/WAMP control panel.
3. Open **phpMyAdmin** (usually `http://localhost/phpmyadmin`).
4. Click **Import**, choose the file `clinix.sql` from this folder, and click **Go**.
   This creates the `clinix` database with all tables and demo data.
5. Open `config/db.php` and update the credentials if needed:
   ```php
   $DB_HOST = 'localhost';
   $DB_NAME = 'clinix';
   $DB_USER = 'root';
   $DB_PASS = '';   // your MySQL root password, blank by default on XAMPP
   ```
6. Visit `http://localhost/clinix/` in your browser.

## 3. Demo accounts
All seeded accounts use the password: **password123**

| Role         | Email                          |
|--------------|---------------------------------|
| Admin        | admin@clinix.com                |
| Doctor       | maria.santos@clinix.com         |
| Doctor       | juan.delacruz@clinix.com        |
| Receptionist | ana.reyes@clinix.com            |
| Patient      | pedro.gonzales@example.com      |

New patients can also self-register from the login page.

## 4. Folder structure
```
clinix/
├── clinix.sql              -> Import this in phpMyAdmin
├── config/
│   ├── db.php               -> Database connection (edit credentials here)
│   └── auth.php             -> Session/auth helpers
├── includes/                -> Shared header/sidebar/footer templates
├── assets/css/style.css     -> Theme (green/gold palette)
├── index.php                -> Login page
├── register.php             -> Patient self-registration
├── logout.php
├── patient/                 -> Patient portal (book, view, cancel appointments, records, profile)
├── doctor/                  -> Doctor portal (schedule, patients, add medical records)
└── admin/                   -> Admin/Receptionist portal (CRUD patients, doctors, schedules, reports)
```

## 5. Features included
- Registration & login with hashed passwords (bcrypt), role-based access
- Patient: book appointments against real doctor availability (auto-generated time slots), view/cancel appointments, view medical records, edit profile
- Doctor: manage weekly availability, confirm/cancel appointments, add diagnosis/prescription/lab results which mark a visit completed
- Admin/Receptionist: full CRUD on patients, doctors/staff, schedules; manual appointment booking; reports & analytics dashboard with charts (Chart.js) using the green/gold/red theme
- Responsive Bootstrap 5 UI with a custom sidebar layout, mobile offcanvas menu, and status badges

## 6. Notes for going to production
- Change all demo passwords immediately.
- Set a strong, unique `$DB_PASS` in `config/db.php` and restrict DB user privileges.
- Serve over HTTPS.
- Consider adding real SMS/email notifications (e.g. via Twilio / PHPMailer) — the schema and workflow already support "Confirmed / Cancelled" state changes that could trigger such notifications.
