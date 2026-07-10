<?php
require_once __DIR__ . '/config/auth.php';

if (is_logged_in()) {
    $u = current_user();
    header('Location: ' . ltrim(dashboard_url($u['role']), '../'));
    exit;
}

$errors = [];
$old = ['full_name'=>'','email'=>'','phone'=>'','age'=>'','gender'=>'','medical_history'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'phone'     => trim($_POST['phone'] ?? ''),
        'age'       => trim($_POST['age'] ?? ''),
        'gender'    => $_POST['gender'] ?? '',
        'medical_history' => trim($_POST['medical_history'] ?? ''),
    ];
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($old['full_name'] === '') $errors[] = 'Full name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if ($old['age'] !== '' && (!is_numeric($old['age']) || $old['age'] < 0 || $old['age'] > 130)) $errors[] = 'Please enter a valid age.';

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, phone, age, gender, medical_history)
                                VALUES (?, ?, ?, 'patient', ?, ?, ?, ?)");
        $stmt->execute([
            $old['full_name'], $old['email'], $hash, $old['phone'] ?: null,
            $old['age'] !== '' ? $old['age'] : null, $old['gender'] ?: null, $old['medical_history'] ?: null
        ]);
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = 'patient';
        $_SESSION['full_name'] = $old['full_name'];
        header('Location: patient/dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clinix &mdash; Patient Registration</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="card auth-card shadow" style="max-width:560px;">
        <div class="auth-header">
            <div class="logo-badge"><i class="fa-solid fa-user-plus"></i></div>
            <h3 class="mb-0 fw-bold">Create Patient Account</h3>
            <small>It only takes a minute</small>
        </div>
        <div class="auth-body">
            <?php if ($errors): ?>
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required value="<?= e($old['full_name']) ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($old['email']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($old['phone']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Age</label>
                        <input type="number" name="age" min="0" max="130" class="form-control" value="<?= e($old['age']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select...</option>
                            <?php foreach (['Male','Female','Other'] as $g): ?>
                                <option value="<?= $g ?>" <?= $old['gender']===$g?'selected':'' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Medical History (optional)</label>
                        <textarea name="medical_history" class="form-control" rows="2" placeholder="Allergies, chronic conditions, past surgeries..."><?= e($old['medical_history']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-clinix w-100 py-2 mt-4">Create Account</button>
            </form>
            <p class="text-center mt-3 mb-0">Already have an account? <a href="index.php" style="color:var(--clinix-primary)" class="fw-semibold">Log in</a></p>
        </div>
    </div>
</div>
</body>
</html>
