<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
$conn = $GLOBALS['conn'];
$success_message = $error_message = '';

// Ensure medical_records table exists
$sql = "CREATE TABLE IF NOT EXISTS medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT,
    visit_date DATE NOT NULL,
    diagnosis TEXT NOT NULL,
    prescription TEXT,
    test_results TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
)";
$conn->query($sql);

// Get all medical records with patient and doctor details
$medical_records = [];
$sql = "SELECT mr.*, p.name as patient_name, p.email as patient_email, 
               d.name as doctor_name, doc.specialization 
        FROM medical_records mr 
        LEFT JOIN users p ON mr.patient_id = p.id 
        LEFT JOIN users d ON mr.doctor_id = d.id 
        LEFT JOIN doctors doc ON mr.doctor_id = doc.user_id 
        WHERE p.id IS NOT NULL 
        ORDER BY mr.visit_date DESC";
$result = $conn->query($sql);
if ($result === false) {
    $error_message = 'Database error: ' . $conn->error;
} else {
    while ($row = $result->fetch_assoc()) {
        $medical_records[] = $row;
    }
}

// Get all patients for filtering
$patients = [];
$sql = "SELECT id, name, email FROM users WHERE role = 'patient'";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $patients[] = $row;
}

// Get all doctors for filtering
$doctors = [];
$sql = "SELECT u.id, u.name, d.specialization FROM users u LEFT JOIN doctors d ON u.id = d.user_id WHERE u.role = 'doctor'";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Trinetra Eye Care</title>
    <link href="../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin-nav.css" rel="stylesheet">
    <style>
         .navbar {
            transition: all 0.3s ease;
            padding: 0;
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

        </style>
</head>
<body>
            
<div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar" style="background: var(--sidebar-dark); box-shadow: var(--shadow-sm); border-right: 1px solid var(--card-border);">
                <div class="text-center mb-4 py-4">
                    <img src="logo.png" alt="Logo" height="50" class="mb-3">
                    <h5 class="fw-bold mb-0" style="color: var(--text-primary);">Trinetra Eye Care</h5>
                    <p class="text-muted small mb-0">Admin Dashboard</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="doctors.php">
                            <i class="fas fa-user-md"></i>Doctors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="patients.php">
                            <i class="fas fa-users"></i>Patients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">
                            <i class="fas fa-calendar-check"></i>Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medical-history.php">
                            <i class="fas fa-notes-medical"></i>Medical History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-glasses"></i>Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-cart"></i>Orders
                        </a>
                    </li>
                    
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-4 py-4">
                <!-- Dashboard Header -->
                <div class="dashboard-header rounded-xl p-4 mb-4" style="background: linear-gradient(135deg, var(--primary-color) 0%, #2e59d9 100%); box-shadow: var(--shadow-lg);">
                    <div class="position-absolute top-0 end-0 mt-2 me-2">
                        <div class="d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-2">
                            <i class="fas fa-calendar-alt text-white me-2"></i>
                            <span class="text-white"><?php echo date('F j, Y'); ?></span>
                        </div>
                       
                    </div>
        

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="card-title mb-0">Medical History Management</h2>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            <span class="text-muted"><?php echo date('F j, Y'); ?></span>
                        </div>
                    </div>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <!-- DataTables CSS -->
                    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
                    <link href="css/medical-history.css" rel="stylesheet">
                    <!-- DataTables JavaScript -->
                    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
                    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <select class="form-select" id="filterPatient">
                                <option value="">Filter by Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['id']; ?>">
                                        <?php echo htmlspecialchars($patient['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="filterDoctor">
                                <option value="">Filter by Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['id']; ?>">
                                        <?php echo htmlspecialchars($doctor['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="date" class="form-control" id="filterDate" placeholder="Filter by Date">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="medicalHistoryTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Diagnosis</th>
                                    <th>Prescription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medical_records as $record): ?>
                                    <tr>
                                        <td data-sort="<?php echo $record['visit_date']; ?>"><?php echo date('Y-m-d', strtotime($record['visit_date'])); ?></td>
                                        <td data-patient-id="<?php echo $record['patient_id']; ?>">
                                            <?php echo htmlspecialchars($record['patient_name']); ?><br>
                                            <small class="text-muted"><?php echo $record['patient_email']; ?></small>
                                        </td>
                                        <td data-doctor-id="<?php echo $record['doctor_id']; ?>">
                                            <?php if ($record['doctor_name']): ?>
                                                <?php echo htmlspecialchars($record['doctor_name']); ?><br>
                                                <small class="text-muted"><?php echo $record['specialization']; ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Self Reported</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($record['diagnosis']); ?></td>
                                        <td><?php echo htmlspecialchars($record['prescription']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-record" data-id="<?php echo $record['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
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
</div>

<!-- View Record Modal -->
<div class="modal fade" id="viewRecordModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Medical Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Record details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable with Bootstrap 5 styling
    var table = $('#medicalHistoryTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            search: "Search records:",
            lengthMenu: "Show _MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ records"
        }
    });

    // Custom filtering function
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var patientId = $('#filterPatient').val();
        var doctorId = $('#filterDoctor').val();
        var date = $('#filterDate').val();

        var row = table.row(dataIndex).data();
        var match = true;

        if (patientId && row[1].indexOf('data-patient-id="' + patientId + '"') === -1) match = false;
        if (doctorId && row[2].indexOf('data-doctor-id="' + doctorId + '"') === -1) match = false;
        if (date && row[0] !== date) match = false;

        return match;
    });

    // Filter handlers with debounce
    var filterTimeout;
    $('#filterPatient, #filterDoctor, #filterDate').on('change', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            table.draw();
        }, 300);
    });

    // View record handler
    $(document).on('click', '.view-record', function() {
        var recordId = $(this).data('id');
        
        // Load record details via AJAX
        $.ajax({
            url: '/api/get-medical-record.php',
            method: 'GET',
            data: { id: recordId },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    var record = response.data;
                    var modalBody = $('#viewRecordModal .modal-body');
                    modalBody.html(`
                        <div class="mb-3">
                            <h6 class="fw-bold">Patient</h6>
                            <p>${record.patient_name}</p>
                            <small class="text-muted">${record.patient_email}</small>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">Doctor</h6>
                            <p>${record.doctor_name || 'Self Reported'}</p>
                            ${record.specialization ? `<small class="text-muted">${record.specialization}</small>` : ''}
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">Visit Date</h6>
                            <p>${record.visit_date}</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">Diagnosis</h6>
                            <p>${record.diagnosis}</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">Prescription</h6>
                            <p>${record.prescription || 'None'}</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">Test Results</h6>
                            <p>${record.test_results || 'None'}</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">Notes</h6>
                            <p>${record.notes || 'None'}</p>
                        </div>
                    `);
                    
                    var modal = new bootstrap.Modal(document.getElementById('viewRecordModal'));
                    modal.show();
                } else {
                    alert('Error loading record: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('AJAX Error: ' + error);
            }
        });
    });
});
        // Load record details via AJAX
    });
</script>
</body>
</html>