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

// Handle form submission for new medical record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['diagnosis'])) {
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];
    $notes = $_POST['notes'];
    $visit_date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO medical_records (patient_id, doctor_id, diagnosis, prescription, notes, visit_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iissss', $patient_id, $doctor_id, $diagnosis, $treatment, $notes, $visit_date);

    if ($stmt->execute()) {
        $message = 'Medical record added successfully!';
    } else {
        $message = 'Error adding medical record.';
    }
}

// Get medical records
$records_query = $patient_id 
    ? "SELECT * FROM medical_records WHERE patient_id = ? AND doctor_id = ? ORDER BY visit_date DESC"
    : "SELECT mr.*, u.name as patient_name 
       FROM medical_records mr 
       JOIN users u ON mr.patient_id = u.id 
       WHERE mr.doctor_id = ? 
       ORDER BY mr.visit_date DESC";

$stmt = $conn->prepare($records_query);
if ($patient_id) {
    $stmt->bind_param('ii', $patient_id, $doctor_id);
} else {
    $stmt->bind_param('i', $doctor_id);
}
$stmt->execute();
$records = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Trinetra Eye Care</title>
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
                        <a class="nav-link" href="patients.php">Patients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="medical-records.php">Medical Records</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?php echo $patient_id ? "Medical Records - " . htmlspecialchars($patient_name) : "All Medical Records"; ?></h2>
            <?php if ($patient_id): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                    <i class="fas fa-plus"></i> Add New Record
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
                                <th>Diagnosis</th>
                                <th>Treatment</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($record = $records->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d', strtotime($record['record_date'])); ?></td>
                                    <?php if (!$patient_id): ?>
                                        <td><?php echo htmlspecialchars($record['patient_name']); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($record['diagnosis']); ?></td>
                                    <td><?php echo htmlspecialchars($record['treatment']); ?></td>
                                    <td><?php echo htmlspecialchars($record['notes']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($records->num_rows === 0): ?>
                                <tr>
                                    <td colspan="<?php echo $patient_id ? '4' : '5'; ?>" class="text-center">
                                        No medical records found.
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
    <!-- Add Record Modal -->
    <div class="modal fade" id="addRecordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Medical Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">Diagnosis</label>
                            <textarea class="form-control" id="diagnosis" name="diagnosis" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="treatment" class="form-label">Treatment</label>
                            <textarea class="form-control" id="treatment" name="treatment" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>