<?php
require_once __DIR__ . '/vendor/autoload.php';

// Include database configuration
require_once __DIR__ . '/config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success = $error = '';
// Fetch all active doctors from the database
$doctors_query = "SELECT u.id, u.name, u.role, d.specialization FROM users u LEFT JOIN doctors d ON u.id = d.user_id WHERE u.role = 'doctor' AND u.status = 'active' ORDER BY u.name";
$doctors_result = $conn->query($doctors_query);

if ($doctors_result === false) {
    die("Error executing query: " . $conn->error);
}

$doctors = [];
while ($row = $doctors_result->fetch_assoc()) {
    $doctors[] = $row;
}

// // Adding more doctors with Nepali names
$additional_doctors = [];
//     [
//         'id' => 101,
//         'name' => 'Dr. Pratik Sharma',
//         'specialization' => 'General Ophthalmology',
//         'description' => 'Complete eye health evaluation with advanced diagnostic techniques.'
//     ],
//     // ... (other doctors)
//     [
//         'id' => 115,
//         'name' => 'Dr. Neeta Chhetri',
//         'specialization' => 'Corneal Diseases',
//         'description' => 'Specialized care for corneal conditions and treatments.'
//     ],
// ];

// Merge the additional doctors with the existing ones
$doctors = array_merge($doctors, $additional_doctors);
// Get doctor ID from URL if present
$selected_doctor_id = isset($_GET['doctor']) ? $_GET['doctor'] : '';

// Fetch all active doctors from the database
$doctors_query = "SELECT u.id, u.name, u.role, d.specialization FROM users u LEFT JOIN doctors d ON u.id = d.user_id WHERE u.role = 'doctor' AND u.status = 'active' ORDER BY u.name";
$doctors_result = $conn->query($doctors_query);

if ($doctors_result === false) {
    die("Error executing query: " . $conn->error);
}

