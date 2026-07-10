<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_role(['admin']); // only admin manages staff accounts

$errors = [];
$success = flash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=? AND role IN ('doctor','receptionist')");
    $stmt->execute([(int)$_POST['delete_id']]);
    flash('success', 'Staff account removed.');
    header('Location: doctors.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET status = IF(status='Active','Inactive','Active') WHERE id=? AND role IN ('doctor','receptionist')");
    $stmt->execute([(int)$_POST['toggle_id']]);
    header('Location: doctors.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = (int)($_POST['id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($full_name === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (!in_array($role, ['doctor','receptionist'])) $errors[] = 'Invalid role.';
    if ($role === 'doctor' && $specialization === '') $errors[] = 'Specialization is required for doctors.';
    if (!$id && strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) $errors[] = 'That email is already used by another account.';
    }

    if (!$errors) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, role=?, phone=?, specialization=? WHERE id=?");
            $stmt->execute([$full_name, $email, $role, $phone ?: null, $role==='doctor' ? $specialization : null, $id]);
            if ($password) {
                $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt->execute([password_hash($password, PASSWORD_BCRYPT), $id]);
            }
            flash('success', 'Account updated.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (full_name,email,password,role,phone,specialization) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$full_name, $email, password_hash($password, PASSWORD_BCRYPT), $role, $phone ?: null, $role==='doctor' ? $specialization : null]);
            flash('success', 'Account created.');
        }
        header('Location: doctors.php'); exit;
    }
}

$staff = $pdo->query("SELECT * FROM users WHERE role IN ('doctor','receptionist') ORDER BY role, full_name")->fetchAll();

$page_title = 'Doctors & Staff';
$active = 'doctors';
$assets = '../assets';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="section-title mb-0">Doctors &amp; Staff</h3>
    <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#staffModal" onclick="openCreate()"><i class="fa-solid fa-plus me-1"></i> Add Staff</button>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Name</th><th>Role</th><th>Email</th><th>Specialization</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($staff as $s): ?>
                    <tr>
                        <td><?= e($s['full_name']) ?></td>
                        <td><span class="badge text-bg-secondary text-capitalize"><?= $s['role'] ?></span></td>
                        <td><?= e($s['email']) ?></td>
                        <td><?= e($s['specialization']) ?: '—' ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="toggle_id" value="<?= $s['id'] ?>">
                                <button class="btn btn-sm <?= $s['status']==='Active' ? 'btn-outline-clinix' : 'btn-outline-secondary' ?>"><?= $s['status'] ?></button>
                            </form>
                        </td>
                        <td class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-clinix" onclick='openEdit(<?= json_encode($s) ?>)' data-bs-toggle="modal" data-bs-target="#staffModal"><i class="fa-solid fa-pen"></i></button>
                            <form method="POST" onsubmit="return confirm('Remove this staff account?');">
                                <input type="hidden" name="delete_id" value="<?= $s['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="staffModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header" style="background:var(--clinix-primary); color:#fff;">
          <h5 class="modal-title" id="modalTitle">Add Staff</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="f_id">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="full_name" id="f_name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" id="f_email" class="form-control" required></div>
            <div class="col-md-4">
                <label class="form-label">Role *</label>
                <select name="role" id="f_role" class="form-select" required onchange="toggleSpec()">
                    <option value="doctor">Doctor</option>
                    <option value="receptionist">Receptionist</option>
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" id="f_phone" class="form-control"></div>
            <div class="col-md-4" id="specWrap"><label class="form-label">Specialization</label><input type="text" name="specialization" id="f_spec" class="form-control" placeholder="e.g. Cardiology"></div>
            <div class="col-12">
                <label class="form-label">Password <span id="pwHint" class="text-muted small">(leave blank to keep current)</span></label>
                <input type="password" name="password" id="f_password" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="save" value="1" class="btn btn-clinix">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleSpec() {
    document.getElementById('specWrap').style.display = document.getElementById('f_role').value === 'doctor' ? 'block' : 'none';
}
function openCreate() {
    document.getElementById('modalTitle').textContent = 'Add Staff';
    ['f_id','f_name','f_email','f_phone','f_spec','f_password'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('f_role').value = 'doctor';
    document.getElementById('f_password').required = true;
    document.getElementById('pwHint').textContent = '(required)';
    toggleSpec();
}
function openEdit(s) {
    document.getElementById('modalTitle').textContent = 'Edit Staff';
    document.getElementById('f_id').value = s.id;
    document.getElementById('f_name').value = s.full_name;
    document.getElementById('f_email').value = s.email;
    document.getElementById('f_role').value = s.role;
    document.getElementById('f_phone').value = s.phone || '';
    document.getElementById('f_spec').value = s.specialization || '';
    document.getElementById('f_password').value = '';
    document.getElementById('f_password').required = false;
    document.getElementById('pwHint').textContent = '(leave blank to keep current)';
    toggleSpec();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
