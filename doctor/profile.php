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
$error = '';

// Get doctor's current information
$stmt = $conn->prepare("SELECT u.*, d.specialization, d.qualification, d.experience_years 
                       FROM users u 
                       LEFT JOIN doctors d ON u.id = d.user_id 
                       WHERE u.id = ? AND u.role = 'doctor'");
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if (!$doctor) {
    die('Error: Doctor profile not found');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    $qualification = trim($_POST['qualification']);
    $experience_years = (int)$_POST['experience_years'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update users table
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param('sssi', $name, $email, $phone, $doctor_id);
        $stmt->execute();

        // Update doctors table
        $stmt = $conn->prepare("UPDATE doctors SET specialization = ?, qualification = ?, experience_years = ? WHERE user_id = ?");
        $stmt->bind_param('ssii', $specialization, $qualification, $experience_years, $doctor_id);
        $stmt->execute();

        $conn->commit();
        $message = 'Profile updated successfully!';
        
        // Refresh doctor's information
        $stmt = $conn->prepare("SELECT u.*, d.specialization, d.qualification, d.experience_years 
                               FROM users u 
                               LEFT JOIN doctors d ON u.id = d.user_id 
                               WHERE u.id = ? AND u.role = 'doctor'");
        $stmt->bind_param('i', $doctor_id);
        $stmt->execute();
        $doctor = $stmt->get_result()->fetch_assoc();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = 'Error updating profile. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <!-- <div class="sidebar">
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
                        <a class="nav-link" href="patients.php">Patients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medical-records.php">Medical Records</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="prescriptions.php">Prescriptions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">Profile</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <a class="nav-link" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav> -->
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
        <h2 class="mb-4">My Profile</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($doctor['phone']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization" 
                                   value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="qualification" class="form-label">Qualification</label>
                            <input type="text" class="form-control" id="qualification" name="qualification" 
                                   value="<?php echo htmlspecialchars($doctor['qualification']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="experience_years" class="form-label">Years of Experience</label>
                            <input type="number" class="form-control" id="experience_years" name="experience_years" 
                                   value="<?php echo (int)$doctor['experience_years']; ?>" required min="0" max="50">
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $experience_years = (int)$_POST['experience_years'];
    if ($experience_years < 0 || $experience_years > 50) {
        $errors[] = 'Years of experience must be between 0 and 50.';
    }
}
?>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>