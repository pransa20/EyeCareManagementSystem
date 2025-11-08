<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Auth.php';

session_start();
$auth = new Auth();

// Check if user is logged in and is a doctor
if (!$auth->isLoggedIn() || !$auth->hasRole('doctor')) {
    header('Location: login.php');
    exit();
}

$doctor_id = $_SESSION['user_id'];
$message = '';

// Get appointments for the doctor
$stmt = $conn->prepare("SELECT a.*, p.name as patient_name, p.email as patient_email, p.phone as patient_phone 
                       FROM appointments a 
                       JOIN users p ON a.patient_id = p.id 
                       WHERE a.doctor_id = ? 
                       ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

// Update appointment status if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id']) && isset($_POST['status'])) {
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];
    
    $update_stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
    $update_stmt->bind_param('sii', $status, $appointment_id, $doctor_id);
    
    if ($update_stmt->execute()) {
        $message = 'Appointment status updated successfully!';
        // Refresh the page to show updated data
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $message = 'Error updating appointment status.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointments - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4">
                    <img src="logo.png" alt="Logo" height="60" class="sidebar-logo">
                    <h5 class="mt-2">Trinetra Eye Care</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">
                            <i class="fas fa-calendar-check me-2"></i>Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="patients.php">
                            <i class="fas fa-users me-2"></i>Patients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medical-records.php">
                            <i class="fas fa-notes-medical me-2"></i>Medical Records
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="prescriptions.php">
                            <i class="fas fa-prescription me-2"></i>Prescriptions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user-md me-2"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>

    <div class="main-content container mt-4">
        <h2 class="mb-4">My Appointments</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Patient Name</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($appointment = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d', strtotime($appointment['appointment_date'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                    <td>
                                        Email: <?php echo htmlspecialchars($appointment['patient_email']); ?><br>
                                        Phone: <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($appointment['status']) {
                                                'pending' => 'warning',
                                                'confirmed' => 'success',
                                                'cancelled' => 'danger',
                                                'completed' => 'info',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <select name="status" class="form-select form-select-sm d-inline-block w-auto me-2">
                                                <option value="pending" <?php echo $appointment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="cancelled" <?php echo $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                <option value="completed" <?php echo $appointment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($result->num_rows === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No appointments found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>