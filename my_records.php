<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_role(['patient']);

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $id = (int)$_POST['cancel_id'];
    $stmt = $pdo->prepare("UPDATE appointments SET status='Cancelled' WHERE id=? AND patient_id=? AND status IN ('Pending','Confirmed')");
    $stmt->execute([$id, $user['id']]);
    flash('success', 'Appointment cancelled.');
    header('Location: my_appointments.php');
    exit;
}
$success = flash('success');

$stmt = $pdo->prepare("SELECT a.*, u.full_name AS doctor_name, u.specialization
                        FROM appointments a JOIN users u ON a.doctor_id = u.id
                        WHERE a.patient_id = ?
                        ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$stmt->execute([$user['id']]);
$appointments = $stmt->fetchAll();

$page_title = 'My Appointments';
$active = 'appts';
$assets = '../assets';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="section-title mb-0">My Appointments</h3>
    <a href="book_appointment.php" class="btn btn-gold"><i class="fa-solid fa-calendar-plus me-1"></i> Book New</a>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (!$appointments): ?>
            <p class="text-muted mb-0">You haven't booked any appointments yet.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Reason</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($a['appointment_date'])) ?></td>
                        <td><?= date('g:i A', strtotime($a['appointment_time'])) ?></td>
                        <td>Dr. <?= e($a['doctor_name']) ?><br><small class="text-muted"><?= e($a['specialization']) ?></small></td>
                        <td><?= e($a['reason']) ?: '<span class="text-muted">—</span>' ?></td>
                        <td><span class="badge badge-<?= strtolower($a['status']) ?>"><?= $a['status'] ?></span></td>
                        <td>
                            <?php if (in_array($a['status'], ['Pending','Confirmed'])): ?>
                            <form method="POST" onsubmit="return confirm('Cancel this appointment?');">
                                <input type="hidden" name="cancel_id" value="<?= $a['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i> Cancel</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
