<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('doctor')) {
    header('Location: /login.php');
    exit;
}

$conn = $GLOBALS['conn'];
$doctor = $auth->getCurrentUser();
$doctor_id = $doctor['id'];
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $conn->begin_transaction();

        // Delete existing schedules for the doctor
        $sql = "DELETE FROM doctor_schedules WHERE doctor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $doctor_id);
        $stmt->execute();

        // Insert new schedules
        $sql = "INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, break_start, break_end, is_available, slot_duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        foreach ($_POST['schedule'] as $day => $schedule) {
            $is_available = isset($schedule['is_available']) ? 1 : 0;
            $start_time = $is_available ? $schedule['start_time'] : null;
            $end_time = $is_available ? $schedule['end_time'] : null;
            $break_start = !empty($schedule['break_start']) ? $schedule['break_start'] : null;
            $break_end = !empty($schedule['break_end']) ? $schedule['break_end'] : null;
            $slot_duration = $schedule['slot_duration'] ?? 60;

            $stmt->bind_param('iissssii', 
                $doctor_id, 
                $day, 
                $start_time, 
                $end_time, 
                $break_start, 
                $break_end, 
                $is_available,
                $slot_duration
            );
            $stmt->execute();
        }

        $conn->commit();
        $message = 'Schedule updated successfully!';
    } catch (Exception $e) {
        $conn->rollback();
        $error = 'Failed to update schedule. Please try again.';
    }
}

// Get current schedule
$schedules = [];
$sql = "SELECT * FROM doctor_schedules WHERE doctor_id = ? ORDER BY day_of_week";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $schedules[$row['day_of_week']] = $row;
}

// Day names mapping
$days = [
    0 => 'Sunday',
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedule - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Include sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Schedule</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" class="mb-4">
                    <div class="card">
                        <div class="card-body">
                            <?php foreach ($days as $day_num => $day_name): ?>
                                <?php
                                $schedule = $schedules[$day_num] ?? [
                                    'is_available' => true,
                                    'start_time' => '09:00',
                                    'end_time' => '17:00',
                                    'break_start' => '13:00',
                                    'break_end' => '14:00',
                                    'slot_duration' => 60
                                ];
                                ?>
                                <div class="mb-4 border-bottom pb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="schedule[<?php echo $day_num; ?>][is_available]" 
                                                       id="available_<?php echo $day_num; ?>" 
                                                       <?php echo $schedule['is_available'] ? 'checked' : ''; ?>
                                                       onchange="toggleDaySchedule(<?php echo $day_num; ?>)">
                                                <label class="form-check-label fw-bold" for="available_<?php echo $day_num; ?>">
                                                    <?php echo htmlspecialchars($day_name); ?>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-10" id="schedule_<?php echo $day_num; ?>">
                                            <div class="row g-3">
                                                <div class="col-md-2">
                                                    <label class="form-label">Start Time</label>
                                                    <input type="time" class="form-control" 
                                                           name="schedule[<?php echo $day_num; ?>][start_time]" 
                                                           value="<?php echo $schedule['start_time']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">End Time</label>
                                                    <input type="time" class="form-control" 
                                                           name="schedule[<?php echo $day_num; ?>][end_time]" 
                                                           value="<?php echo $schedule['end_time']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Break Start</label>
                                                    <input type="time" class="form-control" 
                                                           name="schedule[<?php echo $day_num; ?>][break_start]" 
                                                           value="<?php echo $schedule['break_start']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Break End</label>
                                                    <input type="time" class="form-control" 
                                                           name="schedule[<?php echo $day_num; ?>][break_end]" 
                                                           value="<?php echo $schedule['break_end']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Slot Duration (mins)</label>
                                                    <select class="form-select" name="schedule[<?php echo $day_num; ?>][slot_duration]">
                                                        <option value="30" <?php echo $schedule['slot_duration'] == 30 ? 'selected' : ''; ?>>30 minutes</option>
                                                        <option value="60" <?php echo $schedule['slot_duration'] == 60 ? 'selected' : ''; ?>>1 hour</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary">Save Schedule</button>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleDaySchedule(day) {
        const checkbox = document.getElementById(`available_${day}`);
        const scheduleDiv = document.getElementById(`schedule_${day}`);
        scheduleDiv.style.display = checkbox.checked ? 'block' : 'none';
    }

    // Initialize schedule visibility
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($days as $day_num => $day_name): ?>
            toggleDaySchedule(<?php echo $day_num; ?>);
        <?php endforeach; ?>
    });
    </script>
</body>
</html>