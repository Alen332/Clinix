<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_role(['admin','receptionist']);

$errors = [];
$success = flash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM schedules WHERE id=?")->execute([(int)$_POST['delete_id']]);
    flash('success', 'Schedule slot removed.');
    header('Location: schedules.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $doctorId = (int)$_POST['doctor_id'];
    $day = $_POST['day_of_week'] ?? '';
    $start = $_POST['start_time'] ?? '';
    $end = $_POST['end_time'] ?? '';
    $slotMin = (int)($_POST['slot_minutes'] ?? 30);
    $validDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

    if (!$doctorId) $errors[] = 'Please select a doctor.';
    if (!in_array($day, $validDays)) $errors[] = 'Please select a valid day.';
    if (!$start || !$end || $start >= $end) $errors[] = 'End time must be after start time.';

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO schedules (doctor_id, day_of_week, start_time, end_time, slot_minutes) VALUES (?,?,?,?,?)");
        $stmt->execute([$doctorId, $day, $start, $end, $slotMin]);
        flash('success', 'Schedule added.');
        header('Location: schedules.php'); exit;
    }
}

$doctors = $pdo->query("SELECT id, full_name, specialization FROM users WHERE role='doctor' ORDER BY full_name")->fetchAll();
$schedules = $pdo->query("SELECT s.*, u.full_name AS doctor_name FROM schedules s JOIN users u ON s.doctor_id = u.id
                           ORDER BY u.full_name, FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")->fetchAll();

$page_title = 'Schedules';
$active = 'schedules';
$assets = '../assets';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="section-title mb-0">Doctor Schedules</h3>
    <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fa-solid fa-plus me-1"></i> Add Availability</button>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (!$schedules): ?>
            <p class="text-muted mb-0">No schedules configured yet.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Doctor</th><th>Day</th><th>Time</th><th>Slot</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($schedules as $s): ?>
                    <tr>
                        <td>Dr. <?= e($s['doctor_name']) ?></td>
                        <td><?= $s['day_of_week'] ?></td>
                        <td><?= date('g:i A', strtotime($s['start_time'])) ?> - <?= date('g:i A', strtotime($s['end_time'])) ?></td>
                        <td><?= $s['slot_minutes'] ?> min</td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Remove this slot?');">
                                <input type="hidden" name="delete_id" value="<?= $s['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header" style="background:var(--clinix-primary); color:#fff;">
          <h5 class="modal-title">Add Availability</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Doctor</label>
            <select name="doctor_id" class="form-select" required>
                <option value="">-- Select --</option>
                <?php foreach ($doctors as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['full_name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Day of Week</label>
            <select name="day_of_week" class="form-select" required>
                <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?><option><?= $d ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="row g-2">
            <div class="col-6"><label class="form-label">Start</label><input type="time" name="start_time" class="form-control" required></div>
            <div class="col-6"><label class="form-label">End</label><input type="time" name="end_time" class="form-control" required></div>
          </div>
          <div class="mt-2"><label class="form-label">Slot Length (min)</label><input type="number" name="slot_minutes" class="form-control" value="30" min="10" max="180" required></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="save" value="1" class="btn btn-clinix">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
