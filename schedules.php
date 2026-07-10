<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_role(['admin','receptionist']);

$errors = [];
$success = flash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_status'])) {
    $id = (int)$_POST['appt_id'];
    $status = $_POST['set_status'];
    if (in_array($status, ['Pending','Confirmed','Cancelled','Completed'])) {
        $stmt = $pdo->prepare("UPDATE appointments SET status=? WHERE id=?");
        $stmt->execute([$status, $id]);
        flash('success', "Appointment marked as $status.");
    }
    header('Location: appointments.php'.(isset($_GET['status'])?'?status='.$_GET['status']:'')); exit;
}

// Manual booking by receptionist/admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_appt'])) {
    $patientId = (int)$_POST['patient_id'];
    $doctorId = (int)$_POST['doctor_id'];
    $date = $_POST['appointment_date'] ?? '';
    $time = $_POST['appointment_time'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    if (!$patientId || !$doctorId || !$date || !$time) {
        $errors[] = 'All fields are required to create an appointment.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM appointments WHERE doctor_id=? AND appointment_date=? AND appointment_time=? AND status != 'Cancelled'");
        $stmt->execute([$doctorId, $date, $time]);
        if ($stmt->fetch()) {
            $errors[] = 'This doctor already has an appointment at that date/time.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status) VALUES (?,?,?,?,?, 'Confirmed')");
            $stmt->execute([$patientId, $doctorId, $date, $time, $reason ?: null]);
            flash('success', 'Appointment created and confirmed.');
            header('Location: appointments.php'); exit;
        }
    }
}

$filter = $_GET['status'] ?? 'all';
$sql = "SELECT a.*, p.full_name AS patient_name, d.full_name AS doctor_name, d.specialization
        FROM appointments a
        JOIN users p ON a.patient_id = p.id
        JOIN users d ON a.doctor_id = d.id";
$params = [];
if (in_array($filter, ['Pending','Confirmed','Completed','Cancelled'])) {
    $sql .= " WHERE a.status = ?";
    $params[] = $filter;
}
$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

$patients = $pdo->query("SELECT id, full_name FROM users WHERE role='patient' ORDER BY full_name")->fetchAll();
$doctors = $pdo->query("SELECT id, full_name, specialization FROM users WHERE role='doctor' AND status='Active' ORDER BY full_name")->fetchAll();

$page_title = 'Appointments';
$active = 'appointments';
$assets = '../assets';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="section-title mb-0">Appointment Management</h3>
    <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fa-solid fa-plus me-1"></i> New Appointment</button>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<ul class="nav nav-pills mb-3">
    <?php foreach (['all'=>'All','Pending'=>'Pending','Confirmed'=>'Confirmed','Completed'=>'Completed','Cancelled'=>'Cancelled'] as $key=>$label): ?>
        <li class="nav-item"><a class="nav-link <?= $filter===$key?'active bg-success':'text-success' ?>" href="?status=<?= $key ?>"><?= $label ?></a></li>
    <?php endforeach; ?>
</ul>

<div class="card">
    <div class="card-body">
        <?php if (!$appointments): ?>
            <p class="text-muted mb-0">No appointments found.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?= e($a['patient_name']) ?></td>
                        <td>Dr. <?= e($a['doctor_name']) ?><br><small class="text-muted"><?= e($a['specialization']) ?></small></td>
                        <td><?= date('M j, Y', strtotime($a['appointment_date'])) ?></td>
                        <td><?= date('g:i A', strtotime($a['appointment_time'])) ?></td>
                        <td><?= e($a['reason']) ?: '—' ?></td>
                        <td><span class="badge badge-<?= strtolower($a['status']) ?>"><?= $a['status'] ?></span></td>
                        <td class="d-flex gap-1 flex-wrap">
                            <?php if ($a['status'] === 'Pending'): ?>
                                <form method="POST"><input type="hidden" name="appt_id" value="<?= $a['id'] ?>"><input type="hidden" name="set_status" value="Confirmed"><button class="btn btn-sm btn-outline-clinix">Confirm</button></form>
                            <?php endif; ?>
                            <?php if (in_array($a['status'], ['Pending','Confirmed'])): ?>
                                <form method="POST"><input type="hidden" name="appt_id" value="<?= $a['id'] ?>"><input type="hidden" name="set_status" value="Cancelled"><button class="btn btn-sm btn-outline-danger">Cancel</button></form>
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

<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header" style="background:var(--clinix-primary); color:#fff;">
          <h5 class="modal-title">New Appointment</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Patient *</label>
                <select name="patient_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($patients as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['full_name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Doctor *</label>
                <select name="doctor_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($doctors as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['full_name']) ?> (<?= e($d['specialization']) ?>)</option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6"><label class="form-label">Date *</label><input type="date" name="appointment_date" class="form-control" min="<?= date('Y-m-d') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Time *</label><input type="time" name="appointment_time" class="form-control" required></div>
            <div class="col-12"><label class="form-label">Reason</label><textarea name="reason" class="form-control" rows="2"></textarea></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="create_appt" value="1" class="btn btn-clinix">Create &amp; Confirm</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
