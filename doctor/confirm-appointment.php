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
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify appointment exists and belongs to this doctor
$appointment = null;
$sql = "SELECT a.*, p.name as patient_name, p.email as patient_email 
        FROM appointments a 
        JOIN users p ON a.patient_id = p.id 
        WHERE a.id = ? AND a.doctor_id = ? AND a.status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $appointment_id, $user['id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: dashboard.php');
    exit;
}
$appointment = $result->fetch_assoc();

// Handle confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if ($action === 'confirm') {
        $sql = "UPDATE appointments SET status = 'confirmed', notes = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $notes, $appointment_id);
        
        if ($stmt->execute()) {
            // Send confirmation email to patient
            $mail = new PHPMailer(true);
            try {
                $mail->setFrom('your-email@gmail.com', 'Trinetra Eye Care');
                $mail->addAddress($appointment['patient_email']);
                $mail->Subject = 'Appointment Confirmed';
                $mail->Body = "Dear {$appointment['patient_name']},\n\n"
                           . "Your appointment with Dr. {$user['name']} on "
                           . date('F j, Y', strtotime($appointment['appointment_date'])) . " at "
                           . date('g:i A', strtotime($appointment['appointment_time'])) . " has been confirmed.\n\n"
                           . ($notes ? "Doctor's Notes: {$notes}\n\n" : "")
                           . "Thank you for choosing Trinetra Eye Care.";
                $mail->send();
            } catch (Exception $e) {
                // Log email error but don't stop the process
                error_log("Failed to send confirmation email: {$mail->ErrorInfo}");
            }
            
            header('Location: dashboard.php');
            exit;
        }
    } elseif ($action === 'cancel') {
        $sql = "UPDATE appointments SET status = 'cancelled', notes = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $notes, $appointment_id);
        
        if ($stmt->execute()) {
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Appointment - Trinetra Eye Care</title>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Confirm Appointment</h4>
                        <div class="alert alert-info">
                            <h6 class="mb-2">Appointment Details</h6>
                            <p class="mb-1"><strong>Patient:</strong> <?php echo htmlspecialchars($appointment['patient_name']); ?></p>
                            <p class="mb-1"><strong>Date:</strong> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                            <p class="mb-1"><strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
                        </div>
                        
                        <form method="POST" action="" class="mt-4">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes (optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Add any special instructions or notes for the patient"></textarea>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" name="action" value="cancel" class="btn btn-outline-danger">
                                    <i class="fas fa-times me-1"></i>Cancel Appointment
                                </button>
                                <button type="submit" name="action" value="confirm" class="btn btn-success">
                                    <i class="fas fa-check me-1"></i>Confirm Appointment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>