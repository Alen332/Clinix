<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_role(['patient']);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id=? AND status='Pending'");
$stmt->execute([$user['id']]); $pendingCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id=? AND status='Confirmed' AND appointment_date >= CURDATE()");
$stmt->execute([$user['id']]); $upcomingCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id=? AND status='Completed'");
$stmt->execute([$user['id']]); $completedCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT a.*, u.full_name AS doctor_name, u.specialization
                        FROM appointments a JOIN users u ON a.doctor_id = u.id
                        WHERE a.patient_id = ? AND a.appointment_date >= CURDATE() AND a.status != 'Cancelled'
                        ORDER BY a.appointment_date, a.appointment_time LIMIT 5");
$stmt->execute([$user['id']]);
$upcoming = $stmt->fetchAll();

$page_title = 'Patient Dashboard';
$active = 'dashboard';
$assets = '../assets';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="section-title mb-0">Welcome back, <?= e(explode(' ', $user['full_name'])[0]) ?> 👋</h3>
        <p class="text-muted mb-0">Here's what's happening with your appointments.</p>
    </div>
    <a href="book_appointment.php" class="btn btn-gold"><i class="fa-solid fa-calendar-plus me-1"></i> Book Appointment</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div><div class="stat-num"><?= $pendingCount ?></div><div>Pending Requests</div></div>
                <i class="fa-solid fa-hourglass-half stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card gold">
            <div class="d-flex justify-content-between align-items-center">
                <div><div class="stat-num"><?= $upcomingCount ?></div><div>Upcoming Visits</div></div>
                <i class="fa-solid fa-calendar-check stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card dark">
            <div class="d-flex justify-content-between align-items-center">
                <div><div class="stat-num"><?= $completedCount ?></div><div>Completed Visits</div></div>
                <i class="fa-solid fa-file-circle-check stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Upcoming Appointments</div>
    <div class="card-body">
        <?php if (!$upcoming): ?>
            <p class="text-muted mb-0">You have no upcoming appointments. <a href="book_appointment.php">Book one now &rarr;</a></p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Specialization</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($upcoming as $a): ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($a['appointment_date'])) ?></td>
                        <td><?= date('g:i A', strtotime($a['appointment_time'])) ?></td>
                        <td>Dr. <?= e($a['doctor_name']) ?></td>
                        <td><?= e($a['specialization']) ?></td>
                        <td><span class="badge badge-<?= strtolower($a['status']) ?>"><?= $a['status'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
