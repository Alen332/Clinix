<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_role(['patient']);

$errors = [];
$success = flash('success');

$doctors = $pdo->query("SELECT id, full_name, specialization FROM users WHERE role='doctor' AND status='Active' ORDER BY full_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctorId = (int)($_POST['doctor_id'] ?? 0);
    $date = $_POST['appointment_date'] ?? '';
    $time = $_POST['appointment_time'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    if (!$doctorId) $errors[] = 'Please select a doctor.';
    if (!$date) $errors[] = 'Please select a date.';
    if (!$time) $errors[] = 'Please select an available time slot.';

    if (!$errors) {
        // Re-check the slot isn't already taken (race condition safety)
        $stmt = $pdo->prepare("SELECT id FROM appointments WHERE doctor_id=? AND appointment_date=? AND appointment_time=? AND status != 'Cancelled'");
        $stmt->execute([$doctorId, $date, $time]);
        if ($stmt->fetch()) {
            $errors[] = 'That slot was just taken. Please pick another time.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status)
                                    VALUES (?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute([$user['id'], $doctorId, $date, $time, $reason ?: null]);
            flash('success', 'Your appointment request has been submitted and is pending confirmation.');
            header('Location: book_appointment.php');
            exit;
        }
    }
}

$page_title = 'Book Appointment';
$active = 'book';
$assets = '../assets';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<h3 class="section-title mb-4">Book an Appointment</h3>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" id="bookingForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">1. Choose a Doctor</label>
                    <select name="doctor_id" id="doctorSelect" class="form-select" required>
                        <option value="">-- Select doctor --</option>
                        <?php foreach ($doctors as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= e($d['full_name']) ?> (<?= e($d['specialization']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">2. Choose a Date</label>
                    <input type="date" id="dateInput" class="form-control" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">3. Choose an Available Time Slot</label>
                    <div id="slotsWrapper" class="row g-2">
                        <p class="text-muted mb-0" id="slotsHint">Select a doctor and date to see available slots.</p>
                    </div>
                    <input type="hidden" name="appointment_time" id="selectedTime">
                    <input type="hidden" name="appointment_date" id="selectedDate">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">4. Reason for Visit (optional)</label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Fever and cough for 3 days"></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-clinix mt-4 px-4" id="submitBtn" disabled>
                <i class="fa-solid fa-check me-1"></i> Confirm Appointment Request
            </button>
        </form>
    </div>
</div>

<script>
const doctorSelect = document.getElementById('doctorSelect');
const dateInput = document.getElementById('dateInput');
const slotsWrapper = document.getElementById('slotsWrapper');
const selectedTime = document.getElementById('selectedTime');
const selectedDate = document.getElementById('selectedDate');
const submitBtn = document.getElementById('submitBtn');

function loadSlots() {
    const doctorId = doctorSelect.value;
    const date = dateInput.value;
    selectedTime.value = '';
    submitBtn.disabled = true;

    if (!doctorId || !date) {
        slotsWrapper.innerHTML = '<p class="text-muted mb-0">Select a doctor and date to see available slots.</p>';
        return;
    }
    slotsWrapper.innerHTML = '<p class="text-muted mb-0"><i class="fa-solid fa-spinner fa-spin"></i> Loading slots...</p>';

    fetch(`ajax_slots.php?doctor_id=${doctorId}&date=${date}`)
        .then(r => r.json())
        .then(data => {
            slotsWrapper.innerHTML = '';
            if (!data.slots || data.slots.length === 0) {
                slotsWrapper.innerHTML = `<p class="text-muted mb-0">${data.message || 'No slots available for this date.'}</p>`;
                return;
            }
            data.slots.forEach(slot => {
                const col = document.createElement('div');
                col.className = 'col-6 col-md-3 col-lg-2';
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'slot-btn w-100' + (slot.taken ? ' taken' : '');
                btn.textContent = slot.label;
                btn.disabled = slot.taken;
                if (!slot.taken) {
                    btn.addEventListener('click', () => {
                        document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                        btn.classList.add('selected');
                        selectedTime.value = slot.time;
                        selectedDate.value = date;
                        submitBtn.disabled = false;
                    });
                }
                col.appendChild(btn);
                slotsWrapper.appendChild(col);
            });
        })
        .catch(() => {
            slotsWrapper.innerHTML = '<p class="text-danger mb-0">Could not load slots. Please try again.</p>';
        });
}

doctorSelect.addEventListener('change', loadSlots);
dateInput.addEventListener('change', loadSlots);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
