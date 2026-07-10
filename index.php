<?php
require_once __DIR__ . '/config/auth.php';

if (is_logged_in()) {
    $u = current_user();
    header('Location: ' . ltrim(dashboard_url($u['role']), '../'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'Inactive') {
                $error = 'Your account has been deactivated. Please contact the clinic.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                header('Location: ' . ltrim(dashboard_url($user['role']), '../'));
                exit;
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clinix &mdash; Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="card auth-card shadow">
        <div class="auth-header">
            <div class="logo-badge"><i class="fa-solid fa-suitcase-medical"></i></div>
            <h3 class="mb-0 fw-bold">Clinix</h3>
            <small>Clinic Appointment &amp; Patient Management</small>
        </div>
        <div class="auth-body">
            <h5 class="mb-3 section-title">Sign in to your account</h5>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-clinix w-100 py-2">Log In</button>
            </form>

            <hr>
            <p class="text-center mb-1">Don't have an account? <a href="register.php" class="fw-semibold" style="color:var(--clinix-primary)">Register as a patient</a></p>

            
        </div>
    </div>
</div>
</body>
</html>
