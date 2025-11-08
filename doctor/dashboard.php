<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || $_SESSION['user_role'] !== 'doctor') {
    header('Location: login.php');
    exit;
}

// Get database connection
$conn = $GLOBALS['conn'];

// Get doctor's information including specialization
$stmt = $conn->prepare("SELECT u.id, u.name, u.role, d.specialization FROM users u INNER JOIN doctors d ON u.id = d.user_id WHERE u.id = ? AND u.role = 'doctor'");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

if (!$doctor) {
    header('Location: login.php');
    exit;
}

// Set default specialization if not set
if (!isset($doctor['specialization']) || empty($doctor['specialization'])) {
    $doctor['specialization'] = 'General Eye Care';
}

// Remove the duplicate redirect - this was causing the issue
// header('Location: login.php');
// exit;

// Remove duplicate doctor assignment - this was overwriting the database data
// $doctor = $auth->getCurrentUser();
$doctor_id = $doctor['id'];

// Initialize statistics array
$stats = [
    'total_patients' => 0,
    'today_appointments' => 0,
    'pending_appointments' => 0,
    'total_consultations' => 0,
    'monthly_consultations' => 0,
    'completed_appointments' => 0
];

// Get total unique patients
$sql = "SELECT COUNT(DISTINCT patient_id) as count FROM appointments WHERE doctor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_patients'] = $result->fetch_assoc()['count'];

// Get appointment statistics
$sql = "SELECT 
    COUNT(*) as total_consultations,
    SUM(CASE WHEN DATE(appointment_date) = CURDATE() THEN 1 ELSE 0 END) as today_appointments,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_appointments,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
    SUM(CASE WHEN appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as monthly_consultations
    FROM appointments WHERE doctor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Assign statistics with null checking
$stats['total_consultations'] = $row['total_consultations'] ?? 0;
$stats['today_appointments'] = $row['today_appointments'] ?? 0;
$stats['pending_appointments'] = $row['pending_appointments'] ?? 0;
$stats['completed_appointments'] = $row['completed_appointments'] ?? 0;
$stats['monthly_consultations'] = $row['monthly_consultations'] ?? 0;

// Get today's appointments
$today_appointments = [];
$sql = "SELECT a.*, p.name as patient_name, p.phone as patient_phone, p.email as patient_email
        FROM appointments a 
        JOIN users p ON a.patient_id = p.id 
        WHERE a.doctor_id = ? AND DATE(a.appointment_date) = CURDATE()
        ORDER BY a.appointment_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $today_appointments[] = $row;
}

// Get pending appointments
$pending_appointments = [];
$sql = "SELECT a.*, p.name as patient_name, p.phone as patient_phone, p.email as patient_email
        FROM appointments a 
        JOIN users p ON a.patient_id = p.id 
        WHERE a.doctor_id = ? AND a.status = 'pending'
        ORDER BY a.appointment_date ASC, a.appointment_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pending_appointments[] = $row;
}

// Get recent medical records
$recent_records = [];
$sql = "SELECT mr.*, p.name as patient_name 
        FROM medical_records mr 
        JOIN users p ON mr.patient_id = p.id 
        WHERE mr.doctor_id = ? 
        ORDER BY mr.visit_date DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_records[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Trinetra Eye Care</title>
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
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .appointment-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            font-size: 0.8rem;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #fff;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4 pt-3">
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

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div>
                        <h1 class="h2">Welcome  <?php echo isset($doctor['name']) ? htmlspecialchars($doctor['name']) : 'Doctor'; ?></h1>
                        <p class="text-muted"><?php echo isset($doctor['specialization']) ? htmlspecialchars($doctor['specialization']) : 'Eye Specialist'; ?></p>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="appointments.php" class="btn btn-primary me-2">
                            <i class="fas fa-calendar-plus me-2"></i>Manage Appointments
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Total Patients</h6>
                                        <h2 class="mt-2 mb-0"><?php echo $stats['total_patients']; ?></h2>
                                    </div>
                                    <i class="fas fa-users stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Today's Appointments</h6>
                                        <h2 class="mt-2 mb-0"><?php echo $stats['today_appointments']; ?></h2>
                                    </div>
                                    <i class="fas fa-calendar-day stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Pending Appointments</h6>
                                        <h2 class="mt-2 mb-0"><?php echo $stats['pending_appointments']; ?></h2>
                                    </div>
                                    <i class="fas fa-clock stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Completed Appointments</h6>
                                        <h2 class="mt-2 mb-0"><?php echo $stats['completed_appointments']; ?></h2>
                                    </div>
                                    <i class="fas fa-check-circle stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Appointments -->
                <div class="card dashboard-card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Today's Appointments</h5>
                        <a href="appointments.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body appointment-list">
                        <?php if (empty($today_appointments)): ?>
                            <p class="text-muted">No appointments scheduled for today.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Patient</th>
                                            <th>Contact</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($today_appointments as $appointment): ?>
                                            <tr>
                                                <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                                <td>
                                                    <small>
                                                        <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($appointment['patient_phone']); ?><br>
                                                        <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($appointment['patient_email']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $appointment['status'] === 'confirmed' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="medical-record.php?patient_id=<?php echo $appointment['patient_id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="fas fa-notes-medical"></i>
                                                    </a>
                                                    <a href="prescriptions.php?patient_id=<?php echo $appointment['patient_id']; ?>" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-prescription"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Medical Records -->
                <div class="card dashboard-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Medical Records</h5>
                        <a href="medical-records.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_records)): ?>
                            <p class="text-muted">No recent medical records found.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_records as $record): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($record['patient_name']); ?></h6>
                                                <p class="mb-1 small"><?php echo htmlspecialchars($record['diagnosis']); ?></p>
                                                <small class="text-muted">
                                                    <i class="far fa-calendar me-1"></i><?php echo date('F j, Y', strtotime($record['visit_date'])); ?>
                                                </small>
                                            </div>
                                            <a href="medical-record.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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