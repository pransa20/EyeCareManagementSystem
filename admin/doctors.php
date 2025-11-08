<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    header('Location: /admin/login.php');
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
$conn = Database::getInstance()->getConnection();
$error = $success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_doctor'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $specialization = trim($_POST['specialization']);
        $phone = trim($_POST['phone']);
        $status = trim($_POST['status']);
        $password = trim($_POST['password']);

        if (empty($name) || empty($email) || empty($specialization) || empty($phone) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } elseif ($auth->emailExists($email)) {
            $error = 'This email address is already registered.';
        } else {
            // First create the user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'doctor';
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssss', $name, $email, $hashed_password, $role, $phone, $status);

            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                // Then add doctor specialization
                $stmt = $conn->prepare("INSERT INTO doctors (user_id, specialization) VALUES (?, ?)");
                $stmt->bind_param('is', $user_id, $specialization);
                
                if ($stmt->execute()) {
                    $success = 'Doctor added successfully!';
                } else {
                    $error = 'Error adding doctor: ' . $conn->error;
                }
            }
        }
    } elseif (isset($_POST['update_doctor'])) {
        $id = $_POST['doctor_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $specialization = trim($_POST['specialization']);
        $phone = trim($_POST['phone']);
        $status = trim($_POST['status']);

        if (empty($name) || empty($email) || empty($specialization) || empty($phone)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } else {
            $stmt = $conn->prepare("UPDATE users u LEFT JOIN doctors d ON u.id = d.user_id 
                           SET u.name = ?, u.email = ?, d.specialization = ?, u.phone = ?, u.status = ? 
                           WHERE u.id = ?");
            $stmt->bind_param('sssss', $name, $email, $specialization, $phone, $status, $id);
            
            if ($stmt->execute()) {
                $success = 'Doctor updated successfully!';
            } else {
                $error = 'Error updating doctor: ' . $conn->error;
            }
        }
    } elseif (isset($_POST['delete_doctor'])) {
        $id = $_POST['doctor_id'];
        // First delete related appointments
        $stmt = $conn->prepare("DELETE FROM appointments WHERE doctor_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Then delete the doctor record
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'doctor'");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $success = 'Doctor deleted successfully!';
        } else {
            $error = 'Error deleting doctor: ' . $conn->error;
        }
    }
}

// Fetch all doctors with their specializations
$result = $conn->query("SELECT u.*, d.specialization FROM users u LEFT JOIN doctors d ON u.id = d.user_id WHERE u.role = 'doctor' ORDER BY u.name");
$doctors = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors - Trinetra Eye Care</title>    
    <link href="../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
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
    </style>
</head>
<body class="bg-light">
   <!-- Vertical Sidebar -->
   <nav class="navbar navbar-dark" style="background: var(--sidebar-dark); height: 100vh; width: 250px; position: fixed; top: 0; left: 0; box-shadow: var(--shadow-sm);">
    <div class="d-flex flex-column align-items-start p-3" style="height: 100%;">
        <a href="#" class="navbar-brand mb-4 d-flex flex-column align-items-center w-100">
            <img src="logo.png" alt="Logo" height="50" class="mb-2">
            <small class="text-muted text-center">Admin Dashboard</small>
        </a>

        <ul class="nav nav-pills flex-column w-100">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="doctors.php">
                    <i class="fas fa-user-md me-2"></i> Doctors
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="patients.php">
                    <i class="fas fa-users me-2"></i> Patients
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="appointments.php">
                    <i class="fas fa-calendar-check me-2"></i> Appointments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="medical-history.php">
                    <i class="fas fa-notes-medical me-2"></i> Medical History
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="products.php">
                    <i class="fas fa-glasses me-2"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="orders.php">
                    <i class="fas fa-shopping-cart me-2"></i> Orders
                </a>
            </li>
            <li class="nav-item mt-auto">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<main class="px-4 py-4" style="margin-left: 250px; width: calc(100% - 250px); margin-top: 80px;">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="card-title">Manage Doctors</h2>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                            <i class="fas fa-plus"></i> Add New Doctor
                        </button>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table id="doctorsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Specialization</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($doctors as $doctor): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $doctor['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($doctor['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary edit-doctor" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editDoctorModal"
                                                    data-id="<?php echo $doctor['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($doctor['name']); ?>"
                                                    data-email="<?php echo htmlspecialchars($doctor['email']); ?>"
                                                    data-specialization="<?php echo htmlspecialchars($doctor['specialization']); ?>"
                                                    data-phone="<?php echo htmlspecialchars($doctor['phone']); ?>"
                                                    data-status="<?php echo htmlspecialchars($doctor['status']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-doctor"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteDoctorModal"
                                                    data-id="<?php echo $doctor['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($doctor['name']); ?>">
                                                <i class="fas fa-trash"></i>
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

    <!-- Add Doctor Modal -->
    <div class="modal fade" id="addDoctorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Doctor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">Set an initial password for the doctor's account.</small>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_doctor" class="btn btn-primary">Add Doctor</button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Doctor Modal -->
    <div class="modal fade" id="editDoctorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Doctor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="doctor_id" id="edit_doctor_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="edit_specialization" name="specialization" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_doctor" class="btn btn-primary">Update Doctor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Doctor Modal -->
    <div class="modal fade" id="deleteDoctorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Doctor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <span id="delete_doctor_name"></span>?</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="doctor_id" id="delete_doctor_id">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_doctor" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#doctorsTable').DataTable({
                "order": [[0, "asc"]],
                "pageLength": 10,
                "searching": true,
                "paging": true
            });

            // Edit doctor modal handler
            $('.edit-doctor').click(function() {
                $('#edit_doctor_id').val($(this).data('id'));
                $('#edit_name').val($(this).data('name'));
                $('#edit_email').val($(this).data('email'));
                $('#edit_specialization').val($(this).data('specialization'));
                $('#edit_phone').val($(this).data('phone'));
                $('#edit_status').val($(this).data('status'));
            });

// Delete doctor modal handler
$('.delete-doctor').click(function() {
    $('#delete_doctor_id').val($(this).data('id'));
    $('#delete_doctor_name').text($(this).data('name'));
});
});
</script>
</body>
</html>