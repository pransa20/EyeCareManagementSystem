<?php
session_start();
require_once __DIR__ . '/includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$order_id = $_GET['order_id'] ?? 0;
if (!$order_id) {
    header('Location: /index.html');
    exit;
}

$conn = $GLOBALS['conn'];
$order = null;
$order_items = [];

// Get order details
$sql = "SELECT o.*, u.name as customer_name, u.email as customer_email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($order = $result->fetch_assoc()) {
    // Get order items
    $sql = "SELECT oi.*, p.name as product_name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($item = $result->fetch_assoc()) {
        $order_items[] = $item;
    }
} else {
    header('Location: /index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .container {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5 mt-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Order Confirmation</h2>
                    <button onclick="window.print()" class="btn btn-primary no-print">
                        <i class="fas fa-print me-2"></i> Print Invoice
                    </button>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Order Details</h5>
                        <p class="mb-1"><strong>Order #:</strong> <?php echo $order_id; ?></p>
                        <p class="mb-1"><strong>Date:</strong> <?php echo date('d M Y', strtotime($order['created_at'])); ?></p>
                        <p class="mb-1"><strong>Status:</strong> 
                            <span class="badge bg-<?php echo $order['status'] === 'delivered' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'info'); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h5>Customer Details</h5>
                        <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-end">₹<?php echo number_format($item['price'], 2); ?></td>
                                <td class="text-end">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                <td class="text-end"><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="border-top pt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Shipping Address</h5>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Payment Information</h5>
                            <p class="mb-1"><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                            <p class="mb-0"><strong>Payment Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-center no-print">
                    <a href="/shop2.php" class="btn btn-outline-primary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>