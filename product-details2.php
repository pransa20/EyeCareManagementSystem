<?php
session_start();
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/Cart.php';
require_once __DIR__ . '/includes/ProductFilter.php';

$auth = new Auth();
$currentUser = $auth->getCurrentUser() ?? [];
$cart = new Cart($currentUser ? $currentUser['id'] : null);
$productFilter = new ProductFilter($conn);
$isLoggedIn = $auth->isLoggedIn();

// Get product details
$product_id = $_GET['id'] ?? 0;
$product = $productFilter->getProductById($product_id);

// Get recommended products using cosine similarity
$recommendations = $productFilter->getRecommendedProducts($product_id);

// Handle prescription upload
$uploadError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['prescription'])) {
    $targetDir = "uploads/prescriptions/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $fileName = basename($_FILES["prescription"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Check if file exists and is a actual image
    if (!empty($_FILES["prescription"]["tmp_name"]) && file_exists($_FILES["prescription"]["tmp_name"])) {
        $check = getimagesize($_FILES["prescription"]["tmp_name"]);
        if($check !== false) {
            // Allow certain file formats
            if($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg" && $fileType != "gif" ) {
                $uploadError = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            } else {
                // Upload file
                if (move_uploaded_file($_FILES["prescription"]["tmp_name"], $targetFilePath)) {
                    $_SESSION['prescription_path'] = $targetFilePath;
                } else {
                    $uploadError = "Sorry, there was an error uploading your file.";
                }
            }
        } else {
            $uploadError = "File is not an image.";
        }
    }
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!$auth->isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Please login to continue shopping', 'requires_login' => true]);
        exit;
    }
    $quantity = $_POST['quantity'] ?? 1;
    $cart->addItem($product_id, $quantity);
    echo json_encode(['success' => true, 'message' => 'Product added to cart successfully']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name'] ?? 'Product Details') ?> - Trinetra Eye Care</title>
    <link href="bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet"> <link href="assets/css/style.css" rel="stylesheet">
    <link href="fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 80px;
            
        }
        .navbar {
    transition: all 0.3s ease;
    font-size: 5px;
    padding: 0;
    color: black;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
        .navbar-brand img {
    height: 40px;
    margin-right: 2rem;
}
        .navbar-nav .nav-link {
    font-size: 1.2rem;
    padding: 0.3rem 0.5rem;
    margin-left: 0.5rem;
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
    
    <div class="container py-5">
        <div class="row">
            <div class="col-md-6">
                <?php if (!empty($product['image_path'])): ?>
                <img src="<?= htmlspecialchars($product['image_path']) ?>" 
                     class="img-fluid rounded" alt="<?= htmlspecialchars($product['name'] ?? 'Product Image') ?>"
                     onerror="this.src='uploads/products/default.jpg'">
            <?php else: ?>
                <img src="uploads/products/default.jpg" class="img-fluid rounded" alt="Default Product Image">
            <?php endif; ?>
            </div>
            <div class="col-md-6">
                <h1><?= htmlspecialchars($product['name'] ?? 'Product') ?></h1>
                <!-- <p class="text-muted">SKU: <?= htmlspecialchars($product['sku'] ?? 'N/A') ?></p> -->
                <h3 class="my-4">Rs. <?= number_format($product['price'] ?? 0, 2) ?></h3>
                
                <div class="mb-4">
                    <h4>Description</h4>
                    <p><?= htmlspecialchars($product['description'] ?? 'No description available') ?></p>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1">
                    </div>
                    
                    <div class="mb-3">
                        <label for="prescription" class="form-label">Upload Prescription (if required)</label>
                        <input type="file" class="form-control" id="prescription" name="prescription" accept="image/*">
                        <?php if ($uploadError): ?>
                            <div class="alert alert-danger mt-2"><?= $uploadError ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                    </button>
                </form>
            </div>
        </div>
        
        <?php if (!empty($recommendations)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3>Recommended Products</h3>
                <div class="row">
                    <?php foreach ($recommendations as $recommended): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($recommended['image_path'])): ?>
                                <img src="<?= htmlspecialchars($recommended['image_path']) ?>" 
                                     class="card-img-top" alt="<?= htmlspecialchars($recommended['name']) ?>"
                                     onerror="this.src='uploads/products/default.jpg'">
                            <?php else: ?>
                                <img src="uploads/products/default.jpg" class="card-img-top" alt="Default Product Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($recommended['name']) ?></h5>
                                <p class="card-text">Rs. <?= number_format($recommended['price'], 2) ?></p>
                                <a href="product-details2.php?id=<?= $recommended['id'] ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: '',
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                var res = JSON.parse(response);
                if (res.requires_login) {
                    $('#loginModal').modal('show');
                } else if (res.success) {
                    location.reload();
                }
            }
        });
    });
});
</script>
    <!-- Login/Register Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Please login or register to continue shopping.</p>
                </div>
                <div class="modal-footer">
                    <a href="customer-login.php" class="btn btn-primary">Login</a>
                    <a href="customer-register.php" class="btn btn-success">Register</a>
                </div>
            </div>
        </div>
    </div>
    <script>
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('add_to_cart', '1');
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = parseInt(cartCountElement.textContent) + parseInt(formData.get('quantity'));
            }
            alert(data.message);
        } else if (data.requires_login) {
            window.location.href = 'customer-login.php';
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the item to cart.');
    });
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
</body>
</html>