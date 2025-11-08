<?php
session_start();
require_once __DIR__ . '/includes/Auth.php';

$auth = new Auth();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $user = $auth->loginCustomer($email, $password);
            if ($user) {
            header('Location: shop2.php');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
<?php
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
                            <li><a class="dropdown-item" href="patient/login.php">Patient Login</a></li>
                            <li><a class="dropdown-item" href="doctor/login.php">Doctor Login</a></li>
                            <li><a class="dropdown-item" href="admin/login.php">Admin Login</a></li>
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
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="text-primary">Customer Login</h2>
                            <p class="text-muted">Access your customer account</p>
                        </div>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                <div class="mt-3">
                    Don't have an account? <a href="customer-register.php">Register here</a>
                </div>
                <div class="mt-3">
                    <a href="forgot-password.php">Forgot Password?</a>
                </div>
                <div class="mt-3">
                    <a href="shop2.php" class="btn btn-outline-secondary">Back to Shop</a>
                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>