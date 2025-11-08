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
$success_message = $error_message = '';

// Handle form submission for adding medical history
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_record'])) {
    $diagnosis = $_POST['diagnosis'] ?? '';
    $symptoms = $_POST['symptoms'] ?? '';
    $medications = $_POST['medications'] ?? '';
    $allergies = $_POST['allergies'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($diagnosis) || empty($symptoms)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // Get a random active doctor for the record
        $sql_doctor = "SELECT id FROM users WHERE role = 'doctor' AND status = 'active' LIMIT 1";
        $doctor_result = $conn->query($sql_doctor);
        $doctor = $doctor_result->fetch_assoc();
        
        if (!$doctor) {
            $error_message = 'No active doctor found in the system.';
        } else {
            $sql = "INSERT INTO medical_records (patient_id, doctor_id, visit_date, diagnosis, test_results, prescription, notes) 
                    VALUES (?, ?, CURDATE(), ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iissss', $user['id'], $doctor['id'], $diagnosis, $symptoms, $medications, $notes);
        
            if ($stmt->execute()) {
                $success_message = 'Medical record added successfully!';
            } else {
                $error_message = 'Failed to add medical record. Please try again.';
            }
        }
    }
}

// Get patient's medical history
$medical_history = [];
$sql = "SELECT * FROM medical_records WHERE patient_id = ? ORDER BY visit_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user['id']);
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
    <title>Medical History - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
        
                        <img src="logo.png" alt="Logo" height="60">
                        
                    
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['name']); ?>
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
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add Medical History</h5>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="diagnosis" class="form-label">Diagnosis/Condition*</label>
                                <input type="text" class="form-control" id="diagnosis" name="diagnosis" required>
                            </div>
                            <div class="mb-3">
                                <label for="symptoms" class="form-label">Symptoms/Issues*</label>
                                <textarea class="form-control" id="symptoms" name="symptoms" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="medications" class="form-label">Current Medications</label>
                                <textarea class="form-control" id="medications" name="medications" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="allergies" class="form-label">Allergies</label>
                                <input type="text" class="form-control" id="allergies" name="allergies">
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                            </div>
                            <button type="submit" name="add_record" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>Add Record
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Medical History</h5>
                        <?php if (empty($medical_history)): ?>
                            <p class="text-muted">No medical records found.</p>
                        <?php else: ?>
                            <div class="accordion" id="medicalHistoryAccordion">
                                <?php foreach ($medical_history as $index => $record): ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#record<?php echo $index; ?>">
                                                <?php echo date('F j, Y', strtotime($record['visit_date'])); ?> - <?php echo htmlspecialchars($record['diagnosis']); ?>
                                            </button>
                                        </h2>
                                        <div id="record<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>">
                                            <div class="accordion-body">
                                                <p><strong>Symptoms/Issues:</strong><br><?php echo nl2br(htmlspecialchars($record['test_results'])); ?></p>
                                                <?php if (!empty($record['prescription'])): ?>
                                                    <p><strong>Medications:</strong><br><?php echo nl2br(htmlspecialchars($record['prescription'])); ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($record['notes'])): ?>
                                                    <p><strong>Notes:</strong><br><?php echo nl2br(htmlspecialchars($record['notes'])); ?></p>
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