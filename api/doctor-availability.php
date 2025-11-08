<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$conn = $GLOBALS['conn'];
$doctor_id = $_GET['doctor_id'] ?? '';
$date = $_GET['date'] ?? '';

if (empty($doctor_id) || empty($date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

// Get doctor's schedule for the given day
$day_of_week = date('w', strtotime($date));
$sql = "SELECT start_time, end_time, break_start, break_end, slot_duration, is_available 
        FROM doctor_schedules 
        WHERE doctor_id = ? AND day_of_week = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $doctor_id, $day_of_week);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();

if (!$schedule || !$schedule['is_available']) {
    echo json_encode(['available_slots' => [], 'message' => 'Doctor is not available on this day']);
    exit;
}

// Generate all possible time slots based on schedule
$start = strtotime($schedule['start_time']);
$end = strtotime($schedule['end_time']);
$duration = $schedule['slot_duration'] * 60; // Convert minutes to seconds
$break_start = $schedule['break_start'] ? strtotime($schedule['break_start']) : null;
$break_end = $schedule['break_end'] ? strtotime($schedule['break_end']) : null;

$all_slots = [];
for ($time = $start; $time < $end; $time += $duration) {
    // Skip slots during break time
    if ($break_start && $break_end) {
        if ($time >= $break_start && $time < $break_end) {
            continue;
        }
    }
    $all_slots[] = date('H:i', $time);
}

// Get booked appointments for the doctor on the selected date
$sql = "SELECT appointment_time FROM appointments 
        WHERE doctor_id = ? AND appointment_date = ? 
        AND status NOT IN ('cancelled', 'rejected')";
$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();

// Create array of booked times
$booked_slots = [];
while ($row = $result->fetch_assoc()) {
    $booked_slots[] = date('H:i', strtotime($row['appointment_time']));
}

// Filter out booked slots and past times for today
$available_slots = array_filter($all_slots, function($slot) use ($booked_slots, $date) {
    // If slot is already booked, it's not available
    if (in_array($slot, $booked_slots)) {
        return false;
    }
    
    // If it's today, check if the time has already passed
    if ($date === date('Y-m-d')) {
        $slot_time = strtotime($date . ' ' . $slot);
        $current_time = strtotime('+1 hour'); // Add 1 hour buffer
        return $slot_time > $current_time;
    }
    
    return true;
});

echo json_encode([
    'available_slots' => array_values($available_slots),
    'schedule' => [
        'start_time' => $schedule['start_time'],
        'end_time' => $schedule['end_time'],
        'break_start' => $schedule['break_start'],
        'break_end' => $schedule['break_end'],
        'slot_duration' => $schedule['slot_duration']
    ]
]);