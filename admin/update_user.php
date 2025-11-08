<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    if (empty($user_id) || empty($name) || empty($email) || empty($phone)) {
        $response['message'] = 'All fields are required';
    } else {
        $conn = $GLOBALS['conn'];
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'User updated successfully';
        } else {
            $response['message'] = 'Failed to update user';
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);