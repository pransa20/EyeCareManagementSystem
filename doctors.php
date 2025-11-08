<?php
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/config/database.php';

$auth = new Auth();
$currentUser  = $auth->isLoggedIn() ? $auth->getCurrentUser () : null;

// Fetch all active doctors from the database
$doctors_query = "SELECT u.id, u.name, u.role, d.specialization FROM users u LEFT JOIN doctors d ON u.id = d.user_id WHERE u.role = 'doctor' AND u.status = 'active' ORDER BY u.name";
$doctors_result = $conn->query($doctors_query);

if ($doctors_result === false) {
    die("Error executing query: " . $conn->error);
}

$doctors = [];
while ($row = $doctors_result->fetch_assoc()) {
    $doctors[] = $row;
}

// Adding more doctors with Nepali names
// $additional_doctors = [


//     [
//         'id' => 103,
//         'name' => 'Dr. Ramesh Adhikari',
//         'specialization' => 'Cataract Surgery',
//         'description' => 'Advanced surgical procedures for cataract removal and lens replacement.'
//     ],

   
//     [
//         'id' => 105,
//         'name' => 'Dr. Aditi Bhandari',
//         'specialization' => 'Vision Therapy, Contact Lens Fitting ',
//         'description' => 'Specialized exercises and treatments to improve visual function.'
//     ],
//     [
//         'id' => 106,
//         'name' => 'Dr. Bipin Ghimire',
//         'specialization' => 'Neuro-Ophthalmology',
//         'description' => 'Treatment of visual problems related to the nervous system.'
//     ],
//     [
//         'id' => 107,
//         'name' => 'Dr. Kavita Shrestha',
//         'specialization' => 'Pediatric Ophthalmology',
//         'description' => 'Specialized eye care services for children.'
//     ],
    
//     [
//         'id' => 108,
//         'name' => 'Dr. Manisha Rathi',
//         'specialization' => 'Uveitis Specialist',
//         'description' => 'Specialized care for inflammatory eye conditions.'
//     ],
//     [
//         'id' => 109,
//         'name' => 'Dr. Sandeep Baral',
//         'specialization' => 'Ocular Imaging',
//         'description' => 'Advanced imaging technologies for precise diagnosis.'
//     ],


// ];
// Initialize additional doctors array if not using hardcoded values
$additional_doctors = [];

// Merge the additional doctors with the existing ones
$doctors = array_merge($doctors, $additional_doctors);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Doctors - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        /* Navigation */
        .navbar {
            transition: all 0.3s ease;
            padding: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            padding: 1rem 0;
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
            margin-right: 8px;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover img {
            transform: scale(1.05);
        }

        .nav-link {
            padding: 0.5rem 1rem !important;
            color: black;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: var(--primary-color);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 70%;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(17, 17, 17, 0.1);
            border-radius: 8px;
            padding: 0.5rem;
        }

        .dropdown-item {
            padding: 0.7rem 1.2rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: rgba(0, 123, 255, 0.1);
            color: var(--primary-color);
            transform: translateX(5px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <img src="logo.png" alt="Trinetra Eye Care Logo"> <span class="text-primary"></span> 
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.html">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="services.php">Our Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop2.php">Optical Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i>Login
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="loginDropdown" style="min-width: 200px;">
                                                        <li><a class="dropdown-item py-2 px-3 hover-primary" href="admin/login.php"><i class="fas fa-user-shield me-2"></i>Admin Login</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 px-3 hover-primary" href="doctor/login.php"><i class="fas fa-user-md me-2"></i>Doctor Login</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 px-3 hover-primary" href="patient/login.php"><i class="fas fa-user me-2"></i>Patient Login</a></li>
                        </ul>
                    </li>
                    <li class="nav-item ms-lg-2"><a class="btn btn-primary rounded-pill px-4" href="appointment.php">Book Appointment</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5 mt-5">
        <h1 class="text-center mb-5">Meet Our Expert Doctors</h1>
        
        <div class="row g-4">
            <?php foreach ($doctors as $doctor): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-user-md fa-4x text-primary"></i>
                        </div>
                        <h5 class="card-title"><?php echo htmlspecialchars($doctor['name']); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars($doctor['specialization'] ?? 'Ophthalmologist'); ?></p>
                        <?php if (isset($doctor['description'])): ?>
                            <p class="card-text"><?php echo htmlspecialchars($doctor['description']); ?></p>
                        <?php endif; ?>
                        <div class="d-grid">
                            <a href="appointment.php?doctor=<?php echo htmlspecialchars($doctor['id']); ?>" class="btn btn-primary">Book Appointment</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>