$doctors = [];
while ($row = $doctors_result->fetch_assoc()) {
    $doctors[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $service = $_POST['service'] ?? '';
    $message = $_POST['message'] ?? '';
    $doctor_id = $_POST['doctor_id'] ?? '';

    // Validate name
if (empty($name)) {
        $error = 'Name is required.';
} elseif (!preg_match('/^[a-zA-Z ]{2,50}$/', $name)) {
        $error = 'Name should only contain letters and spaces (2-50 characters).';
}
// Validate email
elseif (empty($email)) {
        $error = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
}
// Validate phone number
elseif (empty($phone)) {
        $error = 'Phone number is required.';
} elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = 'Please enter a valid 10-digit phone number.';
}
// Validate other required fields
elseif (empty($date) || empty($time) || empty($service)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if appointment time is at least 1 hour in the future
        $appointmentDateTime = strtotime($date . ' ' . $time);
        $minBookingTime = strtotime('+1 hour');

        if ($appointmentDateTime < $minBookingTime) {
            $error = 'Appointments must be booked at least 1 hour in advance.';
        } else {
            // ... existing code ...
            $doctor_id = $_POST['doctor_id'] ?? '';
            $day_of_week = date('w', strtotime($date));
            $schedule_query = "SELECT * FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ? AND is_available = TRUE";
            $schedule_stmt = $conn->prepare($schedule_query);
            if ($schedule_stmt === false) {
                $error = 'Database error: ' . $conn->error;
            } else {
                $schedule_stmt->bind_param('ii', $doctor_id, $day_of_week);
                if (!$schedule_stmt->execute()) {
                    $error = 'Error executing query: ' . $schedule_stmt->error;
                } else {
                    $schedule_result = $schedule_stmt->get_result();
                    if ($schedule_result->num_rows === 0) {
                        $error = 'Doctor is not available on this day.';
                    } else {
                        $schedule = $schedule_result->fetch_assoc();
                        if (strtotime($time) < strtotime($schedule['start_time']) || 
                            strtotime($time) > strtotime($schedule['end_time'])) {
                            $error = 'Appointment time must be between ' . $schedule['start_time'] . ' and ' . $schedule['end_time'];
                        }
                    }
                }
            }
// ... existing code ...
            // Check doctor's schedule availability
            // $doctor_id = $_POST['doctor_id'] ?? '';
            // $day_of_week = date('w', strtotime($date));
            // $schedule_query = "SELECT * FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ? AND is_available = TRUE";
            // $schedule_stmt = $conn->prepare($schedule_query);
            // $schedule_stmt->bind_param('ii', $doctor_id, $day_of_week);
            // $schedule_stmt->execute();
            // $schedule_result = $schedule_stmt->get_result();
            // if ($schedule_result->num_rows === 0) {
            //     $error = 'Doctor is not available on this day.';
                
            // }
            // $schedule = $schedule_result->fetch_assoc();
            // if (strtotime($time) < strtotime($schedule['start_time']) || 
            //     strtotime($time) > strtotime($schedule['end_time'])) {
            //     $error = 'Appointment time must be between ' . $schedule['start_time'] . ' and ' . $schedule['end_time'];
                
            // }
            // if ($schedule['break_start'] && $schedule['break_end'] &&
            //     strtotime($time) >= strtotime($schedule['break_start']) &&
            //     strtotime($time) <= strtotime($schedule['break_end'])) {
            //     $error = 'Appointment time conflicts with doctor\'s break time.';
                
            // }

            // Check for existing appointments at the same time

            if (empty($doctor_id)) {
                $error = 'Please select a doctor.';
            } else {
                // Check if the doctor is available at the selected time
                $stmt = $conn->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'");
$stmt->bind_param('iss', $doctor_id, $date, $time);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $error_message = 'This time slot is already booked. Please select a different time.';
    return;
}
            $stmt->bind_param("iss", $doctor_id, $date, $time);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error = 'This time slot is already booked. Please select a different time.';
            } else {
                try {
                    // Format the appointment date and time
                    $formatted_date = date('Y-m-d', strtotime($date));
                    $formatted_time = date('H:i:s', strtotime($time));
                    
                    // Validate doctor_id exists
                    $doctor_check = $conn->prepare("SELECT id FROM doctors WHERE id = ?");
                    $doctor_check->bind_param("i", $doctor_id);
                    $doctor_check->execute();
                    $doctor_check->store_result();
                    
                    if ($doctor_check->num_rows === 0) {
                        $error = 'Invalid doctor selected.';
                    } else {
                        // Insert appointment into database
                        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, notes) VALUES ((SELECT id FROM users WHERE email = ?), ?, ?, ?, 'pending', ?)");
                        $stmt->bind_param("sisss", $email, $doctor_id, $formatted_date, $formatted_time, $message);
                        
                        if ($stmt->execute()) {
                            // Send email to admin
                            $mail = new PHPMailer(true);
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = 'eyecaretrinetra@gmail.com';
                            $mail->Password = 'pomm yfiu rtvd crdm';
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = 587;
                            $mail->SMTPDebug = 2;
                            $mail->Debugoutput = function($str, $level) {
                                error_log("SMTP Debug: $str");
                            };
                            $mail->Timeout = 30;
                        }
                    }
                } catch (Exception $e) {
                    $error = 'An error occurred while processing your appointment: ' . $e->getMessage();
                }
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
    <link href="bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet" href="fontawesome/css/all.min.css">
   <link href="assets/css/style.css" rel="stylesheet">
    <script>
        function disableSaturdays() {
            const dateInput = document.getElementById('date');
            dateInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const day = selectedDate.getUTCDay(); // 0 = Sunday, 6 = Saturday
                if (day === 6) {
                    alert("Saturdays are not available for appointments.");
                    this.value = ''; // Clear the selection
                }
            });
        }

        function checkDoctorAvailability() {
            const doctorId = document.getElementById('doctor_id').value;
            const date = document.getElementById('date').value;
            const timeSelect = document.getElementById('time');

            if (!doctorId || !date) return;

            // Reset all options
            Array.from(timeSelect.options).forEach(option => {
                option.disabled = false;
            });

            // Fetch available time slots from the API
            fetch(`api/doctor-availability.php?doctor_id=${doctorId}&date=${date}`)
                .then(response => response.json())
                .then(data => {
    if (data.available_slots) {
        Array.from(timeSelect.options).forEach(option => {
            if (option.value && !data.available_slots.includes(option.value)) {
                option.disabled = true;
            }
        });
    }
});
                    }
                           

        function disableTimeOptions() {
            const timeSelect = document.getElementById('time');
            const currentTime = new Date();
            const currentHour = currentTime.getHours();
            const currentMinutes = currentTime.getMinutes();

            // Disable 9 AM if the current time is already 9 AM or later
            if (currentHour > 9 || (currentHour === 9 && currentMinutes > 0)) {
                const option = timeSelect.querySelector('option[value="09:00"]');
                if (option) {
                    option.disabled = true;
                }
            }
        }

        function validateForm() {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const date = document.getElementById('date').value;
            const time = document.getElementById('time').value;
            const service = document.getElementById('service').value;

            if (name === '' || email === '' || phone === '' || date === '' || time === '' || service === '') {
                alert('Please fill in all required fields.');
                return false;
            }
        }

        window.onload = function() {
            disableSaturdays();
            disableTimeOptions();
        };
    </script>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <img src="logo.png" alt="Trinetra Eye Care Logo"> <span class="text-primary"></span> 
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.html">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="services.php">Our Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop2.php">Optical Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i>Login
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="loginDropdown" style="min-width: 200px;">
                                                        <li><a class="dropdown-item py-2 px-3 hover-primary" href="admin/login.php"><i class="fas fa-user-shield me-2"></i>Admin Login</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 px-3 hover-primary" href="doctor/login.php"><i class="fas fa-user-md me-2"></i>Doctor Login</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 px-3 hover-primary" href="patient/login.php"><i class="fas fa-user me-2"></i>Patient Login</a></li>
                        </ul>
                    </li>
                    <li class="nav-item ms-lg-2"><a class="btn btn-primary rounded-pill px-4" href="appointment.php">Book Appointment</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Appointment Form Section -->
    <section class="appointment-section py-5 mt-5">
        <div class="container">
            <h2 class="text-center mb-5">Book Your Appointment</h2>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <form method="POST" action="" onsubmit="return validateForm()">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="doctor_id" class="form-label">Select Doctor *</label>
                                        <!-- <select class="form-select" id="doctor_id" name="doctor_id" required onchange="checkDoctorAvailability()">
                                            <option value="">Choose a doctor</option>
                                            <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?php echo htmlspecialchars($doctor['id']); ?>" <?php echo ($selected_doctor_id == $doctor['id'] ? 'selected' : ''); ?>>
                                                <?php echo htmlspecialchars($doctor['name']); ?>
                                                <?php echo $doctor['specialization'] ? ' (' . htmlspecialchars($doctor['specialization']) . ')' : ''; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select> -->
                                        <select class="form-select" id="doctor_id" name="doctor_id" required onchange="checkDoctorAvailability()">
                                            <option value="">Choose a doctor</option>
                                            <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?php echo htmlspecialchars($doctor['id']); ?>" <?php echo ($selected_doctor_id == $doctor['id'] ? 'selected' : ''); ?>>
                                                <?php echo htmlspecialchars($doctor['name']); ?>
                                                <?php echo $doctor['specialization'] ? ' (' . htmlspecialchars($doctor['specialization']) . ')' : ''; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="service" class="form-label">Service Type *</label>
                                        <select class="form-select" id="service" name="service" required>
                                            <option value="">Select a service</option>
                                            <option value="eye-examination">Eye Examination</option>
                                            <option value="vision-correction">Vision Correction</option>
                                            <option value="surgical-procedure">Surgical Procedure</option>
                                            <option value="optical-shop">Optical Shop</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date" class="form-label">Preferred Date *</label>
                                        <input type="date" class="form-control" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>" onchange="checkDoctorAvailability()">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="time" class="form-label">Preferred Time *</label>
                                        <select class="form-select" id="time" name="time" required>
                                            <option value="">Select a time</option>
                                            <?php
                                            $times = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00'];
                                            foreach ($times as $t) {
                                                echo "<option value=\"$t\">" . date('h:i A', strtotime($t)) . "</option>";
                                            }
                
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="message" class="form-label">Additional Message</label>
                                        <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Book Appointment</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>