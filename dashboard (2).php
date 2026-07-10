<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_role(['admin','receptionist']);

$startDate = $_GET['start'] ?? date('Y-m-01');
$endDate = $_GET['end'] ?? date('Y-m-d');

$stmt = $pdo->prepare("SELECT status, COUNT(*) AS total FROM appointments WHERE appointment_date BETWEEN ? AND ? GROUP BY status");
$stmt->execute([$startDate, $endDate]);
$statusCounts = ['Pending'=>0,'Confirmed'=>0,'Completed'=>0,'Cancelled'=>0];
foreach ($stmt->fetchAll() as $row) { $statusCounts[$row['status']] = (int)$row['total']; }
$totalInRange = array_sum($statusCounts);

$stmt = $pdo->prepare("SELECT d.full_name, COUNT(a.id) AS total,
                        SUM(a.status='Completed') AS completed,
                        SUM(a.status='Cancelled') AS cancelled
                        FROM users d
                        LEFT JOIN appointments a ON a.doctor_id = d.id AND a.appointment_date BETWEEN ? AND ?
                        WHERE d.role='doctor'
                        GROUP BY d.id ORDER BY total DESC");
$stmt->execute([$startDate, $endDate]);
$byDoctor = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role='patient' AND created_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)");
$stmt->execute([$startDate, $endDate]);
$newPatients = $stmt->fetchColumn();

$page_title = 'Reports';
$active = 'reports';
$assets = '../assets';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<h3 class="section-title mb-4">Reports &amp; Analytics</h3>

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-auto">
                <label class="form-label mb-0">From</label>
                <input type="date" name="start" class="form-control" value="<?= e($startDate) ?>">
            </div>
            <div class="col-auto">
                <label class="form-label mb-0">To</label>
                <input type="date" name="end" class="form-control" value="<?= e($endDate) ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-clinix"><i class="fa-solid fa-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3"><div class="stat-card"><div class="stat-num"><?= $totalInRange ?></div><div>Total Appointments</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="stat-card dark"><div class="stat-num"><?= $statusCounts['Completed'] ?></div><div>Completed</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="stat-card red"><div class="stat-num"><?= $statusCounts['Cancelled'] ?></div><div>Cancelled</div></div></div>
    <div class="col-sm-6 col-lg-3"><div class="stat-card gold"><div class="stat-num"><?= $newPatients ?></div><div>New Patients</div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Appointment Status Breakdown</div>
            <div class="card-body"><canvas id="statusChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Staff Workload (selected range)</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Doctor</th><th>Total</th><th>Completed</th><th>Cancelled</th></tr></thead>
                        <tbody>
                        <?php foreach ($byDoctor as $d): ?>
                            <tr>
                                <td>Dr. <?= e($d['full_name']) ?></td>
                                <td><?= (int)$d['total'] ?></td>
                                <td><?= (int)$d['completed'] ?></td>
                                <td><?= (int)$d['cancelled'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: ['Completed','Confirmed','Pending','Cancelled'],
        datasets: [{
            data: [<?= $statusCounts['Completed'] ?>, <?= $statusCounts['Confirmed'] ?>, <?= $statusCounts['Pending'] ?>, <?= $statusCounts['Cancelled'] ?>],
            backgroundColor: ['#006B3F', '#7fb69f', '#F4C430', '#CD5C5C']
        }]
    },
    options: { responsive: true }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
