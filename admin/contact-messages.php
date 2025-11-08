<?php
require_once '../config/database.php';
require_once '../includes/Auth.php';

// Initialize the session
session_start();

// Check if the user is logged in and is an admin
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    header('Location: /login.php');
    exit;
}

// Handle message status update
if (isset($_POST['message_id']) && isset($_POST['status'])) {
    $message_id = filter_var($_POST['message_id'], FILTER_SANITIZE_NUMBER_INT);
    $status = $_POST['status'] === 'read' ? 'read' : 'unread';
    
    $stmt = $conn->prepare('UPDATE contact_messages SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $message_id);
    $stmt->execute();
}

// Get messages from database
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);
$messages = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin Dashboard</title>
    <link href="../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
</head>
<body>
    

<div class="d-flex">
    <nav class="sidebar bg-dark" style="width: 250px;">
    <div class="sidebar-sticky">
        <a class="navbar-brand d-flex align-items-center px-3 py-2" href="#">
            <img src="logo.png" alt="Logo" height="50" class="me-2">
            <span class="fw-bold text-white">Trinetra Eye Care</span>
        </a>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active text-white" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="doctors.php">
                    <i class="fas fa-user-md"></i> Doctors
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="patients.php">
                    <i class="fas fa-users"></i> Patients
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="appointments.php">
                    <i class="fas fa-calendar-check"></i> Appointments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="medical-history.php">
                    <i class="fas fa-notes-medical"></i> Medical History
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="products.php">
                    <i class="fas fa-glasses"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="orders.php">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
    <div class="content flex-grow-1">
        <!-- Rest of the content -->
    </div>
</div>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Contact Messages</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Subject</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $message): ?>
                                    <tr class="<?php echo $message['status'] === 'unread' ? 'table-primary' : ''; ?>">
                                        <td><?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><a href="mailto:<?php echo htmlspecialchars($message['email']); ?>"><?php echo htmlspecialchars($message['email']); ?></a></td>
                                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($message['message'], 0, 100)) . (strlen($message['message']) > 100 ? '...' : ''); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $message['status'] === 'unread' ? 'primary' : 'success'; ?>">
                                                <?php echo ucfirst($message['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $message['status'] === 'unread' ? 'read' : 'unread'; ?>">
                                                <button type="submit" class="btn btn-sm btn-<?php echo $message['status'] === 'unread' ? 'success' : 'secondary'; ?>">
                                                    <i class="fas fa-<?php echo $message['status'] === 'unread' ? 'check' : 'undo'; ?>"></i>
                                                    <?php echo $message['status'] === 'unread' ? 'Mark as Read' : 'Mark as Unread'; ?>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $message['id']; ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Message Modal -->
                                    <div class="modal fade" id="messageModal<?php echo $message['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Message Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <strong>From:</strong> <?php echo htmlspecialchars($message['name']); ?> (<?php echo htmlspecialchars($message['email']); ?>)
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Message:</strong><br>
                                                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Received:</strong> <?php echo date('F j, Y H:i:s', strtotime($message['created_at'])); ?>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-primary">
                                                        <i class="fas fa-reply"></i> Reply
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>