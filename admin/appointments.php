<?php
require_once '../config/database.php';

// Verify database connection
if (!$conn || $conn->connect_error) {
    die('Database connection failed: ' . ($conn ? $conn->connect_error : 'Connection not established'));
}

// Set connection timeout and error handling
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 300);
$conn->set_charset('utf8mb4');
require_once '../includes/Auth.php';

// Initialize the session
session_start();

// Check if the user is logged in and is an admin, if not redirect to login page
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    header('Location: /login.php');
    exit;
}

// Get appointments from database
$sql = "SELECT a.*, p.name as patient_name, d.name as doctor_name, 
        (SELECT status_history FROM appointment_status_history WHERE appointment_id = a.id ORDER BY changed_at DESC LIMIT 1) as last_status_change
        FROM appointments a 
        LEFT JOIN users p ON a.patient_id = p.id 
        LEFT JOIN users d ON a.doctor_id = d.id";

// Add status filter if provided
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    $sql .= " WHERE a.status = '$status'";
}

$sql .= " ORDER BY a.appointment_date DESC, a.status ASC";

// Execute query with error handling
try {
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception('Database query error: ' . $conn->error);
    }
    $appointments = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die('Error retrieving appointments: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments Management - Trinetra Eye Care</title>
    <!-- <link href="bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">  -->
    <link href="../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
<link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin-nav.css" rel="stylesheet">
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
            margin-left: -100px;
            
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
        
            <!-- </div>
            Main content -->
           <!-- <main class="col-md-10 ms-sm-auto px-4 py-4"> -->
                <!-- Dashboard Header -->
                <!-- <div class="dashboard-header rounded-xl p-4 mb-4" style="background: linear-gradient(135deg, var(--primary-color) 0%, #2e59d9 100%); box-shadow: var(--shadow-lg);">
                    <div class="position-absolute top-0 end-0 mt-2 me-2">
                        <div class="d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-2">
                            <i class="fas fa-calendar-alt text-white me-2"></i>
                            <span class="text-white"><?php echo date('F j, Y'); ?></span>
                        </div> -->
    

            <div class="col-md-9">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Appointments Management</h6>
                        <div>
                            <button class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
                                <i class="fas fa-plus me-1"></i>Add Appointment
                            </button>
                            <div class="btn-group">
                                <a href="/admin/appointments.php" class="btn btn-primary btn-sm <?php echo !isset($_GET['status']) ? 'active' : ''; ?>">All</a>
                                <a href="/admin/appointments.php?status=pending" class="btn btn-primary btn-sm <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'active' : ''; ?>">Pending</a>
                                <a href="/admin/appointments.php?status=confirmed" class="btn btn-primary btn-sm <?php echo isset($_GET['status']) && $_GET['status'] === 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo $appointment['id']; ?></td>
                                        <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($appointment['appointment_date'])); ?></td>
                                        <td>
                                            <span id="status-badge-<?php echo $appointment['id']; ?>" class="badge bg-<?php
                                                $status_colors = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'success',
                                                    'cancelled' => 'danger',
                                                    'completed' => 'info'
                                                ];
                                                echo $status_colors[$appointment['status']] ?? 'secondary';
                                            ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Update Status
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><button class="dropdown-item status-update-btn" data-appointment-id="<?php echo $appointment['id']; ?>" data-status="pending">Pending</button></li>
                                                    <li><button class="dropdown-item status-update-btn" data-appointment-id="<?php echo $appointment['id']; ?>" data-status="confirmed">Confirm</button></li>
                                                    <li><button class="dropdown-item status-update-btn" data-appointment-id="<?php echo $appointment['id']; ?>" data-status="cancelled">Cancel</button></li>
                                                    <li><button class="dropdown-item status-update-btn" data-appointment-id="<?php echo $appointment['id']; ?>" data-status="completed">Complete</button></li>
                                                </ul>
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

                        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                        <script>
                        $(document).ready(function() {
                            // Add click event listener for status update buttons
                            $('.status-update-btn').on('click', function() {
                                const appointmentId = $(this).data('appointment-id');
                                const newStatus = $(this).data('status');
                                updateAppointmentStatus(appointmentId, newStatus);
                            });
                        });

                        function updateAppointmentStatus(appointmentId, newStatus) {
                            if (!confirm('Are you sure you want to update this appointment status to ' + newStatus + '?')) {
                                return;
                            }

                            $.ajax({
                                url: 'update-appointment-status.php',
                                method: 'POST',
                                data: {
                                    appointment_id: appointmentId,
                                    status: newStatus
                                },
                                success: function(response) {
                                    const data = JSON.parse(response);
                                    if (data.success) {
                                        alert('Appointment status updated successfully!');
                                        location.reload(); // Refresh the page to show updated status
                                    } else {
                                        alert('Failed to update appointment status: ' + data.message);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    alert('Error updating appointment status: ' + error);
                                }
                            });
                        }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Appointment Modal -->
    <div class="modal fade" id="addAppointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addAppointmentForm" method="post">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Patient</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="newPatientToggle">
                                    <label class="form-check-label" for="newPatientToggle">New Patient</label>
                                </div>
                            </div>
                            <div id="existingPatientSection">
                                <select class="form-select" id="patient" name="patient_id">
                                    <option value="">Select Patient</option>
                                    <?php
                                    $patients = $conn->query("SELECT id, name, email FROM users WHERE role = 'patient'")->fetch_all(MYSQLI_ASSOC);
                                    foreach ($patients as $patient) {
                                        echo "<option value='{$patient['id']}'>{$patient['name']} ({$patient['email']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div id="newPatientSection" style="display: none;">
                                <div class="mb-2">
                                    <input type="text" class="form-control" id="newPatientName" name="new_patient_name" placeholder="Patient Name">
                                </div>
                                <div class="mb-2">
                                    <input type="email" class="form-control" id="newPatientEmail" name="new_patient_email" placeholder="Email Address">
                                </div>
                                <div class="mb-2">
                                    <input type="tel" class="form-control" id="newPatientPhone" name="new_patient_phone" placeholder="Phone Number">
                                </div>
                                <div class="mb-2">
                                    <textarea class="form-control" id="newPatientAddress" name="new_patient_address" placeholder="Address" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="doctor" class="form-label">Doctor</label>
                            <select class="form-select" id="doctor" name="doctor_id" required>
                                <option value="">Select Doctor</option>
                                <?php
                                $doctors = $conn->query("SELECT id, name FROM users WHERE role = 'doctor'")->fetch_all(MYSQLI_ASSOC);
                                foreach ($doctors as $doctor) {
                                    echo "<option value='{$doctor['id']}'>{$doctor['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="appointment_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="appointment_time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitAppointment()">Add Appointment</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
     <script src="assets/js/appointment-status.js"></script>
    <script>
    // Toggle between existing and new patient forms
    document.getElementById('newPatientToggle').addEventListener('change', function() {
        const existingSection = document.getElementById('existingPatientSection');
        const newSection = document.getElementById('newPatientSection');
        const patientSelect = document.getElementById('patient');
        
        if (this.checked) {
            existingSection.style.display = 'none';
            newSection.style.display = 'block';
            patientSelect.removeAttribute('required');
        } else {
            existingSection.style.display = 'block';
            newSection.style.display = 'none';
            patientSelect.setAttribute('required', 'required');
        }
    });

    function submitAppointment() {
        const form = document.getElementById('addAppointmentForm');
        const isNewPatient = document.getElementById('newPatientToggle').checked;
        const formData = new FormData(form);
        
        // Add appointment date and time together
        const date = document.getElementById('appointment_date').value;
        const time = document.getElementById('appointment_time').value;
        formData.append('appointment_datetime', `${date} ${time}`);
        formData.append('is_new_patient', isNewPatient);

        // Submit the form
        fetch('add-appointment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Appointment added successfully!');
                location.reload();
            } else {
                alert('Failed to add appointment: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error adding appointment: ' + error);
        });
    }

    function viewAppointment(id) {
        // Implement view appointment details
        alert('View appointment ' + id);
    }

    function updateStatus(id, status) {
        if (confirm('Are you sure you want to update this appointment status?')) {
            // Implement status update logic
            alert('Update appointment ' + id + ' status to ' + status);
        }
    }

    function submitAppointment() {
        const form = document.getElementById('addAppointmentForm');
        const formData = new FormData(form);
        
        // Combine date and time
        const date = formData.get('appointment_date');
        const time = formData.get('appointment_time');
        formData.set('appointment_datetime', `${date} ${time}`);
        
        fetch('add-appointment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Appointment added successfully!');
                location.reload();
            } else {
                alert('Failed to add appointment: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error adding appointment: ' + error);
        });
    }
    </script>
</body>
</html>