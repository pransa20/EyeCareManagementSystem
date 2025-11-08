<?php
require_once __DIR__ . '/Auth.php';
$auth = new Auth();
$currentUser = $auth->isLoggedIn() ? $auth->getCurrentUser() : null;
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="../index.html">
            <img src="logo.png" alt="Trinetra Eye Care Logo"> 
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="../index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="../doctors.php">Our Doctors</a></li>
                <li class="nav-item"><a class="nav-link" href="../services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="../shop2.php">Optical Shop</a></li>
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