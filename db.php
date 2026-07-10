<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_role(['admin','receptionist']);

$errors = [];
$success = flash('success');

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=? AND role='patient'");
    $stmt->execute([(int)$_POST['delete_id']]);
    flash('success', 'Patient record deleted.');
    header('Location: patients.php'); exit;
}

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = (int)($_POST['id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $medical_history = trim($_POST['medical_history'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($full_name === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($age !== '' && (!is_numeric($age) || $age < 0 || $age > 130)) $errors[] = 'Enter a valid age.';
    if (!$id && strlen($password) < 6) $errors[] = 'Password must be at least 6 characters for new patients.';

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) $errors[] = 'That email is already used by another account.';
    }

    if (!$errors) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, phone=?, age=?, gender=?, medical_history=? WHERE id=? AND role='patient'");
            $stmt->execute([$full_name, $email, $phone ?: null, $age !== '' ? $age : null, $gender ?: null, $medical_history ?: null, $id]);
            if ($password) {
                $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt->execute([password_hash($password, PASSWORD_BCRYPT), $id]);
            }
            flash('success', 'Patient updated.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (full_name,email,password,role,phone,age,gender,medical_history) VALUES (?,?,?,'patient',?,?,?,?)");
            $stmt->execute([$full_name, $email, password_hash($password, PASSWORD_BCRYPT), $phone ?: null, $age !== '' ? $age : null, $gender ?: null, $medical_history ?: null]);
            flash('success', 'Patient added.');
        }
        header('Location: patients.php'); exit;
    }
}

$search = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM users WHERE role='patient'";
$params = [];
if ($search !== '') {
    $sql .= " AND (full_name LIKE ? OR email LIKE ?)";
    $params = ["%$search%", "%$search%"];
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll();

$page_title = 'Patients';
$active = 'patients';
$assets = '../assets';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="section-title mb-0">Patient Records</h3>
    <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#patientModal" onclick="openCreate()"><i class="fa-solid fa-plus me-1"></i> Add Patient</button>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<div class="card mb-3">
    <div class="card-body py-2">
        <form class="d-flex gap-2" method="GET">
            <input type="text" name="q" class="form-control" placeholder="Search by name or email..." value="<?= e($search) ?>">
            <button class="btn btn-outline-clinix"><i class="fa-solid fa-search"></i></button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!$patients): ?>
            <p class="text-muted mb-0">No patients found.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Age/Gender</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($patients as $p): ?>
                    <tr>
                        <td><?= e($p['full_name']) ?></td>
                        <td><?= e($p['email']) ?></td>
                        <td><?= e($p['phone']) ?: '—' ?></td>
                        <td><?= e($p['age']) ?: '—' ?> / <?= e($p['gender']) ?: '—' ?></td>
                        <td class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-clinix" onclick='openEdit(<?= json_encode($p) ?>)' data-bs-toggle="modal" data-bs-target="#patientModal"><i class="fa-solid fa-pen"></i></button>
                            <form method="POST" onsubmit="return confirm('Delete this patient record? This also deletes their appointments.');">
                                <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
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

<!-- Modal -->
<div class="modal fade" id="patientModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header" style="background:var(--clinix-primary); color:#fff;">
          <h5 class="modal-title" id="modalTitle">Add Patient</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="f_id">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="full_name" id="f_name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" id="f_email" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" id="f_phone" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Age</label><input type="number" name="age" id="f_age" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Gender</label>
                <select name="gender" id="f_gender" class="form-select">
                    <option value="">--</option><option>Male</option><option>Female</option><option>Other</option>
                </select>
            </div>
            <div class="col-12"><label class="form-label">Medical History</label><textarea name="medical_history" id="f_history" class="form-control" rows="2"></textarea></div>
            <div class="col-12">
                <label class="form-label">Password <span id="pwHint" class="text-muted small">(leave blank to keep current)</span></label>
                <input type="password" name="password" id="f_password" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="save" value="1" class="btn btn-clinix">Save Patient</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openCreate() {
    document.getElementById('modalTitle').textContent = 'Add Patient';
    document.getElementById('f_id').value = '';
    document.getElementById('f_name').value = '';
    document.getElementById('f_email').value = '';
    document.getElementById('f_phone').value = '';
    document.getElementById('f_age').value = '';
    document.getElementById('f_gender').value = '';
    document.getElementById('f_history').value = '';
    document.getElementById('f_password').value = '';
    document.getElementById('f_password').required = true;
    document.getElementById('pwHint').textContent = '(required)';
}
function openEdit(p) {
    document.getElementById('modalTitle').textContent = 'Edit Patient';
    document.getElementById('f_id').value = p.id;
    document.getElementById('f_name').value = p.full_name;
    document.getElementById('f_email').value = p.email;
    document.getElementById('f_phone').value = p.phone || '';
    document.getElementById('f_age').value = p.age || '';
    document.getElementById('f_gender').value = p.gender || '';
    document.getElementById('f_history').value = p.medical_history || '';
    document.getElementById('f_password').value = '';
    document.getElementById('f_password').required = false;
    document.getElementById('pwHint').textContent = '(leave blank to keep current)';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
