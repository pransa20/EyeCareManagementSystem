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

// Get patient's prescriptions with doctor information
$prescriptions = [];
$sql = "SELECT p.*, d.name as doctor_name, doc.specialization 
        FROM prescriptions p 
        JOIN users d ON p.doctor_id = d.id 
        JOIN doctors doc ON d.id = doc.user_id
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
    <title>My Prescriptions - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        .prescription-card {
            border-radius: 10px;
            transition: transform 0.2s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .prescription-card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="logo.png" alt="Logo" height="60">
                        <h5 class="mt-2">Trinetra Eye Care</h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="book-appointment.php">
                                <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="medical-history.php">
                                <i class="fas fa-notes-medical me-2"></i>Medical History
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="prescriptions.php">
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
                    <h1 class="h2">My Prescriptions</h1>
                </div>

                <?php if (empty($prescriptions)): ?>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>No prescriptions found.
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($prescriptions as $prescription): ?>
                            <div class="col">
                                <div class="card prescription-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="card-title"><?php echo htmlspecialchars($prescription['medication_name']); ?></h5>
                                            <span class="badge bg-<?php echo $prescription['status'] === 'active' ? 'success' : ($prescription['status'] === 'completed' ? 'secondary' : 'danger'); ?> status-badge">
                                                <?php echo ucfirst(htmlspecialchars($prescription['status'])); ?>
                                            </span>
                                        </div>
                                        <p class="card-text">
                                            <strong>Doctor:</strong> <?php echo htmlspecialchars($prescription['doctor_name']); ?>
                                            (<?php echo htmlspecialchars($prescription['specialization']); ?>)
                                        </p>
                                        <p class="card-text">
                                            <strong>Dosage:</strong> <?php echo htmlspecialchars($prescription['dosage']); ?><br>
                                            <strong>Frequency:</strong> <?php echo htmlspecialchars($prescription['frequency']); ?><br>
                                            <strong>Duration:</strong> <?php echo htmlspecialchars($prescription['duration']); ?>
                                        </p>
                                        <?php if ($prescription['instructions']): ?>
                                            <p class="card-text">
                                                <strong>Instructions:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($prescription['instructions'])); ?>
                                            </p>
                                        <?php endif; ?>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                Prescribed on: <?php echo date('F j, Y', strtotime($prescription['prescribed_date'])); ?>
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>