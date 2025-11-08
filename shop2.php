<?php
session_start();
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/Cart.php';

$auth = new Auth();
$currentUser = $auth->getCurrentUser();
$cart = new Cart($currentUser ? $currentUser['id'] : null);

$success = $error = '';
$conn = $GLOBALS['conn'];

// Check if user is logged in
$isLoggedIn = $auth->isLoggedIn();

// Fetch products from admin/products.php
$products = [];
// Get sort parameter from URL
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build SQL query based on sort parameter
if ($sort === 'oldest') {
    $sql = "SELECT p.*, p.stock AS stock_quantity FROM products p ORDER BY p.created_at ASC";
} else {
    $sql = "SELECT p.*, p.stock AS stock_quantity FROM products p ORDER BY p.created_at DESC";
}
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $products[] = array_merge($row, ['content_title' => isset($row['title']) ? $row['title'] : '', 'content_description' => isset($row['description']) ? $row['description'] : '']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Trinetra Eye Care</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet">


    <script src="assets/js/quick-view.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
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

            <!-- Login Required Modal -->
            <!-- <div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="loginRequiredModalLabel">Login Required</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Please login or register to continue shopping.</p>
                        </div>
                        <div class="modal-footer">
                            <a href="customer-login.php" class="btn btn-primary">Login</a>
                            <a href="customer-register.php" class="btn btn-success">Register</a>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div> -->
            <!-- Login Required Modal -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginRequiredModalLabel">Login Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Please login or register to continue shopping.</p>
            </div>
            <div class="modal-footer">
                <a href="customer-login.php" class="btn btn-primary">Login</a>
                <a href="customer-register.php" class="btn btn-success">Register</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

            <div class="col-md-9 d-flex flex-column align-items-center" style="max-width: 1200px; margin: 0 auto; padding: 0 15px; display: flex;" >
                <?php
// Fetch content from admin/content.php
// Fetch content from website_content table with error handling
$contents = [];
try {
    $contentSql = "SELECT title, description FROM website_content WHERE section_name = 'shop' AND is_active = 1 ORDER BY created_at DESC";
    $contentResult = $conn->query($contentSql);
    if ($contentResult) {
        while ($row = $contentResult->fetch_assoc()) {
            $contents[] = $row;
        }
    } else {
        error_log("Failed to fetch shop content: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error fetching shop content: " . $e->getMessage());
}
?>
<div class="container mt-3">
    <div class="row">
        <div class="col-md-12">
           
            <?php foreach ($contents as $content): ?>
                <div class="mb-4">
                    <h3><?= $content['title'] ?></h3>
                    <p><?= $content['description'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Our Products</h1>
            </div>
    <div class="btn-group" role="group">
        <a href="?sort=newest" class="btn btn-outline-primary <?= $sort === 'newest' ? 'active' : '' ?>">Newest</a>
        <a href="?sort=oldest" class="btn btn-outline-primary <?= $sort === 'oldest' ? 'active' : '' ?>">Oldest</a>
    
</div>

                <?php
$itemsPerPage = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;
$totalProducts = count($products);
$totalPages = ceil($totalProducts / $itemsPerPage);
$paginatedProducts = array_slice($products, $offset, $itemsPerPage);
?>
<div class="row justify-content-center mx-auto" style="max-width: 1200px; margin: 0 auto; padding: 0 15px; display: flex; ">
                    <?php foreach ($paginatedProducts as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card product-card h-100">
                                <?php if ($product['image_path']): ?>
                                    <img src="<?= $product['image_path'] ?>" class="card-img-top" alt="<?= $product['name'] ?>" style="width: 100%; height: 250px; object-fit: contain; padding: 10px;">
                                <?php endif; ?>
                                <div class="card-body"style="align-items: center;">
                                    <h5 class="card-title"><?= $product['name'] ?></h5>
                                    <p class="card-text"><?= $product['description'] ?></p>
                                    <p class="text-muted">Rs. <?= number_format($product['price'], 2) ?></p>
                                    <p class="text-muted">Stock: <?= $product['stock_quantity'] ?></p>
                                    <div class="d-flex align-items-center justify-content-between w-100" style="padding-top: 10px;">
                                        <a href="product-details2.php?id=<?= $product['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                                     
                                    <div class="input-group" style="width: 150px;">
                                        <input type="number" class="form-control form-control-sm" id="quantity-<?= $product['id'] ?>" 
                                            value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                                        <button class="btn btn-success btn-sm" 
                                                onclick="addToCart(<?= $product['id'] ?>, document.getElementById('quantity-<?= $product['id'] ?>').value)" 
                                                <?= $product['stock_quantity'] == 0 ? 'disabled' : '' ?>>
                                            <i class="fas fa-shopping-cart"></i> Add
                                        </button>
                                    </div>
                                  
                                        <script>
                                            window.addToCart = function(productId, quantity) {
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    // 
    .then(response => response.json())
.then(data => {
    if (data.success) {
        const cartCountElement = document.getElementById('cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = data.cart_count;
        }
        showMessageModal(data.message, "Item Added");
    } else if (data.requires_login) {
        // Show login required modal
        var loginModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
        loginModal.show();
    } else {
        showMessageModal("Failed to add to cart. Please login or try again.", "Add to Cart Failed");
    }
})
.catch(error => {
    console.error('Error:', error);
    showMessageModal("An error occurred while adding the item to the cart.", "Error");
});

}
                                        // window.addToCart = function(productId, quantity) {
                                        //     fetch('api/cart.php', {
                                        //         method: 'POST',
                                        //         headers: {
                                        //             'Content-Type': 'application/json'
                                        //         },
                                        //         body: JSON.stringify({
                                        //             product_id: productId,
                                        //             quantity: quantity
                                        //         })
                                        //     })
                                        //     .then(response => response.json())
                                        //     .then(data => {
                                        //         if (data.success) {
                                        //             const cartCountElement = document.getElementById('cart-count');
                                        //             if (cartCountElement) {
                                        //                 cartCountElement.textContent = data.cart_count;
                                        //             }
                                        //             alert(data.message);
                                        //         } else {
                                        //             alert(data.message);
                                        //         }
                                        //     })
                                        //     .catch(error => {
                                        //         console.error('Error:', error);
                                        //         alert('An error occurred while adding the item to cart.');
                                        //     });
                                        // }
                                        
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
            <?php endif; ?>
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <?php
// Fetch content from admin/content.php
// Fetch content from website_content table with error handling
$contents = [];
try {
    $contentSql = "SELECT title, description FROM website_content WHERE section_name = 'shop' AND is_active = 1 ORDER BY created_at DESC";
    $contentResult = $conn->query($contentSql);
    if ($contentResult) {
        while ($row = $contentResult->fetch_assoc()) {
            $contents[] = $row;
        }
    } else {
        error_log("Failed to fetch shop content: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error fetching shop content: " . $e->getMessage());
}
?>

            <?php foreach ($contents as $content): ?>
                <div class="mb-4">
                    <h3><?= $content['title'] ?></h3>
                    <p><?= $content['description'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
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
<script src="bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php include 'includes/footer.php'; ?>
</body>
</html>