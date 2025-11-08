<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
$conn = Database::getInstance()->getConnection();
$success = $error = '';

// Currency conversion rate (1 NPR = X USD)
$npr_to_usd_rate = 0.0075;

// Get selected currency
$selected_currency = $_GET['currency'] ?? 'npr';

// Handle form submissions for adding categories
if (isset($_POST['add_category'])) {
    $name = $_POST['category_name'] ?? '';
    $slug = $_POST['category_slug'] ?? '';
    $description = $_POST['category_description'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO product_categories (name, description, slug, is_active, created_at) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt === false) {
        $error = 'Database preparation error: ' . $conn->error;
    } else {
        $stmt->bind_param("sssi", $name, $description, $slug, $is_active);
        if ($stmt->execute()) {
            $success = 'Category added successfully.';
        } else {
            $error = 'Failed to add category: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle form submissions for adding products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category = $_POST['category'] ?? '';

    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($file_extension, $allowed_extensions)) {
            $file_name = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = 'uploads/products/' . $file_name;
            } else {
                $error = 'Failed to move uploaded file.';
            }
        } else {
            $error = 'Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.';
        }
    } else {
        $error = 'Please select a valid image file.';
    }

    // Validate category exists
    $category_check = $conn->prepare("SELECT id FROM product_categories WHERE id = ?");
    $category_check->bind_param("i", $category);
    $category_check->execute();
    $category_check->store_result();

    if ($category_check->num_rows === 0) {
        $error = 'Invalid product category selected.';
    } else {
        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, image_path) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                $error = 'Database preparation error: ' . $conn->error;
            } else {
                $stmt->bind_param("ssddis", $name, $description, $price, $stock, $category, $image_path);
                if ($stmt->execute()) {
                    $success = 'Product added successfully.';
                } else {
                    $error = 'Failed to add product: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Handle product updates
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category = $_POST['category'] ?? '';

    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ? WHERE id = ?");
    if ($stmt === false) {
        $error = 'Database preparation error: ' . $conn->error;
    } else {
        $stmt->bind_param("ssddis", $name, $description, $price, $stock, $category, $product_id);
        if (!$stmt->execute()) {
            $error = 'Failed to update product: ' . $stmt->error;
        } else {
            $success = 'Product updated successfully.';
        }
        $stmt->close();
    }
}

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'] ?? '';

    // Before deleting a product, remove related entries in shopping_cart
    $stmt = $conn->prepare("DELETE FROM shopping_cart WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt === false) {
        $error = 'Database preparation error: ' . $conn->error;
    } else {
        $stmt->bind_param("i", $product_id);
        if ($stmt->execute()) {
            $success = 'Product deleted successfully.';
        } else {
            $error = 'Failed to delete product: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Get all products
$products = [];
$sql = "SELECT p.*, c.name AS category_name FROM products p JOIN product_categories c ON p.category_id = c.id ORDER BY p.created_at DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Get all categories for the dropdown
$categories = [];
$category_result = $conn->query("SELECT * FROM product_categories ORDER BY created_at DESC");
while ($category = $category_result->fetch_assoc()) {
    $categories[] = $category;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Trinetra Eye Care</title>
    <link href="../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="text-center mb-4 py-4">
                    <img src="logo.png" alt="Logo" height="50" class="mb-3">
                    <h5 class="fw-bold mb-0">Trinetra Eye Care</h5>
                    <p class="text-muted small mb-0">Admin Dashboard</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </li>
                </ul>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-4 py-4">
                <h1 class="h2">Manage Products</h1>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header">Add New Product</div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price (NPR)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Product Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                            </div>
                            <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Products List</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <?php if ($product['image_path']): ?>
                                                <img src="/<?php echo htmlspecialchars($product['image_path']); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td><?php echo number_format($product['price'], 2); ?> NPR</td>
                                        <td><?php echo $product['stock']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $product['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $product['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo $product['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Product</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        <div class="mb-3">
                                                            <label for="edit_name<?php echo $product['id']; ?>" class="form-label">Product Name</label>
                                                            <input type="text" class="form-control" id="edit_name<?php echo $product['id']; ?>" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="edit_category<?php echo $product['id']; ?>" class="form-label">Category</label>
                                                            <select class="form-select" id="edit_category<?php echo $product['id']; ?>" name="category" required>
                                                                                                                        <?php foreach ($categories as $category): ?>
                                                                                                                            <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                                                                                                                <?php echo htmlspecialchars($category['name']); ?>
                                                                                                                            </option>
                                                                                                                        <?php endforeach; ?>
                                                                                                                    </select>
                                                                                                                </div>
                                                                                                                <div class="mb-3">
                                                                                                                    <label for="edit_price<?php echo $product['id']; ?>" class="form-label">Price (NPR)</label>
                                                                                                                    <input type="number" class="form-control" id="edit_price<?php echo $product['id']; ?>" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                                                                                                                </div>
                                                                                                                <div class="mb-3">
                                                                                                                    <label for="edit_stock<?php echo $product['id']; ?>" class="form-label">Stock</label>
                                                                                                                    <input type="number" class="form-control" id="edit_stock<?php echo $product['id']; ?>" name="stock" min="0" value="<?php echo $product['stock']; ?>" required>
                                                                                                                </div>
                                                                                                                <div class="mb-3">
                                                                                                                    <label for="edit_description<?php echo $product['id']; ?>" class="form-label">Description</label>
                                                                                                                    <textarea class="form-control" id="edit_description<?php echo $product['id']; ?>" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                            <div class="modal-footer">
                                                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                                                <button type="submit" name="update_product" class="btn btn-primary">Save Changes</button>
                                                                                                            </div>
                                                                                                        </form>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                        
                                                                                            <!-- Delete Modal -->
                                                                                            <div class="modal fade" id="deleteModal<?php echo $product['id']; ?>" tabindex="-1">
                                                                                                <div class="modal-dialog">
                                                                                                    <div class="modal-content">
                                                                                                        <div class="modal-header">
                                                                                                            <h5 class="modal-title">Delete Product</h5>
                                                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                                                        </div>
                                                                                                        <div class="modal-body">
                                                                                                            <p>Are you sure you want to delete this product?</p>
                                                                                                            <p><strong><?php echo htmlspecialchars($product['name']); ?></strong></p>
                                                                                                        </div>
                                                                                                        <div class="modal-footer">
                                                                                                            <form method="POST" action="">
                                                                                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                                                <button type="submit" name="delete_product" class="btn btn-danger">Delete</button>
                                                                                                            </form>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <?php endforeach; ?>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </main>
                                                                </div>
                                                            </div>
                                                        
                                                            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                                                        </body>
                                                        </html>