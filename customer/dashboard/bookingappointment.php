<?php
session_start();
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/header.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../../customer-login.php');
    exit;
}

$conn = $GLOBALS['conn'];
$user_id = $_SESSION['user_id'];

// Fetch user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch available doctors
$doctors = [];
$doctorSql = "SELECT id, name, specialization FROM doctors WHERE status = 'active' ORDER BY name ASC";
$doctorResult = $conn->query($doctorSql);
while ($row = $doctorResult->fetch_assoc()) {
    $doctors[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;
    $appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : '';
    $appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : '';
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

    // Validate inputs
    $errors = [];
    if (empty($doctor_id)) {
        $errors[] = 'Please select a doctor';
    }
    if (empty($appointment_date)) {
        $errors[] = 'Please select appointment date';
    }
    if (empty($appointment_time)) {
        $errors[] = 'Please select appointment time';
    }
    if (empty($reason)) {
        $errors[] = 'Please enter reason for appointment';
    }

    if (empty($errors)) {
        // Check if doctor is available at selected time
        $checkSql = "SELECT id FROM appointments 
                     WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('iss', $doctor_id, $appointment_date, $appointment_time);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $errors[] = 'Selected time slot is already booked. Please choose another time.';
        } else {
            // Insert appointment
            $insertSql = "INSERT INTO appointments 
                          (user_id, doctor_id, appointment_date, appointment_time, reason, status) 
                          VALUES (?, ?, ?, ?, ?, 'pending')";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param('iisss', $user_id, $doctor_id, $appointment_date, $appointment_time, $reason);
            
            if ($insertStmt->execute()) {
                $_SESSION['success'] = 'Appointment booked successfully!';
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = 'Failed to book appointment. Please try again.';
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
    <link href="../../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../fontawesome/css/all.min.css">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        body {
            padding-top: 80px;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <div class="form-container mt-4">
            <h2 class="mb-4">Book Appointment</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="bookingappointment.php">
                <div class="mb-3">
                    <label for="doctor_id" class="form-label">Select Doctor</label>
                    <select class="form-select" id="doctor_id" name="doctor_id" required>
                        <option value="">-- Select Doctor --</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?= $doctor['id'] ?>" <?= isset($_POST['doctor_id']) && $_POST['doctor_id'] == $doctor['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($doctor['name']) ?> - <?= htmlspecialchars($doctor['specialization']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="appointment_date" class="form-label">Appointment Date</label>
                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                               value="<?= isset($_POST['appointment_date']) ? htmlspecialchars($_POST['appointment_date']) : '' ?>" 
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="appointment_time" class="form-label">Appointment Time</label>
                        <input type="time" class="form-control" id="appointment_time" name="appointment_time" 
                               value="<?= isset($_POST['appointment_time']) ? htmlspecialchars($_POST['appointment_time']) : '' ?>" 
                               min="09:00" max="17:00" step="1800" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason for Appointment</label>
                    <textarea class="form-control" id="reason" name="reason" rows="3" required><?= isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : '' ?></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Book Appointment</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="../../bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>