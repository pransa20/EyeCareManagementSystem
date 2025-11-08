<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('doctor')) {
    header('Location: /login.php');
    exit;
}

$conn = $GLOBALS['conn'];
$user = $auth->getCurrentUser();
$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

// Verify patient exists and get their info
$patient = null;
$sql = "SELECT * FROM users WHERE id = ? AND role = 'patient'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: dashboard.php');
    exit;
}
$patient = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_record'])) {
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];
    $prescription = $_POST['prescription'];
    $notes = $_POST['notes'];
    
    $sql = "INSERT INTO medical_records (patient_id, doctor_id, diagnosis, test_results, prescription, notes, visit_date) 
            VALUES (?, ?, ?, ?, ?, ?, CURDATE())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iissss', $patient_id, $user['id'], $diagnosis, $test_results, $prescription, $notes);
    
    if ($stmt->execute()) {
        $success_message = 'Medical record added successfully!';
    } else {
        $error_message = 'Failed to add medical record. Please try again.';
    }
}

// Get patient's medical history
$medical_history = [];
$sql = "SELECT mr.*, u.name as doctor_name 
        FROM medical_records mr 
        JOIN users u ON mr.doctor_id = u.id 
        WHERE mr.patient_id = ? 
        ORDER BY mr.visit_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $medical_history[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Record - <?php echo htmlspecialchars($patient['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">
            <img src="logo.png" alt="Logo" height="60">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-chevron-left me-1"></i>Back to Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-md me-1"></i>Dr. <?php echo htmlspecialchars($user['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row g-4">
            <!-- Patient Info -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Patient Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($patient['name']); ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
                                <p class="mb-1"><strong>Registered:</strong> <?php echo date('F j, Y', strtotime($patient['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Medical Record Form -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add Medical Record</h5>
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="diagnosis" class="form-label">Diagnosis</label>
                                <input type="text" class="form-control" id="diagnosis" name="diagnosis" required>
                            </div>
                            <div class="mb-3">
                                <label for="treatment" class="form-label">Treatment</label>
                                <textarea class="form-control" id="treatment" name="treatment" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="prescription" class="form-label">Prescription</label>
                                <textarea class="form-control" id="prescription" name="prescription" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                            </div>
                            <button type="submit" name="add_record" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add Record
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Medical History -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Medical History</h5>
                        <?php if (empty($medical_history)): ?>
                            <p class="text-muted">No medical records found.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($medical_history as $record): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($record['diagnosis']); ?></h6>
                                                <p class="mb-1 small"><?php echo htmlspecialchars($record['treatment']); ?></p>
                                                <?php if ($record['prescription']): ?>
                                                    <p class="mb-1 small text-primary">
                                                        <i class="fas fa-prescription me-1"></i>
                                                        <?php echo htmlspecialchars($record['prescription']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if ($record['notes']): ?>
                                                    <p class="mb-1 small text-muted">
                                                        <i class="fas fa-sticky-note me-1"></i>
                                                        <?php echo htmlspecialchars($record['notes']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-user-md me-1"></i>Dr. <?php echo htmlspecialchars($record['doctor_name']); ?>
                                                    <i class="fas fa-calendar ms-2 me-1"></i><?php echo date('F j, Y', strtotime($record['visit_date'])); ?>
                                                </small>
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