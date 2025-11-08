<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    header('Location: /login.php');
    exit;
}
$conn = $GLOBALS['conn'];
$doctor_id = $_SESSION['user_id'];

// Get doctor's information
$sql = "SELECT * FROM users WHERE id = ? AND role = 'doctor'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

// Get doctor's appointments
$appointments = [];
$sql = "SELECT a.*, p.name as patient_name, p.email as patient_email 
        FROM appointments a 
        JOIN users p ON a.patient_id = p.id 
        WHERE a.doctor_id = ? 
        ORDER BY a.appointment_date DESC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

// Get doctor's patients
$patients = [];
$sql = "SELECT DISTINCT u.*, 
        (SELECT COUNT(*) FROM appointments WHERE patient_id = u.id AND doctor_id = ?) as visit_count,
        (SELECT appointment_date FROM appointments 
         WHERE patient_id = u.id AND doctor_id = ? 
         ORDER BY appointment_date DESC LIMIT 1) as last_visit
        FROM users u 
        JOIN appointments a ON u.id = a.patient_id 
        WHERE a.doctor_id = ? AND u.role = 'patient'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $doctor_id, $doctor_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $patients[] = $row;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];
    $bio = $_POST['bio'];
    
    $sql = "UPDATE users SET name = ?, email = ?, phone = ?, specialization = ?, bio = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssi', $name, $email, $phone, $specialization, $bio, $doctor_id);
    
    if ($stmt->execute()) {
        $success_message = 'Profile updated successfully!';
        // Refresh doctor info
        $doctor['name'] = $name;
        $doctor['email'] = $email;
        $doctor['phone'] = $phone;
        $doctor['specialization'] = $specialization;
        $doctor['bio'] = $bio;
    } else {
        $error_message = 'Failed to update profile. Please try again.';
    }
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
    <link href="/assets/css/admin.css" rel="stylesheet">
    <style>
         .navbar {
            transition: all 0.3s ease;
            padding: 0.5rem 0;
            background-color: white !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
            margin-left: -150px;
            
            transition: transform 0.3s ease;
        }

        .nav-link {
            color: #333 !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        /* Cart Badge */
        .cart-badge {
            position: relative;
        }
        </style>
</head>
<body>
<!-- <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="logo.png" alt="Trinetra Eye Care Logo"> <span class="text-primary"></span> 
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button> -->
            <!-- <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.html">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.php">Optical Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="shoplogin.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart me-1"></i>Cart</a></li> 
                </ul>
            </div> -->
        </div>
    </nav>
    <div class="container-fluid py-5 mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-md fa-3x text-primary mb-3"></i>
                            <h4><?php echo htmlspecialchars($doctor['name']); ?></h4>
                            <p class="text-muted"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                        </div>
                        <div class="list-group">
                            <a href="#dashboard" class="list-group-item list-group-item-action active" data-bs-toggle="tab">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                            <a href="#appointments" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                                <i class="fas fa-calendar-check me-2"></i> Appointments
                            </a>
                            <a href="#patients" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                                <i class="fas fa-users me-2"></i> My Patients
                            </a>
                            <a href="#profile" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                                <i class="fas fa-user-edit me-2"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white shadow">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-white">Total Patients</h6>
                                                <h2 class="mb-0"><?php echo count($patients); ?></h2>
                                            </div>
                                            <i class="fas fa-users fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white shadow">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-white">Today's Appointments</h6>
                                                <h2 class="mb-0">
                                                    <?php
                                                    $today = date('Y-m-d');
                                                    echo count(array_filter($appointments, function($apt) use ($today) {
                                                        return date('Y-m-d', strtotime($apt['appointment_date'])) === $today;
                                                    }));
                                                    ?>
                                                </h2>
                                            </div>
                                            <i class="fas fa-calendar-day fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white shadow">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-white">Pending Appointments</h6>
                                                <h2 class="mb-0">
                                                    <?php
                                                    echo count(array_filter($appointments, function($apt) {
                                                        return $apt['status'] === 'pending';
                                                    }));
                                                    ?>
                                                </h2>
                                            </div>
                                            <i class="fas fa-clock fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Appointments -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold">Recent Appointments</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Patient</th>
                                                <th>Date & Time</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($appointments, 0, 5) as $appointment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                                <td><?php echo date('d M Y H:i', strtotime($appointment['appointment_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $appointment['status'] === 'confirmed' ? 'success' : ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="prescriptions.php?appointment_id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-prescription"></i> Prescription
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

                    <!-- Appointments Tab -->
                    <div class="tab-pane fade" id="appointments">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold">All Appointments</h6>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary active">All</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary">Today</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary">Upcoming</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Patient</th>
                                                <th>Date & Time</th>
                                                <th>Status</th>
                                                <th>Contact</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($appointments as $appointment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                                <td><?php echo date('d M Y H:i', strtotime($appointment['appointment_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $appointment['status'] === 'confirmed' ? 'success' : ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="mailto:<?php echo $appointment['patient_email']; ?>">
                                                        <?php echo htmlspecialchars($appointment['patient_email']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="prescriptions.php?appointment_id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-prescription"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Patients Tab -->
                    <div class="tab-pane fade" id="patients">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold">My Patients</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Patient Name</th>
                                                <th>Email</th>
                                                <th>Total Visits</th>
                                                <th>Last Visit</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($patients as $patient): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($patient['name']); ?></td>
                                                <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                                <td><?php echo $patient['visit_count']; ?></td>
                                                <td><?php echo $patient['last_visit'] ? date('d M Y', strtotime($patient['last_visit'])) : 'N/A'; ?></td>
                                                <td>
                                                    <a href="patient-history.php?patient_id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-history"></i> View History
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

                    <!-- Profile Tab -->
                    <div class="tab-pane fade" id="profile">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold">Edit Profile</h6>
                            </div>
                            <div class="card-body">
                                <?php if (isset($success_message)): ?>
                                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                                <?php endif; ?>
                                <?php if (isset($error_message)): ?>
                                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                                <?php endif; ?>

                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Specialization</label>
                                        <input type="text" class="form-control" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Bio</label>
                                        <textarea class="form-control" name="bio" rows="4"><?php echo htmlspecialchars($doctor['bio']); ?></textarea>
                                    </div>
                                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>