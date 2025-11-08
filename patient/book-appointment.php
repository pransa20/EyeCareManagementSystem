<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('patient')) {
    header('Location: /login.php');
    exit;
}

$conn = $GLOBALS['conn'];
$user = $auth->getCurrentUser();
$success_message = $error_message = '';

// Get available doctors with their specializations
$doctors = [];
$sql = "SELECT u.id, u.name, d.specialization 
FROM users u 
JOIN doctors d ON u.id = d.user_id 
WHERE u.role = 'doctor' AND u.status = 'active'";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

// Handle AJAX request for available time slots
if (isset($_GET['action']) && $_GET['action'] === 'get_available_times') {
    $doctor_id = $_GET['doctor_id'] ?? '';
    $date = $_GET['date'] ?? '';
    
    if (!empty($doctor_id) && !empty($date)) {
        // Get all booked time slots for the specific doctor and date
        $stmt = $conn->prepare("SELECT appointment_time FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status != 'cancelled'");
        $stmt->bind_param('is', $doctor_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $booked_times = [];
        while ($row = $result->fetch_assoc()) {
            $booked_times[] = $row['appointment_time'];
        }
        
        // All possible time slots
        $all_times = ['09:00:00', '10:00:00', '11:00:00', '12:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'];
        
        // Filter out booked times and past times for today
        $available_times = [];
        $current_date = date('Y-m-d');
        $current_time = date('H:i:s');
        
        foreach ($all_times as $time) {
            // If it's today, check if the time has already passed (with 1 hour buffer)
            if ($date === $current_date) {
                $time_with_buffer = date('H:i:s', strtotime($time) - 3600); // 1 hour before
                if ($time_with_buffer <= $current_time) {
                    continue; // Skip past times
                }
            }
            
            // Check if time is not booked
            if (!in_array($time, $booked_times)) {
                $available_times[] = [
                    'value' => $time,
                    'display' => date('h:i A', strtotime($time))
                ];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($available_times);
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $medical_history = $_POST['medical_history'] ?? '';

    if (empty($doctor_id) || empty($date) || empty($time)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // Check if appointment time is at least 1 hour in the future
        $appointmentDateTime = strtotime($date . ' ' . $time);
        $minBookingTime = strtotime('+1 hour');

        if ($appointmentDateTime < $minBookingTime) {
            $error_message = 'Appointments must be booked at least 1 hour in advance.';
        } else {
            // Check for existing appointments at the same time
            $stmt = $conn->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'");
            $stmt->bind_param('iss', $doctor_id, $date, $time);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = 'This time slot is already booked. Please select a different time.';
            } else {
                try {
                    // Format the appointment date and time
                    $formatted_date = date('Y-m-d', strtotime($date));
                    $formatted_time = date('H:i:s', strtotime($time));
                    
                    // Insert appointment into database
                    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, notes) VALUES (?, ?, ?, ?, 'pending', ?)");
                    $stmt->bind_param('iisss', $user['id'], $doctor_id, $formatted_date, $formatted_time, $reason);
                    
                    if ($stmt->execute()) {
                        $success_message = 'Your appointment has been booked successfully!';
                    } else {
                        $error_message = 'Failed to book appointment. Please try again.';
                    }
                } catch (Exception $e) {
                    $error_message = 'An error occurred while booking your appointment.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">
                <span class="text-primary">Trinetra</span> Eye Care
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="medical-history.php"><i class="fas fa-notes-medical me-1"></i>Medical History</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Book an Appointment</h4>
                        <form method="POST" action="" id="appointmentForm">
                            <div class="mb-3">
                                <label for="doctor_id" class="form-label">Select Doctor*</label>
                                <select class="form-select" id="doctor_id" name="doctor_id" required>
                                    <option value="">Choose a doctor</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?php echo $doctor['id']; ?>">
                                             <?php echo htmlspecialchars($doctor['name']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="date" class="form-label">Preferred Date*</label>
                                <input type="date" class="form-control" id="date" name="date" required 
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="time" class="form-label">Preferred Time*</label>
                                <select class="form-select" id="time" name="time" required disabled>
                                    <option value="">Please select doctor and date first</option>
                                </select>
                                <div id="timeLoader" class="d-none">
                                    <small class="text-muted">Loading available times...</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Visit*</label>
                                <textarea class="form-control" id="reason" name="reason" rows="2" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="medical_history" class="form-label">Relevant Medical History</label>
                                <textarea class="form-control" id="medical_history" name="medical_history" rows="3"
                                          placeholder="Please mention any relevant medical conditions, allergies, or medications"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-check me-2"></i>Book Appointment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const doctorSelect = document.getElementById('doctor_id');
            const dateInput = document.getElementById('date');
            const timeSelect = document.getElementById('time');
            const timeLoader = document.getElementById('timeLoader');

            function updateAvailableTimes() {
                const doctorId = doctorSelect.value;
                const selectedDate = dateInput.value;

                if (!doctorId || !selectedDate) {
                    timeSelect.disabled = true;
                    timeSelect.innerHTML = '<option value="">Please select doctor and date first</option>';
                    return;
                }

                // Show loader
                timeLoader.classList.remove('d-none');
                timeSelect.disabled = true;
                timeSelect.innerHTML = '<option value="">Loading...</option>';

                // Fetch available times
                fetch(`?action=get_available_times&doctor_id=${doctorId}&date=${selectedDate}`)
                    .then(response => response.json())
                    .then(times => {
                        timeLoader.classList.add('d-none');
                        timeSelect.innerHTML = '';

                        if (times.length === 0) {
                            timeSelect.innerHTML = '<option value="">No available times for this date</option>';
                            timeSelect.disabled = true;
                        } else {
                            timeSelect.innerHTML = '<option value="">Select available time</option>';
                            times.forEach(time => {
                                const option = document.createElement('option');
                                option.value = time.value;
                                option.textContent = time.display;
                                timeSelect.appendChild(option);
                            });
                            timeSelect.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching available times:', error);
                        timeLoader.classList.add('d-none');
                        timeSelect.innerHTML = '<option value="">Error loading times</option>';
                        timeSelect.disabled = true;
                    });
            }

            // Add event listeners
            doctorSelect.addEventListener('change', updateAvailableTimes);
            dateInput.addEventListener('change', updateAvailableTimes);
        });
    </script>
</body>
</html>