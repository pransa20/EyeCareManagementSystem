<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    header('Location: /login.php');
    exit;
}

$conn = $GLOBALS['conn'];
$success = $error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_doctor'])) {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        $result = $auth->register($email, $password, $name, $phone, 'doctor');
        if ($result['success']) {
            $success = 'Doctor account created successfully.';
        } else {
            $error = $result['message'];
        }
    } elseif (isset($_POST['update_status'])) {
        $user_id = $_POST['user_id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $user_id);
        
        if ($stmt->execute()) {
            $success = 'User status updated successfully.';
        } else {
            $error = 'Failed to update user status.';
        }
    }
}

// Get all users grouped by role
$users = [
    'doctors' => [],
    'patients' => []
];

$sql = "SELECT id, name, email, phone, role, email_verified, created_at FROM users WHERE role IN ('doctor', 'patient') ORDER BY role, name";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    if ($row['role'] === 'doctor') {
        $users['doctors'][] = $row;
    } else {
        $users['patients'][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Trinetra Eye Care</title>
    <link href="../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
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
        
            <div class="col-md-9">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Add New Doctor</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="add_doctor" class="btn btn-primary">Add Doctor</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Doctors List</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users['doctors'] as $doctor): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $doctor['email_verified'] ? 'success' : 'warning'; ?>">
                                                <?php echo $doctor['email_verified'] ? 'Verified' : 'Pending'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($doctor['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $doctor['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $doctor['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-<?php echo $doctor['email_verified'] ? 'warning' : 'success'; ?>" onclick="toggleUserStatus(<?php echo $doctor['id']; ?>, <?php echo $doctor['email_verified']; ?>)">
                                                <i class="fas fa-<?php echo $doctor['email_verified'] ? 'times' : 'check'; ?>"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Patients List</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users['patients'] as $patient): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($patient['name']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $patient['email_verified'] ? 'success' : 'warning'; ?>">
                                                <?php echo $patient['email_verified'] ? 'Verified' : 'Pending'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($patient['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $patient['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $patient['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-<?php echo $patient['email_verified'] ? 'warning' : 'success'; ?>" onclick="toggleUserStatus(<?php echo $patient['id']; ?>, <?php echo $patient['email_verified']; ?>)">
                                                <i class="fas fa-<?php echo $patient['email_verified'] ? 'times' : 'check'; ?>"></i>
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

    <!-- Edit User Modal -->
    <?php foreach ($users['doctors'] as $doctor): ?>
    <div class="modal fade" id="editModal<?php echo $doctor['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $doctor['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?php echo $doctor['id']; ?>">Edit Doctor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm<?php echo $doctor['id']; ?>">
                        <input type="hidden" name="user_id" value="<?php echo $doctor['id']; ?>">
                        <div class="mb-3">
                            <label for="editName<?php echo $doctor['id']; ?>" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editName<?php echo $doctor['id']; ?>" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail<?php echo $doctor['id']; ?>" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail<?php echo $doctor['id']; ?>" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPhone<?php echo $doctor['id']; ?>" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="editPhone<?php echo $doctor['id']; ?>" name="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateUser(<?php echo $doctor['id']; ?>)">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php foreach ($users['patients'] as $patient): ?>
    <div class="modal fade" id="editModal<?php echo $patient['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $patient['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?php echo $patient['id']; ?>">Edit Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm<?php echo $patient['id']; ?>">
                        <input type="hidden" name="user_id" value="<?php echo $patient['id']; ?>">
                        <div class="mb-3">
                            <label for="editName<?php echo $patient['id']; ?>" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editName<?php echo $patient['id']; ?>" name="name" value="<?php echo htmlspecialchars($patient['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail<?php echo $patient['id']; ?>" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail<?php echo $patient['id']; ?>" name="email" value="<?php echo htmlspecialchars($patient['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPhone<?php echo $patient['id']; ?>" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="editPhone<?php echo $patient['id']; ?>" name="phone" value="<?php echo htmlspecialchars($patient['phone']); ?>" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateUser(<?php echo $patient['id']; ?>)">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function updateUser(userId) {
        const formData = new FormData(document.getElementById('editForm' + userId));
        
        fetch('update_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User updated successfully!');
                location.reload();
            } else {
                alert('Error updating user: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the user');
        });
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch('delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully!');
                    location.reload();
                } else {
                    alert('Error deleting user: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the user');
            });
        }
    }

    function toggleUserStatus(userId, currentStatus) {
        const newStatus = currentStatus === 1 ? 0 : 1;
        fetch('toggle_user_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                user_id: userId,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status');
        });
    }
    </script>
</body>
</html>