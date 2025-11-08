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

// Get the payment token and amount from Khalti
$token = $_POST['token'] ?? '';
$amount = $_POST['amount'] ?? 0;

if (empty($token) || empty($amount)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payment data']);
    exit;
}

// Verify the payment with Khalti
$result = $khalti->verifyPayment($token, $amount);

if ($result['success']) {
    // Payment verified successfully
    $shipping_address = $_SESSION['shipping_address'] ?? '';
    
    // Process the order
    $checkout_result = $cart->checkout('khalti', $shipping_address);
    
    if ($checkout_result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Payment successful',
            'order_id' => $checkout_result['order_id']
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to process order'
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Payment verification failed'
    ]);
}
session_start();
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/PaymentGateway.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$payment_gateway = new PaymentGateway();
$success = $error = '';

// Get payment response parameters
$payment_id = $_POST['payment_id'] ?? $_GET['payment_id'] ?? null;
$order_id = $_POST['order_id'] ?? $_GET['order_id'] ?? null;
$signature = $_POST['signature'] ?? $_GET['signature'] ?? null;

if ($payment_id && $order_id && $signature) {
    $result = $payment_gateway->verifyPayment($payment_id, $order_id, $signature);
    
    if ($result['success']) {
        header('Location: /order-confirmation.php?order_id=' . $order_id);
        exit;
    } else {
        $error = $result['message'];
    }
} else {
    $error = 'Invalid payment response';
}

// If there's an error, redirect to cart with error message
if ($error) {
    $_SESSION['payment_error'] = $error;
    header('Location: /cart.php');
    exit;
}