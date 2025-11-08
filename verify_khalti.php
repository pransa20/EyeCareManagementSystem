<?php
session_start();
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/Cart.php';
require_once __DIR__ . '/includes/KhaltiPayment.php';

$auth = new Auth();
$currentUser = $auth->getCurrentUser();

if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$cart = new Cart($currentUser['id']);
$khalti = new KhaltiPayment();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$token = $_POST['token'] ?? '';
$amount = $_POST['amount'] ?? 0;

if (empty($token) || empty($amount)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment data']);
    exit;
}

// Verify payment with Khalti
$result = $khalti->verifyPayment($token, $amount);

if ($result['success']) {
    // Process the order
    $shipping_address = $_SESSION['shipping_address'] ?? '';
    $orderResult = $cart->checkout('khalti', $shipping_address);
    
    if ($orderResult['success']) {
        // Clear cart after successful checkout
        $cart->clear();
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment successful',
            'order_id' => $orderResult['order_id']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Order processing failed: ' . $orderResult['message']
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Payment verification failed'
    ]);
}