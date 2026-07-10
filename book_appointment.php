<?php
require_once __DIR__ . '/../config/auth.php';
require_role(['patient']);
header('Content-Type: application/json');

$doctorId = (int)($_GET['doctor_id'] ?? 0);
$date = $_GET['date'] ?? '';

if (!$doctorId || !$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['slots' => [], 'message' => 'Invalid request.']);
    exit;
}

$dayName = date('l', strtotime($date));

$stmt = $pdo->prepare("SELECT * FROM schedules WHERE doctor_id = ? AND day_of_week = ?");
$stmt->execute([$doctorId, $dayName]);
$schedules = $stmt->fetchAll();

if (!$schedules) {
    echo json_encode(['slots' => [], 'message' => 'Doctor is not available on this day.']);
    exit;
}

// Get already booked times for that doctor/date (excluding cancelled)
$stmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status != 'Cancelled'");
$stmt->execute([$doctorId, $date]);
$booked = array_column($stmt->fetchAll(), 'appointment_time');

$slots = [];
$today = date('Y-m-d');
$now = date('H:i:s');

foreach ($schedules as $sch) {
    $start = strtotime($sch['start_time']);
    $end = strtotime($sch['end_time']);
    $interval = (int)$sch['slot_minutes'] * 60;
    for ($t = $start; $t < $end; $t += $interval) {
        $timeStr = date('H:i:s', $t);
        // Skip past slots if the date is today
        if ($date === $today && $timeStr <= $now) continue;
        $slots[] = [
            'time' => $timeStr,
            'label' => date('g:i A', $t),
            'taken' => in_array($timeStr, $booked),
        ];
    }
}

echo json_encode(['slots' => $slots]);
