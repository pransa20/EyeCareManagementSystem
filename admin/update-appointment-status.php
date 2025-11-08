<?php
require_once '../config/database.php';
require_once '../includes/Auth.php';

// Initialize the session and check authentication
session_start();
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if the request is POST and has the required data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get and validate the appointment ID and new status
$appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
$new_status = isset($_POST['status']) ? $_POST['status'] : '';

// Validate the status value
$allowed_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
if (!in_array($new_status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

// Update the appointment status in the database
$sql = "UPDATE appointments SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param('si', $new_status, $appointment_id);
    
    if ($stmt->execute()) {
        // Get the updated appointment details for notification
        $sql = "SELECT a.*, p.email as patient_email, p.name as patient_name, 
                d.email as doctor_email, d.name as doctor_name 
                FROM appointments a 
                LEFT JOIN users p ON a.patient_id = p.id 
                LEFT JOIN users d ON a.doctor_id = d.id 
                WHERE a.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $appointment = $result->fetch_assoc();

        // Send email notifications
        if ($appointment) {
            // TODO: Implement email notifications here
            // You can use PHPMailer or your preferred email library
        }

        echo json_encode([
            'success' => true, 
            'message' => 'Appointment status updated successfully',
            'new_status' => $new_status
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to update appointment status: ' . $conn->error
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to prepare statement: ' . $conn->error
    ]);
}

$conn->close();