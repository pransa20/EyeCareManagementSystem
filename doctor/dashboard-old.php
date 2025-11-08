<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('doctor')) {
    header('Location: /login.php');
    exit;
}

$doctor = $auth->getCurrentUser();
$doctor_id = $doctor['id'];

// Get doctor's statistics
$stats = [
    'total_patients' => 0,
    'today_appointments' => 0,
    'pending_appointments' => 0,
    'total_consultations' => 0,
    'monthly_consultations' => 0
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
    SUM(CASE WHEN appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as monthly_consultations
    FROM appointments WHERE doctor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['total_consultations'] = $row['total_consultations'];
$stats['today_appointments'] = $row['today_appointments'];
$stats['pending_appointments'] = $row['pending_appointments'];
$stats['monthly_consultations'] = $row['monthly_consultations'];

// Get recent appointments
$recent_appointments = [];
$sql = "SELECT a.*, p.name as patient_name, p.phone as patient_phone, p.email as patient_email
        FROM appointments a 
        JOIN users p ON a.patient_id = p.id 
        WHERE a.doctor_id = ? 
        ORDER BY a.appointment_date DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_appointments[] = $row;
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
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        .stat-card {
            border-radius: 15px;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="text-center mb-4">
                    <img src="/assets/images/trinetra.png" alt="Logo" height="50">
                    <h5 class="mt-2 mb-0">Trinetra Eye Care</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="/doctor/dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/doctor/appointments.php">
                            <i class="fas fa-calendar-check"></i>Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/doctor/medical-record.php">
                            <i class="fas fa-notes-medical"></i>Medical Records
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/doctor/profile.php">
                            <i class="fas fa-user-md"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="/doctor/logout.php">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Welcome Dr. <?php echo htmlspecialchars($doctor['name']); ?></h2>
                    <div class="user-info d-flex align-items-center">
                        <span class="me-3"><?php echo htmlspecialchars($doctor['specialization']); ?></span>
                        <img src="/assets/images/avatar.png" alt="User" class="rounded-circle" width="40">
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white shadow stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white">Total Patients</h6>
                                        <h2 class="mb-0"><?php echo $stats['total_patients']; ?></h2>
                                    </div>
                                    <i class="fas fa-users stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white shadow stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white">Today's Appointments</h6>
                                        <h2 class="mb-0"><?php echo $stats['today_appointments']; ?></h2>
                                    </div>
                                    <i class="fas fa-calendar-day stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white shadow stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white">Pending Appointments</h6>
                                        <h2 class="mb-0"><?php echo $stats['pending_appointments']; ?></h2>
                                    </div>
                                    <i class="fas fa-clock stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white shadow stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white">Monthly Consultations</h6>
                                        <h2 class="mb-0"><?php echo $stats['monthly_consultations']; ?></h2>
                                    </div>
                                    <i class="fas fa-stethoscope stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row">
                    <!-- Recent Appointments -->
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Appointments</h6>
                                <a href="appointments.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body recent-activity">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Patient</th>
                                                <th>Date & Time</th>
                                                <th>Contact</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_appointments as $appointment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                                <td>
                                                    <?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($appointment['patient_email']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $appointment['status'] == 'pending' ? 'warning' : ($appointment['status'] == 'confirmed' ? 'success' : 'danger'); ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Medical Records -->
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Medical Records</h6>
                                <a href="medical-record.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body recent-activity">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Patient</th>
                                                <th>Visit Date</th>
                                                <th>Diagnosis</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_records as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['patient_name']); ?></td>
                                                <td><?php echo date('d M Y', strtotime($record['visit_date'])); ?></td>
                                                <td>
                                                    <span class="text-truncate d-inline-block" style="max-width: 150px;">
                                                        <?php echo htmlspecialchars($record['diagnosis']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="medical-record.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
});

// Get upcoming appointments (excluding today)
$upcoming_appointments = array_filter($appointments, function($apt) {
    return $apt['appointment_date'] > date('Y-m-d');
});
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-md me-1"></i>Dr. <?php echo htmlspecialchars($user['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="../doctor/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row g-4">
            <!-- Today's Appointments -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Today's Appointments</h5>
                        <?php if (empty($today_appointments)): ?>
                            <p class="text-muted">No appointments scheduled for today.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($today_appointments as $appointment): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($appointment['patient_name']); ?></h6>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-phone me-1"></i>
                                                    <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php echo $appointment['status'] === 'confirmed' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                                <div class="mt-2">
                                                    <a href="medical-record.php?patient_id=<?php echo $appointment['patient_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-notes-medical"></i> Medical Record
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Upcoming Appointments</h5>
                        <?php if (empty($upcoming_appointments)): ?>
                            <p class="text-muted">No upcoming appointments scheduled.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($upcoming_appointments as $appointment): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($appointment['patient_name']); ?></h6>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?>
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php echo $appointment['status'] === 'confirmed' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                                <?php if ($appointment['status'] === 'pending'): ?>
                                                    <div class="mt-2">
                                                        <a href="confirm-appointment.php?id=<?php echo $appointment['id']; ?>" 
                                                           class="btn btn-sm btn-success">
                                                            <i class="fas fa-check"></i> Confirm
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>