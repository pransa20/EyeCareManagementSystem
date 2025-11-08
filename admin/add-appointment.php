<?php
require_once '../config/database.php';
require_once '../includes/Auth.php';

// Initialize the session
session_start();

// Check if the user is logged in and is an admin
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate input
if (!isset($_POST['doctor_id'], $_POST['appointment_datetime'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$doctor_id = intval($_POST['doctor_id']);
$appointment_datetime = $_POST['appointment_datetime'];
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
$is_new_patient = isset($_POST['is_new_patient']) && $_POST['is_new_patient'] === 'true';

// Handle new patient registration
if ($is_new_patient) {
    if (!isset($_POST['new_patient_name'], $_POST['new_patient_email'])) {
        echo json_encode(['success' => false, 'message' => 'Missing patient information']);
        exit;
    }

    $name = trim($_POST['new_patient_name']);
    $email = trim($_POST['new_patient_email']);
    $phone = isset($_POST['new_patient_phone']) ? trim($_POST['new_patient_phone']) : '';
    $address = isset($_POST['new_patient_address']) ? trim($_POST['new_patient_address']) : '';

    // Generate a random password for the new patient
    $password = bin2hex(random_bytes(8));
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new patient
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'patient')");
    $stmt->bind_param('sssss', $name, $email, $hashed_password, $phone, $address);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to create new patient: ' . $conn->error]);
        exit;
    }
    
    $patient_id = $conn->insert_id;
    
    // TODO: Send email to patient with their login credentials
} else {
    if (!isset($_POST['patient_id'])) {
        echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
        exit;
    }
    $patient_id = intval($_POST['patient_id']);
}

// Validate datetime format
// Validate datetime format and ensure it's in the future
$appointment_timestamp = strtotime($appointment_datetime);
if (!$appointment_timestamp || $appointment_timestamp < time()) {
    echo json_encode(['success' => false, 'message' => 'Invalid date/time format or past date']);
    exit;
}

// Validate appointment time is within working hours (9am-5pm)
$hour = date('H', $appointment_timestamp);
if ($hour < 9 || $hour >= 17) {
    echo json_encode(['success' => false, 'message' => 'Appointments must be between 9am and 5pm']);
    exit;
}

// Verify patient exists (for existing patients)
if (!$is_new_patient) {
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ? AND role = 'patient'");
    $stmt->bind_param('i', $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Patient not found']);
        exit;
    }
    $patient = $result->fetch_assoc();
}

// Check if doctor exists
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'doctor'");
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid doctor']);
    exit;
}

// Check for existing appointments at the same time
$stmt = $conn->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status != 'cancelled'");
$stmt->bind_param('is', $doctor_id, $appointment_datetime);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Doctor already has an appointment at this time']);
    exit;
}

// Insert the appointment
$stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, notes, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param('iiss', $patient_id, $doctor_id, $appointment_datetime, $notes);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add appointment: ' . $conn->error]);
}