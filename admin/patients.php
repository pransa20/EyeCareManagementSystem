<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    header('Location: /login.php');
    exit;
}

$conn = $GLOBALS['conn'];
$error_message = '';
$success_message = '';

// Get all patients
$patients = [];
$sql = "SELECT * FROM users WHERE role = 'patient' ORDER BY name ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}

// Handle patient status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $patient_id = $_POST['patient_id'];
    $new_status = $_POST['new_status'];
    
    $sql = "UPDATE users SET status = ? WHERE id = ? AND role = 'patient'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('si', $new_status, $patient_id);
        if ($stmt->execute()) {
            $success_message = 'Patient status updated successfully!';
            // Update status in the patients array
            foreach ($patients as &$patient) {
                if ($patient['id'] == $patient_id) {
                    $patient['status'] = $new_status;
                    break;
                }
            }
        } else {
            $error_message = 'Failed to update patient status.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients - Admin Dashboard</title>
    <link href="../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
      <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <!-- <?php include '../includes/header.php'; ?> -->
            
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
        
    <div class="container-fluid py-5 mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Manage Patients</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($patients as $patient): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($patient['id']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['name']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($patient['address'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $patient['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst(htmlspecialchars($patient['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            
                                            <a href="medical-history.php?id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-info">Medical History</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function toggleUserStatus(userId, newStatus) {
        if (!confirm('Are you sure you want to ' + (newStatus === 'active' ? 'activate' : 'deactivate') + ' this patient?')) {
            return;
        }

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
                location.reload(); // Reload to show updated status
            } else {
                alert('Failed to update status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating status');
        });
    }
    </script>
</body>
</html>