<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('customer')) {
    header('Location: ../customer-login.php');
    exit;
}

// Ensure session is active and authenticated
if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header('Location: ../customer-login.php');
    exit;
}

$conn = $GLOBALS['conn'];
$user_id = $_SESSION['user_id'];

// Fetch user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch recent orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$recent_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get cart count
$stmt = $conn->prepare("SELECT COUNT(*) as cart_count FROM cart WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$cart_result = $stmt->get_result()->fetch_assoc();
$cart_count = $cart_result['cart_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .welcome-banner {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .quick-action {
            text-decoration: none;
            color: inherit;
        }
        .quick-action:hover .dashboard-card {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../includes/header2.php'; ?>

    <div class="container py-5">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                    <p class="mb-0">Manage your orders and explore our latest collection of eyewear.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="../shop2.php" class="btn btn-light btn-lg">Shop Now</a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <a href="../cart2.php" class="quick-action">
                    <div class="dashboard-card p-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h3 class="mb-1"><?php echo $cart_count; ?></h3>
                                <p class="mb-0">Items in Cart</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="../shop2.php" class="quick-action">
                    <div class="dashboard-card p-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-glasses fa-2x text-success"></i>
                            </div>
                            <div>
                                <h3 class="mb-1">Shop</h3>
                                <p class="mb-0">Browse Products</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="../appointment.php" class="quick-action">
                    <div class="dashboard-card p-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-calendar-check fa-2x text-info"></i>
                            </div>
                            <div>
                                <h3 class="mb-1">Book</h3>
                                <p class="mb-0">Eye Examination</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="dashboard-card p-4 mb-4">
            <h2 class="mb-4">Recent Orders</h2>
            <?php if (empty($recent_orders)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                    <p class="mb-0">No orders yet. Start shopping to place your first order!</p>
                    <a href="../shop2.php" class="btn btn-primary mt-3">Browse Products</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($order['status']) {
                                            case 'pending':
                                                $status_class = 'bg-warning text-dark';
                                                break;
                                            case 'processing':
                                                $status_class = 'bg-info text-white';
                                                break;
                                            case 'completed':
                                                $status_class = 'bg-success text-white';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'bg-danger text-white';
                                                break;
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>