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
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? '';
    $status = $data['status'] ?? '';
    
    if (empty($user_id)) {
        $response['message'] = 'User ID is required';
    } else {
        $conn = $GLOBALS['conn'];
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $user_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'User status updated successfully';
        } else {
            $response['message'] = 'Failed to update user status';
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);