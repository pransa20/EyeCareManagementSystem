

<?php
require_once __DIR__ . '/../includes/Auth.php';
$auth = new Auth();

if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Head</title>
    <!-- Bootstrap CSS -->
    <link href="../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <style>
        /* Embedded CSS for Navigation */
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
            margin-right: 10px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg admin-navbar fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="../admin/dashboard-new.php">
                <img src="../logo.png" alt="Trinetra Eye Care Logo">
                Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="../admin/dashboard-new.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usersDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../admin/users.php">All Users</a></li>
                            <li><a class="dropdown-item" href="../admin/doctors.php">Doctors</a></li>
                            <li><a class="dropdown-item" href="../admin/patients.php">Patients</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="appointmentsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-check me-2"></i>Appointments
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../appointments.php">All Appointments</a></li>
                            <li><a class="dropdown-item" href="../admin/appointments.php?status=pending">Pending</a></li>
                            <li><a class="dropdown-item" href="../admin/appointments.php?status=confirmed">Confirmed</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="shopDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-shopping-bag me-2"></i>Shop
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../admin/products.php">Products</a></li>
                            <li><a class="dropdown-item" href="../admin/orders.php">Orders</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/content.php">
                            <i class="fas fa-edit me-2"></i>Content
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-2"></i>Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                           
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
                    </body>
                    </html>