<?php
session_start();
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/Cart.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'add':
                $result = $cart->addItem($data['product_id'], $data['quantity'] ?? 1);
                break;
            case 'remove':
                $result = $cart->removeItem($data['product_id']);
                break;
            case 'update':
                $result = $cart->updateQuantity($data['product_id'], $data['quantity']);
                break;
            default:
                $result = ['success' => false, 'message' => 'Invalid action'];
        }

        $result['cart_count'] = $cart->getTotalCount();
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}


$auth = new Auth();
$currentUser = $auth->getCurrentUser();
$cart = new Cart($currentUser ? $currentUser['id'] : null);
$isLoggedIn = $auth->isLoggedIn();

$conn = $GLOBALS['conn'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    try {
        if (!$auth->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Please login to continue shopping']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
        $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

        if ($productId <= 0) {
            throw new Exception('Invalid product ID');
        }

        if ($quantity <= 0 || $quantity > 10) {
            throw new Exception('Quantity must be between 1 and 10');
        }

        // Check product stock
        $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if (!$product) {
            throw new Exception('Product not found');
        }

        if ($product['stock'] < $quantity) {
            throw new Exception('Not enough stock available');
        }

        // Add to cart
        $cart->addItem($productId, $quantity);
        
        // Get updated cart count
        $cartCount = $cart->getCount();
        
        echo json_encode(['success' => true, 'cart_count' => $cartCount]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get cart items for display
$cartItems = $cart->getItems();
$total = 0;

// Calculate total
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Trinetra Eye Care</title>
    <link href="bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
       
        .cart-item-image { width: 100px; height: 100px; object-fit: contain; }
        .quantity-input { width: 60px; text-align: center; }
        body {
            padding-top: 80px;
            
        }
        .navbar {
    transition: all 0.3s ease;
    font-size: 16px;
    padding: 1rem 0;
    height: auto;
    color: black;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

@media (max-width: 992px) {
    .navbar {
        padding: 0.5rem 0;
    }
}
        .navbar-brand img {
    height: 40px;
    margin-right: 2rem;
}
        .navbar-nav .nav-link {
    font-size: 1rem;
    padding: 0.5rem 1rem;
    margin: 0 0.25rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.navbar-nav .nav-link:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

@media (max-width: 992px) {
    .navbar-nav .nav-link {
        padding: 0.5rem;
        margin: 0;
    }
}

    </style>
</head>
<body>
    
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm" ">
    <div class="container">
        <a class="navbar-brand" href="index.html">
            <img src="logo.png" alt="Trinetra Eye Care Logo" style="height: 40px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link px-3" href="index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="doctors.php">Our Doctors</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link px-3 active" href="shop2.php">Optical Shop</a></li>
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if ($isLoggedIn): ?>
                   
                    <li class="nav-item"><a class="nav-link px-3" href="/project_eye_care/logout.php?redirect=/project_eye_care/customer-login.php" id="logoutLink">Logout</a></li>

                <?php else: ?>
                    <li class="nav-item"><a class="nav-link px-3" href="customer-login.php">Customer Login</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="customer-register.php">Customer Register</a></li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link px-3" href="cart2.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge bg-danger" id="cart-count"><?= $cart->getCount() ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
  

    <div class="container mt-4">
        <h1 class="mb-4">Your Shopping Cart</h1>
        
        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info">Your cart is empty</div>
            <a href="shop2.php" class="btn btn-primary">Continue Shopping</a>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (isset($item['image_path']) && $item['image_path']): ?>
                                            <img src="<?= $item['image_path'] ?>" class="cart-item-image me-3" alt="<?= $item['name'] ?>">
                                        <?php endif; ?>
                                        <div>
                                            <h5 class="mb-0"><?= $item['name'] ?></h5>
                                            <p class="text-muted mb-0"><?= isset($item['description']) ? $item['description'] : '' ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>Rs. <?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <div class="d-flex">
                                        <button class="btn btn-sm btn-outline-secondary update-quantity" data-id="<?= $item['product_id'] ?>" data-action="decrease">-</button>
                                        <input type="number" class="form-control form-control-sm quantity-input mx-1" value="<?= $item['quantity'] ?>" min="1" max="10" data-id="<?= $item['product_id'] ?>">
                                        <button class="btn btn-sm btn-outline-secondary update-quantity" data-id="<?= $item['product_id'] ?>" data-action="increase">+</button>
                                    </div>
                                </td>
                                <td>Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger remove-item" data-id="<?= $item['product_id'] ?>">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td colspan="2">Rs. <?= number_format($total, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
              <div class="d-flex justify-content-between mt-3">
                <a href="shop2.php" class="btn btn-outline-secondary">Continue Shopping</a>
                <div>
                    <button id="pay-with-khalti" class="btn btn-primary">Pay with Khalti</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Khalti Checkout
            const config = {
            "publicKey": "4664dd6b9c79406da13b02d89c50a202",
            "productIdentity": "cart_payment_" + Date.now(),
            "productName": "Cart Payment",
            "productUrl": window.location.href,
            "amount": <?= $total * 100 ?>,
            "paymentPreference": ["KHALTI", "EBANKING", "MOBILE_BANKING", "CONNECT_IPS", "SCT"],
            "eventHandler": {
                onSuccess(payload) {
                    // Send payment verification request to your server
                    fetch('checkout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            token: payload.token,
                            amount: <?= $total * 100 ?>
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'payment-success.php';
                        } else {
                            alert('Payment verification failed: ' + data.message);
                        }
                    });
                },
                onError(error) {
                    console.log(error);
                    alert('Payment failed: ' + error.message);
                },
                onClose() {
                    console.log('widget is closing');
                }
            }
        };
            
            const checkout = new KhaltiCheckout(config);
            
            document.getElementById('pay-with-khalti').addEventListener('click', function() {
                checkout.show({amount: <?= $total * 100 ?>});
            });
            // Update quantity buttons
            document.querySelectorAll('.update-quantity').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-id');
                    const action = this.getAttribute('data-action');
                    const input = this.closest('td').querySelector('.quantity-input');
                    let quantity = parseInt(input.value);
                    
                    if (action === 'increase' && quantity < 10) {
                        quantity++;
                    } else if (action === 'decrease' && quantity > 1) {
                        quantity--;
                    }
                    
                    input.value = quantity;
                    updateCart(productId, quantity);
                });
            });
            
            // Quantity input change
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', function() {
                    const productId = this.getAttribute('data-id');
                    let quantity = parseInt(this.value);
                    
                    if (quantity < 1) quantity = 1;
                    if (quantity > 10) quantity = 10;
                    
                    this.value = quantity;
                    updateCart(productId, quantity);
                });
            });
            
            // Remove item buttons
            document.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-id');
                    removeFromCart(productId);
                });
            });
            
            function updateCart(productId, quantity) {
                fetch('checkout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
                    }),
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count in navbar
                        const cartCountElement = document.getElementById('cart-count');
                        if (cartCountElement) {
                            cartCountElement.textContent = data.cart_count;
                        }
                        
                        // Reload page to reflect changes
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to update cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update cart');
                });
            }
            
            function removeFromCart(productId) {
                if (confirm('Are you sure you want to remove this item from your cart?')) {
                    fetch('cart2.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: 0
                        }),
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update cart count in navbar
                            const cartCountElement = document.getElementById('cart-count');
                            if (cartCountElement) {
                                cartCountElement.textContent = data.cart_count;
                            }
                            
                            // Reload page to reflect changes
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to remove item');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to remove item');
                    });
                }
            }
        });
    </script>
<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">Are you sure you want to logout?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="confirmLogoutBtn" type="button" class="btn btn-danger">Logout</button>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const logoutLink = document.getElementById("logoutLink");
    const confirmLogoutBtn = document.getElementById("confirmLogoutBtn");

    if (logoutLink && confirmLogoutBtn) {
      logoutLink.addEventListener("click", function (e) {
        e.preventDefault(); // Stop default redirect

        // Show Bootstrap modal
        const logoutModal = new bootstrap.Modal(document.getElementById("logoutModal"));
        logoutModal.show();

        // On confirm, redirect
        confirmLogoutBtn.onclick = function () {
          window.location.href = logoutLink.href;
        };
      });
    }
  });
</script>
<script>
function updateCart(action, productId, quantity = 1) {
    fetch('cart2.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action, product_id: productId, quantity: quantity })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('cart-count').textContent = data.cart_count;
            location.reload(); // refresh to show updated cart
        } else {
            alert(data.message || 'Cart update failed.');
        }
    });
}
</script>



</body>
</html>