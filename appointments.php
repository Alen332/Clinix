<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_role(['patient']);

$errors = [];
$success = flash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $medical_history = trim($_POST['medical_history'] ?? '');

    if ($full_name === '') $errors[] = 'Full name is required.';
    if ($age !== '' && (!is_numeric($age) || $age < 0 || $age > 130)) $errors[] = 'Please enter a valid age.';

    if (!$errors) {
        $stmt = $pdo->prepare("UPDATE users SET full_name=?, phone=?, age=?, gender=?, medical_history=? WHERE id=?");
        $stmt->execute([$full_name, $phone ?: null, $age !== '' ? $age : null, $gender ?: null, $medical_history ?: null, $user['id']]);
        $_SESSION['full_name'] = $full_name;
        flash('success', 'Profile updated successfully.');
        header('Location: profile.php');
        exit;
    }
    $user = array_merge($user, compact('full_name','phone','age','gender','medical_history'));
}

$page_title = 'My Profile';
$active = 'profile';
$assets = '../assets';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<h3 class="section-title mb-4">My Profile</h3>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form method="POST">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Email (cannot be changed)</label>
                    <input type="text" class="form-control" value="<?= e($user['email']) ?>" disabled>
                </div>
                <div class="col-12">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($user['phone']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Age</label>
                    <input type="number" name="age" class="form-control" value="<?= e($user['age']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">--</option>
                        <?php foreach (['Male','Female','Other'] as $g): ?>
                            <option value="<?= $g ?>" <?= $user['gender']===$g?'selected':'' ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Medical History</label>
                    <textarea name="medical_history" class="form-control" rows="3"><?= e($user['medical_history']) ?></textarea>
                </div>
            </div>
            <button class="btn btn-clinix mt-4"><i class="fa-solid fa-floppy-disk me-1"></i> Save Changes</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
