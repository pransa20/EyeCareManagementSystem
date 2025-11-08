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
    
    if (empty($user_id)) {
        $response['message'] = 'User ID is required';
    } else {
        $conn = $GLOBALS['conn'];
        
        // First check if the user exists and is not an admin
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user) {
            $response['message'] = 'User not found';
        } elseif ($user['role'] === 'admin') {
            $response['message'] = 'Cannot delete admin users';
        } else {
            // Delete the user
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'User deleted successfully';
            } else {
                $response['message'] = 'Failed to delete user';
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);