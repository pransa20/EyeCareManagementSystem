Warning: Undefined array key "specialization" in C:\xampp\htdocs\project_eye_care\doctor\dashboard.php on line 189<?php
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
$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : null;
$message = '';

// Get patient details if patient_id is provided
$patient_name = '';
if ($patient_id) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ? AND id IN (SELECT DISTINCT patient_id FROM appointments WHERE doctor_id = ?)");
    $stmt->bind_param('ii', $patient_id, $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($patient = $result->fetch_assoc()) {
        $patient_name = $patient['name'];
    } else {
        header('Location: patients.php');
        exit();
    }
}

// Handle form submission for new prescription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['medication'])) {
    $medication = $_POST['medication'];
    $dosage = $_POST['dosage'];
    $frequency = $_POST['frequency'];
    $duration = $_POST['duration'];
    $instructions = $_POST['instructions'];
    $prescribed_date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO prescriptions (patient_id, doctor_id, medication_name, dosage, frequency, duration, instructions, prescribed_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iissssss', $patient_id, $doctor_id, $medication, $dosage, $frequency, $duration, $instructions, $prescribed_date);

    if ($stmt->execute()) {
        $message = 'Prescription added successfully!';
    } else {
        $message = 'Error adding prescription.';
    }
}

// Get prescriptions
$prescriptions_query = $patient_id 
    ? "SELECT * FROM prescriptions WHERE patient_id = ? AND doctor_id = ? ORDER BY prescribed_date DESC"
    : "SELECT p.*, u.name as patient_name 
       FROM prescriptions p 
       JOIN users u ON p.patient_id = u.id 
       WHERE p.doctor_id = ? 
       ORDER BY p.prescribed_date DESC";

$stmt = $conn->prepare($prescriptions_query);
if ($patient_id) {
    $stmt->bind_param('ii', $patient_id, $doctor_id);
} else {
    $stmt->bind_param('i', $doctor_id);
}
$stmt->execute();
$prescriptions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions - Trinetra Eye Care</title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?php echo $patient_id ? "Prescriptions - " . htmlspecialchars($patient_name) : "All Prescriptions"; ?></h2>
            <?php if ($patient_id): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPrescriptionModal">
                    <i class="fas fa-plus"></i> Add New Prescription
                </button>
            <?php endif; ?>
        </div>

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
                                <?php if (!$patient_id): ?><th>Patient</th><?php endif; ?>
                                <th>Medication</th>
                                <th>Dosage</th>
                                <th>Frequency</th>
                                <th>Duration</th>
                                <th>Instructions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($prescription = $prescriptions->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d', strtotime($prescription['prescribed_date'])); ?></td>
                                    <?php if (!$patient_id): ?>
                                        <td><?php echo htmlspecialchars($prescription['patient_name']); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($prescription['medication_name']); ?></td>
                                    <td><?php echo htmlspecialchars($prescription['dosage']); ?></td>
                                    <td><?php echo htmlspecialchars($prescription['frequency']); ?></td>
                                    <td><?php echo htmlspecialchars($prescription['duration']); ?></td>
                                    <td><?php echo htmlspecialchars($prescription['instructions']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($prescriptions->num_rows === 0): ?>
                                <tr>
                                    <td colspan="<?php echo $patient_id ? '6' : '7'; ?>" class="text-center">
                                        No prescriptions found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if ($patient_id): ?>
    <!-- Add Prescription Modal -->
    <div class="modal fade" id="addPrescriptionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Prescription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="medication" class="form-label">Medication</label>
                            <input type="text" class="form-control" id="medication" name="medication" required>
                        </div>
                        <div class="mb-3">
                            <label for="dosage" class="form-label">Dosage</label>
                            <input type="text" class="form-control" id="dosage" name="dosage" required>
                        </div>
                        <div class="mb-3">
                            <label for="frequency" class="form-label">Frequency</label>
                            <input type="text" class="form-control" id="frequency" name="frequency" required>
                        </div>
                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration</label>
                            <input type="text" class="form-control" id="duration" name="duration" required>
                        </div>
                        <div class="mb-3">
                            <label for="instructions" class="form-label">Special Instructions</label>
                            <textarea class="form-control" id="instructions" name="instructions"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Prescription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>