<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Auth.php';

header('Content-Type: application/json');

// Initialize the session
session_start();

// Check if the user is logged in and is an admin
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get record ID from request
$recordId = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$recordId) {
    echo json_encode(['success' => false, 'message' => 'Record ID is required']);
    exit;
}

// Get medical record details
$sql = "SELECT mr.*, p.name as patient_name, p.email as patient_email, 
               d.name as doctor_name, doc.specialization 
        FROM medical_records mr 
        LEFT JOIN users p ON mr.patient_id = p.id 
        LEFT JOIN users d ON mr.doctor_id = d.id 
        LEFT JOIN doctors doc ON mr.doctor_id = doc.user_id 
        WHERE mr.id = ? AND p.id IS NOT NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $recordId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Record not found']);
    exit;
}

$record = $result->fetch_assoc();
echo json_encode(['success' => true, 'data' => $record]);
?>