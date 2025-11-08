<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$doctor_id = $_GET['doctor_id'] ?? '';
$date = $_GET['date'] ?? '';

if (!$doctor_id || !$date) {
    echo json_encode(['booked_times' => []]);
    exit;
}

$conn = $GLOBALS['conn'];

$stmt = $conn->prepare("SELECT appointment_time FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status != 'cancelled'");
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$booked_times = [];
while ($row = $result->fetch_assoc()) {
    $booked_times[] = substr($row['appointment_time'], 0, 5); // 'HH:MM' format
}

echo json_encode(['booked_times' => $booked_times]);
