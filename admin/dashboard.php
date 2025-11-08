


<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    header('Location: /login.php');
    exit;
}

$conn = $GLOBALS['conn'];
$user = $auth->getCurrentUser();

// Get statistics
$stats = [
    'total_patients' => 0,
    'total_doctors' => 0,
    'total_appointments' => 0,
    'total_products' => 0,
    'total_orders' => 0,
    'revenue' => 0,
    'pending_appointments' => 0,
    'today_appointments' => 0,
    'monthly_revenue' => 0,
    'monthly_appointments' => 0,
    'total_prescriptions' => 0,
    'total_consultations' => 0
];

// Get website content
$website_content = [];
$sql = "SELECT * FROM website_content ORDER BY section_name";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $website_content[] = $row;
}

// Get user counts
$result = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
while ($row = $result->fetch_assoc()) {
    if ($row['role'] === 'patient') $stats['total_patients'] = $row['count'];
    if ($row['role'] === 'doctor') $stats['total_doctors'] = $row['count'];
}

// Get appointment statistics
$result = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN DATE(appointment_date) = CURDATE() THEN 1 ELSE 0 END) as today,
    SUM(CASE WHEN appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as monthly
    FROM appointments");
$row = $result->fetch_assoc();
$stats['total_appointments'] = $row['total'];
$stats['pending_appointments'] = $row['pending'];
$stats['today_appointments'] = $row['today'];
$stats['monthly_appointments'] = $row['monthly'];

// Get product and order statistics
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$stats['total_products'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT 
    COUNT(*) as count, 
    SUM(total_amount) as total_revenue,
    SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN total_amount ELSE 0 END) as monthly_revenue
    FROM orders WHERE status = 'delivered'");
$row = $result->fetch_assoc();
$stats['total_orders'] = $row['count'];
$stats['revenue'] = $row['total_revenue'] ?? 0;
$stats['monthly_revenue'] = $row['monthly_revenue'] ?? 0;

// Get recent appointments with more details
$recent_appointments = [];
$sql = "SELECT a.*, p.name as patient_name, p.phone as patient_phone, d.name as doctor_name, doc.specialization
        FROM appointments a 
        JOIN users p ON a.patient_id = p.id 
        JOIN users d ON a.doctor_id = d.id 
        JOIN doctors doc ON doc.user_id = d.id
        ORDER BY a.created_at DESC LIMIT 5";
$result = $conn->query($sql);

if ($result === false) {
    // Log or display the error
    error_log("Query failed: " . $conn->error);
    $recent_appointments = []; // Set empty array as fallback
} else {
    while ($row = $result->fetch_assoc()) {
        $recent_appointments[] = $row;
    }
}


// Get recent orders with product details
$recent_orders = [];
$sql = "SELECT o.*, u.name as customer_name, u.phone as customer_phone,
        GROUP_CONCAT(p.name SEPARATOR ', ') as products
        FROM orders o 
        JOIN users u ON o.customer_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        GROUP BY o.id
        ORDER BY o.created_at DESC LIMIT 5";
$result = $conn->query($sql);
if ($result === false) {
    // Log or display the error
    error_log("Query failed: " . $conn->error);
    $recent_appointments = []; // Set empty array as fallback
} else {
    while ($row = $result->fetch_assoc()) {
        $recent_appointments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Trinetra Eye Care</title>
    <link href="../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f3d56;
            --success-color: #2dd4bf;
            --warning-color: #fbbf24;
            --danger-color: #ef4444;
            --info-color: #60a5fa;
            --dark-bg: #f8fafc;
            --sidebar-dark: #ffffff;
            --card-border: rgba(0, 0, 0, 0.05);
            --card-hover-border: rgba(67, 97, 238, 0.3);
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.05);
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3730a3 100%);
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-primary);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.5;
        }

        .stat-card {
            background: var(--sidebar-dark);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            color: var(--text-primary);
            padding: 1.75rem;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--card-hover-border);
            box-shadow: 0 8px 15px rgba(67, 97, 238, 0.15);
        }
        
        .stat-icon {
            font-size: 2rem;
            opacity: 0.9;
            color: var(--primary-color);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            background: rgba(67, 97, 238, 0.1);
            padding: 12px;
            border-radius: 12px;
        }
        
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) var(--dark-bg);
            padding: 1rem;
            background: var(--sidebar-dark);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            margin-top: 1.5rem;
        }
        
        .activity-item {
            border-left: 3px solid var(--primary-color);
            padding: 1rem;
            margin-bottom: 1rem;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            color: #333;
        }
        
        .quick-action-btn {
            border-radius: 15px;
            padding: 1.25rem;
            text-align: center;
            transition: all 0.3s ease;
            background: #ffffff;
            border: 1px solid var(--card-border);
            color: #333;
            text-decoration: none;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            border-color: var(--primary-color);
        }
        
        .quick-action-icon {
            font-size: 2.25rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover .quick-action-icon {
            transform: scale(1.1);
            color: #fff;
        }
    </style>
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/admin.css" rel="stylesheet">
    <link href="/assets/css/admin-nav.css" rel="stylesheet">
    <style>
        .stat-card {
            background: var(--sidebar-dark);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            color: var(--text-primary);
            padding: 1.75rem;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--card-hover-border);
            box-shadow: 0 8px 12px rgba(67, 97, 238, 0.15);
        }
        .stat-icon {
            font-size: 1.75rem;
            opacity: 0.9;
            color: var(--primary-color);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
            background: rgba(67, 97, 238, 0.1);
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1);
            opacity: 1;
        }
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) var(--dark-bg);
            padding-right: 8px;
        }

        .recent-activity::-webkit-scrollbar {
            width: 6px;
        }

        .recent-activity::-webkit-scrollbar-track {
            background: var(--dark-bg);
            border-radius: 10px;
        }

        .recent-activity::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar" style="background: var(--sidebar-dark); box-shadow: var(--shadow-sm); border-right: 1px solid var(--card-border);">
                <div class="text-center mb-4 py-4">
                    <img src="logo.png" alt="Logo" height="50" class="mb-3">
                    <h5 class="fw-bold mb-0" style="color: var(--text-primary);">Trinetra Eye Care</h5>
                    <p class="text-muted small mb-0">Admin Dashboard</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="doctors.php">
                            <i class="fas fa-user-md"></i>Doctors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="patients.php">
                            <i class="fas fa-users"></i>Patients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">
                            <i class="fas fa-calendar-check"></i>Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medical-history.php">
                            <i class="fas fa-notes-medical"></i>Medical History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-glasses"></i>Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-cart"></i>Orders
                        </a>
                    </li>
                  
                    <li class="nav-item">
                        <a class="nav-link" href="contact-messages.php">
                             <i class="fa-solid fa-message"></i>Contact Messages
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </li>

                </ul>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-4 py-4">
                <!-- Dashboard Header -->
                <div class="dashboard-header rounded-xl p-4 mb-4" style="background: linear-gradient(135deg, var(--primary-color) 0%, #2e59d9 100%); box-shadow: var(--shadow-lg);">
                    <div class="position-absolute top-0 end-0 mt-2 me-2">
                        <div class="d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-2">
                            <i class="fas fa-calendar-alt text-white me-2"></i>
                            <span class="text-white"><?php echo date('F j, Y'); ?></span>
                        </div>
                       
                    </div>
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h2 class="mb-2 text-white fw-bold">Welcome back, <?php echo htmlspecialchars($user['name']); ?>! 👋</h2>
                            <p class="text-white-50 mb-0">Here's your activity overview for Trinetra Eye Care</p>
                        </div>
                        <div class="col-lg-4">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="p-3 rounded-lg" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                                        <div class="small text-white-50 mb-1">Today's Appointments</div>
                                        <h3 class="mb-0 text-white d-flex align-items-center fw-bold">
                                            <i class="fas fa-calendar-day me-2" style="color: var(--success-color);"></i>
                                            <?php echo $stats['today_appointments']; ?>
                                        </h3>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 rounded-lg" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                                        <div class="small text-white-50 mb-1">Pending Actions</div>
                                        <h3 class="mb-0 text-white d-flex align-items-center fw-bold">
                                            <i class="fas fa-clock me-2" style="color: var(--warning-color);"></i>
                                            <?php echo $stats['pending_appointments']; ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Overview -->
                <div class="row g-4 mb-4">
                    <!-- Patient Stats -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card" style="background: var(--gradient-primary); color: white;">
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <a href="../admin/patients.php" class="text-white">
                                    <h6 class="text-uppercase mb-0">Total Patients</h6></a>
                                    <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <h2 class="mb-2 text-white"><?php echo number_format($stats['total_patients']); ?></h2>
                                <div class="d-flex align-items-center text-white-50 small">
                                    <i class="fas fa-chart-line me-2"></i>
                                    <span>Active patient records</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Doctor Stats -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%); color: white;">
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                <a href="../admin/doctors.php" class="text-white">
                                    <h6 class="text-uppercase mb-0">Total Doctors</h6></a>
                                    <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                </div>
                                <h2 class="mb-2 text-white"><?php echo number_format($stats['total_doctors']); ?></h2>
                                <div class="d-flex align-items-center text-white-50 small">
                                    <i class="fas fa-stethoscope me-2"></i>
                                    <span>Registered specialists</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appointment Stats -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); color: white;">
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                <a href="../admin/appointments.php" class="text-white">
                                    <h6 class="text-uppercase mb-0">Appointments</h6></a>
                                    <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                </div>
                                <h2 class="mb-2 text-white"><?php echo number_format($stats['total_appointments']); ?></h2>
                                <div class="d-flex align-items-center text-white-50 small">
                                    <i class="fas fa-clock me-2"></i>
                                    <span>Today: <?php echo number_format($stats['today_appointments']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Revenue Stats -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #2dd4bf 0%, #0d9488 100%); color: white;">
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-uppercase mb-0">Monthly Revenue</h6>
                                    <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                                <h2 class="mb-2 text-white">₹<?php echo number_format($stats['monthly_revenue'], 2); ?></h2>
                                <div class="d-flex align-items-center text-white-50 small">
                                    <i class="fas fa-coins me-2"></i>
                                    <span>Total: ₹<?php echo number_format($stats['revenue'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card" style="background: linear-gradient(135deg, #2dd4bf 0%, #0d9488 100%); color: white;">
                            <div class="d-flex flex-column" >
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-uppercase mb-0">Total Revenue</h6>
                                        <i class="fas fa-rupee-sign stat-icon"></i>
                                </div>
                                <h2 class="mb-0">₹<?php echo number_format($stats['revenue'], 2); ?></h2>
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="fas fa-coins me-2"></i>
                                    <span>Total: ₹<?php echo number_format($stats['revenue'], 2); ?></span>
                                </div>
                            </div>
                            
                            </div>
                    </div>



                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card" style="background: linear-gradient(135deg,rgb(211, 162, 70) 0%,rgb(101, 71, 175) 100%); color: white;">
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    
                                    <a href="../admin/products.php" class="text-white">
                                        <h6 class="text-uppercase mb-0">Total Products</h6></a>
                                        <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;">
                                        <i class="fas fa-glasses stat-icon"></i>
                                        </div>    
                                </div>
                                        <h2 class="mb-0"><?php echo $stats['total_products']; ?></h2>
                                    <div class="d-flex align-items-center text-white-50 small">
                                   
                                </div>
                            </div>
                        </div>
                    </div><div class="col-xl-3 col-md-6">
    <div class="stat-card" style="background: linear-gradient(135deg,rgb(211, 162, 70) 0%,rgb(206, 158, 69) 100%); color: white;">
        <div class="d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../admin/orders.php" class="text-white">
                    <h6 class="text-uppercase mb-0">Total Orders</h6>
                </a>
                <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;">
                    <i class="fas fa-shopping-cart stat-icon"></i>
                </div>
            </div>
            <h2 class="mb-0"><?php echo $stats['total_orders']; ?></h2>
            <div class="d-flex align-items-center text-white-50 small">
                <span>Total: ₹<?php echo number_format($stats['total_orders'], 2); ?></span>
            </div>
        </div>
    </div>
</div>
                </div>
              

                <!-- Quick Actions -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <a href="appointments.php?status=pending" class="stat-card text-decoration-none h-100">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-plus text-primary me-3" style="font-size: 2rem;"></i>
                                <div>
                                    <h6 class="mb-1">New Appointment</h6>
                                    <p class="text-muted small mb-0">Schedule consultation</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="patients.php?action=add" class="stat-card text-decoration-none h-100">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-plus text-success me-3" style="font-size: 2rem;"></i>
                                <div>
                                    <h6 class="mb-1">Add Patient</h6>
                                    <p class="text-muted small mb-0">Register new patient</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="products.php?action=add" class="stat-card text-decoration-none h-100">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-glasses text-info me-3" style="font-size: 2rem;"></i>
                                <div>
                                    <h6 class="mb-1">Add Product</h6>
                                    <p class="text-muted small mb-0">Update inventory</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="doctors.php?action=schedule" class="stat-card text-decoration-none h-100">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock text-warning me-3" style="font-size: 2rem;"></i>
                                <div>
                                    <h6 class="mb-1">Doctor Schedule</h6>
                                    <p class="text-muted small mb-0">Manage timings</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Activity Overview -->
                <!-- <div class="row g-4"> -->
                    <!-- Recent Appointments -->
                    <!-- <div class="col-xl-8 col-lg-7">
                        <div class="card shadow h-100" style="background: var(--sidebar-dark); border: 1px solid var(--card-border);">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: var(--sidebar-dark); border-bottom: 1px solid var(--card-border);">
                                <div>
                                    <h6 class="m-0 font-weight-bold text-white">Recent Appointments</h6>
                                    <p class="text-muted small mb-0">Latest patient consultations</p>
                                </div>
                                <a href="appointments.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover text-white mb-0">
                                        <thead class="bg-dark">
                                            <tr>
                                                <th class="px-4">Patient</th>
                                                <th>Doctor</th>
                                                <th>Date & Time</th>
                                                <th class="text-end px-4">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody> -->
                                     
                                                    <!-- </span>
                                                </td>
                                            </tr>
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <!-- Recent Orders -->
                    <!-- <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4" style="background: var(--sidebar-dark); border: 1px solid var(--card-border);">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: var(--sidebar-dark); border-bottom: 1px solid var(--card-border);">
                                <h6 class="m-0 font-weight-bold text-black">Recent Orders</h6>
                                <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
                           
                            </div>
                        </div>
                    </div> 
                </div>-->
        <!-- Dashboard Header -->
        <!-- <div class="dashboard-header mb-4">
            <div class="container-fluid">
                <h1 class="h3 mb-0">Welcome to Trinetra Eye Care Admin</h1>
                <p class="mb-0">Monitor and manage your eye care center operations</p>
            </div>
        </div> -->

        <!-- Quick Actions-->
         <!--
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <a href="/admin/appointments.php?status=pending" class="quick-action-btn d-block text-decoration-none">
                    <div class="quick-action-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <h6 class="mb-0">New Appointment</h6>
                </a>
            </div> 
            <div class="col-md-3">
                <a href="/admin/patients.php?action=add" class="quick-action-btn d-block text-decoration-none">
                    <div class="quick-action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h6 class="mb-0">Add Patient</h6>
                </a>
            </div>
            <div class="col-md-3">
                <a href="/admin/products.php?action=add" class="quick-action-btn d-block text-decoration-none">
                    <div class="quick-action-icon">
                        <i class="fas fa-glasses"></i>
                    </div>
                    <h6 class="mb-0">Add Product</h6>
                </a>
            </div>
            <div class="col-md-3">
                <a href="/admin/doctors.php?action=schedule" class="quick-action-btn d-block text-decoration-none">
                    <div class="quick-action-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h6 class="mb-0">Doctor Schedule</h6>
                </a>
            </div>
        </div> -->

<!-- Statistics Overview -->
                <!-- <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-2">Total Patients</h6>
                                    <h2 class="mb-0"></h2>
                                </div>
                                <i class="fas fa-users stat-icon"></i>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="col-xl-3 col-md-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-2">Total Appointments</h6>
                                    <h2 class="mb-0"></h2>
                                </div>
                                <i class="fas fa-calendar-check stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white shadow stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white">Today's Appointments</h6>
                                        <h2 class="mb-0"></h2>
                                    </div>
                                    <i class="fas fa-calendar-day stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white shadow stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white">Pending Appointments</h6>
                                        <h2 class="mb-0"</h2>
                                    </div>
                                    <i class="fas fa-clock stat-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->

                    <!-- <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-purple text-white shadow stat-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white">Monthly Revenue</h6>
                                            <h2 class="mb-0">₹/h2>
                                        </div>
                                        <i class="fas fa-chart-line stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div> -->
    <!-- </div> -->
    

                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Appointments</h6>
                                <a href="../admin/appointments.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body recent-activity-card">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Patient</th>
                                                <th>Doctor</th>
                                                <th>Date & Time</th>
                                                <th>Contact</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_appointments as $appointment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($appointment['doctor_name']); ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($appointment['doctor_specialization']); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></small>
                                                </td>
                                                <td>
                                                    <a href="tel:<?php echo htmlspecialchars($appointment['patient_phone']); ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge status-badge bg-<?php echo $appointment['status'] === 'confirmed' ? 'success' : ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                                <a href="/admin/orders.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body recent-activity-card">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Customer</th>
                                                <th>Products</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($order['customer_name']); ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <a href="tel:<?php echo htmlspecialchars($order['customer_phone']); ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($order['customer_phone']); ?>
                                                        </a>
                                                    </small>
                                                </td>
                                                <td>
                                                    <small><?php echo htmlspecialchars($order['products']); ?></small>
                                                </td>
                                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <?php echo date('d M Y', strtotime($order['created_at'])); ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo date('h:i A', strtotime($order['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge status-badge bg-<?php echo $order['status'] === 'delivered' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'info'); ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
          
        


    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Website Content Management</h6>
                    <a href="content.php" class="btn btn-sm btn-primary">Manage Content</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Section</th>
                                    <th>Content</th>
                                    <th>Last Updated</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($website_content as $content): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($content['section_name']); ?></td>
                                    <td><?php echo substr(htmlspecialchars($content['content']), 0, 100) . '...'; ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($content['updated_at'])); ?></td>
                                    <td>
                                        <a href="content.php?section=<?php echo urlencode($content['section_name']); ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>