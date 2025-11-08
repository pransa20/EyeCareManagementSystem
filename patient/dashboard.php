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

// Get patient's appointments
$appointments = [];
$sql = "SELECT a.*, d.name as doctor_name, doc.specialization 
        FROM appointments a 
        JOIN users d ON a.doctor_id = d.id 
        JOIN doctors doc ON d.id = doc.user_id
        WHERE a.patient_id = ? 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

// Get patient's medical history
$medical_history = [];
$sql = "SELECT mr.*, d.name as doctor_name 
        FROM medical_records mr 
        JOIN users d ON mr.doctor_id = d.id 
        WHERE mr.patient_id = ? 
        ORDER BY mr.visit_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $medical_history[] = $row;
}

// Get patient's prescriptions
$prescriptions = [];
$sql = "SELECT p.*, d.name as doctor_name 
        FROM prescriptions p 
        JOIN users d ON p.doctor_id = d.id 
        WHERE p.patient_id = ? 
        ORDER BY p.prescribed_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $prescriptions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        .dashboard-card {
            border-radius: 15px;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2rem;
            opacity: 0.8;
        }
        .appointment-list {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar Navigation -->
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="logo.png" alt="Logo" height="60">
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
                            <a class="nav-link" href="medical-history.php">
                                <i class="fas fa-notes-medical me-2"></i>Medical History
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="prescriptions.php">
                                <i class="fas fa-prescription me-2"></i>Prescriptions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                        </li>
 
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="book-appointment.php" class="btn btn-primary">
                            <i class="fas fa-calendar-plus me-2"></i>Book New Appointment
                        </a>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card dashboard-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Upcoming Appointments</h6>
                                        <h2 class="mt-2 mb-0"><?php echo count(array_filter($appointments, function($a) { return strtotime($a['appointment_date']) >= strtotime('today'); })); ?></h2>
                                    </div>
                                    <i class="fas fa-calendar stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dashboard-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Medical Records</h6>
                                        <h2 class="mt-2 mb-0"><?php echo count($medical_history); ?></h2>
                                    </div>
                                    <i class="fas fa-notes-medical stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dashboard-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Prescriptions</h6>
                                        <h2 class="mt-2 mb-0"><?php echo count($prescriptions); ?></h2>
                                    </div>
                                    <i class="fas fa-prescription stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointments Section -->
                <div class="card dashboard-card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Recent Appointments</h5>
                    </div>
                    <div class="card-body appointment-list">
                        <?php if (empty($appointments)): ?>
                            <p class="text-muted">No appointments found.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($appointments, 0, 5) as $appointment): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($appointment['doctor_name']); ?></h6>
                                                <p class="mb-1 text-muted small"><?php echo htmlspecialchars($appointment['specialization']); ?></p>
                                                <small>
                                                    <i class="far fa-calendar me-1"></i>
                                                    <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?>
                                                    at <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-<?php echo $appointment['status'] === 'confirmed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="appointments.php" class="btn btn-outline-primary btn-sm">View All Appointments</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Medical History Section -->
                <div class="card dashboard-card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Recent Medical History</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($medical_history)): ?>
                            <p class="text-muted">No medical records found.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($medical_history, 0, 3) as $record): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($record['diagnosis']); ?></h6>
                                                <p class="mb-1 small"><?php echo htmlspecialchars($record['test_results']); ?></p>
                                                <small class="text-muted">
                                                    <i class="far fa-user-md me-1"></i>Dr. <?php echo htmlspecialchars($record['doctor_name']); ?> |
                                                    <i class="far fa-calendar me-1"></i><?php echo date('F j, Y', strtotime($record['visit_date'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="medical-history.php" class="btn btn-outline-primary btn-sm">View Complete Medical History</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>