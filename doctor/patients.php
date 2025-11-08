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

// Get all patients who have appointments with this doctor
$stmt = $conn->prepare("SELECT DISTINCT u.*, 
                        (SELECT COUNT(*) FROM appointments WHERE patient_id = u.id AND doctor_id = ?) as appointment_count,
                        (SELECT appointment_date FROM appointments 
                         WHERE patient_id = u.id AND doctor_id = ? 
                         ORDER BY appointment_date DESC LIMIT 1) as last_visit
                       FROM users u 
                       JOIN appointments a ON u.id = a.patient_id 
                       WHERE a.doctor_id = ? AND u.role = 'patient'
                       ORDER BY last_visit DESC");
$stmt->bind_param('iii', $doctor_id, $doctor_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Patients - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="sidebar">
        <div class="sidebar-inner">
            <img src="logo.png" alt="Logo" class="sidebar-logo" height="60">
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="appointments.php">Appointments</a>
                <a class="nav-link" href="patients.php">Patients</a>
                <a class="nav-link" href="medical-records.php">Medical Records</a>
                <a class="nav-link" href="prescriptions.php">Prescriptions</a>
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </nav>
        </div>
    </div>
        <div class="container">
        <img src="logo.png" alt="Logo" height="60">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="patients.php">Patients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medical-records.php">Medical Records</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="prescriptions.php">Prescriptions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <a class="nav-link" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content container mt-4">
        <h2 class="mb-4">My Patients</h2>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact Information</th>
                                <th>Total Visits</th>
                                <th>Last Visit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($patient = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($patient['name']); ?></td>
                                    <td>
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($patient['email']); ?><br>
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($patient['phone']); ?>
                                    </td>
                                    <td><?php echo $patient['appointment_count']; ?></td>
                                    <td>
                                        <?php 
                                        echo $patient['last_visit'] 
                                            ? date('Y-m-d', strtotime($patient['last_visit'])) 
                                            : 'No visits';
                                        ?>
                                    </td>
                                    <td>
                                        <a href="medical-records.php?patient_id=<?php echo $patient['id']; ?>" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-notes-medical"></i> Medical Records
                                        </a>
                                        <a href="prescriptions.php?patient_id=<?php echo $patient['id']; ?>" 
                                           class="btn btn-success btn-sm mt-1">
                                            <i class="fas fa-prescription"></i> Prescriptions
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($result->num_rows === 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No patients found.</td>
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