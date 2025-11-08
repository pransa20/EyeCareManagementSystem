<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Cart.php';

header('Content-Type: application/json');

$auth = new Auth();
$currentUser = $auth->getCurrentUser();
$cart = new Cart($currentUser ? $currentUser['id'] : null);

// // Handle POST request for adding items to cart
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $data = json_decode(file_get_contents('php://input'), true);
    
//     if (!isset($data['product_id']) || !isset($data['quantity'])) {
//         http_response_code(400);
//         echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
//         exit;
//     }
    
//     $result = $cart->addItem($data['product_id'], $data['quantity']);
    
//     if ($result['success']) {
//         echo json_encode([
//             'success' => true,
//             'message' => $result['message'],
//             'cart_count' => $cart->getItemCount()
//         ]);
//     } else {
//         http_response_code(400);
//         echo json_encode(['success' => false, 'message' => $result['message']]);
//     }
//     exit;
// }
// ... existing code ...
// Handle POST request for adding items to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['product_id']) || !isset($data['quantity'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }
    
    $result = $cart->addItem($data['product_id'], $data['quantity']);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'cart_count' => $cart->getItemCount()
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }
    exit;
}
// ... existing code ...
// Handle GET request for retrieving cart items
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => true,
        'items' => $cart->getItems(),
        'total' => $cart->getTotal(),
        'count' => $cart->getItemCount()
    ]);
    exit;
}

// Handle unsupported methods
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);