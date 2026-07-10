<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_role(['patient']);

$stmt = $pdo->prepare("SELECT r.*, a.appointment_date, u.full_name AS doctor_name
                        FROM medical_records r
                        JOIN appointments a ON r.appointment_id = a.id
                        JOIN users u ON a.doctor_id = u.id
                        WHERE r.patient_id = ?
                        ORDER BY r.created_at DESC");
$stmt->execute([$user['id']]);
$records = $stmt->fetchAll();

$page_title = 'Medical Records';
$active = 'records';
$assets = '../assets';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<h3 class="section-title mb-4">My Medical Records</h3>

<?php if (!$records): ?>
    <div class="card"><div class="card-body text-muted">No medical records yet. Records appear here after a doctor completes a consultation.</div></div>
<?php else: ?>
    <?php foreach ($records as $r): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <span><i class="fa-solid fa-file-waveform me-2" style="color:var(--clinix-primary)"></i>Visit on <?= date('M j, Y', strtotime($r['appointment_date'])) ?></span>
            <span class="text-muted">Dr. <?= e($r['doctor_name']) ?></span>
        </div>
        <div class="card-body">
            <p><strong>Diagnosis:</strong><br><?= nl2br(e($r['diagnosis'])) ?: '<span class="text-muted">—</span>' ?></p>
            <p><strong>Prescription:</strong><br><?= nl2br(e($r['prescription'])) ?: '<span class="text-muted">—</span>' ?></p>
            <p class="mb-0"><strong>Lab Results:</strong><br><?= nl2br(e($r['lab_results'])) ?: '<span class="text-muted">—</span>' ?></p>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
