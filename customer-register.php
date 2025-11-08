<?php
session_start();
require_once __DIR__ . '/includes/Auth.php';

$auth = new Auth();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // Server-side validation
        if (empty($email) || empty($password) || empty($name) || empty($phone)) {
            $error = 'All fields are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } elseif (strcmp($password, $confirm_password) !== 0) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
            $error = 'Please enter a valid 10-digit phone number';
        } else {
            $result = $auth->registerCustomer($email, $password, $name, $phone);
            if ($result['success']) {
                $_SESSION['verification_email'] = $email;
                $_SESSION['success_message'] = $result['message'];
                header('Location: verify.php');
                exit();
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Register as a customer at Trinetra Eye Care - Your trusted eye care center">
    <meta name="keywords" content="eye care, registration, customer account, eye hospital">
    <title>Customer Registration - Trinetra Eye Care</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        .card {
            border-radius: 15px;
        }
        .card-body {
            border-radius: 15px;
        }
        .text-primary {
            color: #007bff !important;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
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
    <<?php
require_once __DIR__ . '/includes/Auth.php';
$auth = new Auth();
$currentUser = $auth->isLoggedIn() ? $auth->getCurrentUser() : null;
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.html">
            <img src="logo.png" alt="Trinetra Eye Care Logo"> 
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="doctors.php">Our Doctors</a></li>
                <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="shop2.php">Optical Shop</a></li>
                <?php if ($currentUser): ?>
                    <?php if ($currentUser['role'] === 'patient'): ?>
                        <li class="nav-item"><a class="nav-link" href="../patient/appointments.php">My Appointments</a></li>
                    <?php elseif ($currentUser['role'] === 'doctor'): ?>
                        <li class="nav-item"><a class="nav-link" href="/doctor/appointments.php">Appointments</a></li>
                    <?php elseif ($currentUser['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="/admin/dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if ($currentUser): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($currentUser['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge bg-primary" id="cart-count">0</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown">
                            Login As
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../patient/login.php">Patient Login</a></li>
                            <li><a class="dropdown-item" href="../doctor/login.php">Doctor Login</a></li>
                            <li><a class="dropdown-item" href="../admin/login.php">Admin Login</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="../register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
    <div class="container py-5 mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                    <div class="text-center mb-4">
                    <i class="fas fa-user fa-3x text-primary mb-3"></i>
                        <h3 class="text-center mb-4">Customer Register - Create Account</h3>
</div>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                            <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" 
                                class="form-control" 
                                id="name" 
                                name="name" 
                                value="<?php echo htmlspecialchars($name ?? ''); ?>" 
                                required
                                pattern="^[A-Za-z\s]{2,50}$"
                                title="Full name should only contain letters and spaces (2-50 characters)">
                        </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                                    required
                                    pattern="^98\d{8}$"
                                    title="Phone number must start with 98 and be exactly 10 digits.">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="register" class="btn btn-primary">Register</button>
                            </div>

                            <div class="text-center mt-3">
                                Already have an account? <a href="customer-login.php">Login here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('confirm_password').addEventListener('input', function() {
            if (this.value !== document.getElementById('password').value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>