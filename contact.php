<?php
require_once __DIR__ . '/includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us- Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
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


    <div class="container mt-5 pt-5">
    <div class="row">
        <div class="col-md-6 mb-4">
            <h2 class="mb-4">Contact Us</h2>
            <p class="text-muted mb-4">We're here to help! Send us a message and we'll get back to you as soon as possible.</p>
            
            <form id="contactForm" class="contact-form needs-validation" method="POST" action="api/contact-submit.php" novalidate>
                <div class="mb-4">
                    <label for="name" class="form-label">Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="invalid-feedback">Please enter your name.</div>
                </div>
                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                <div class="mb-4">
                    <label for="subject" class="form-label">Subject</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-heading"></i></span>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="invalid-feedback">Please enter a subject.</div>
                </div>
                <div class="mb-4">
                    <label for="message" class="form-label">Message</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-comment"></i></span>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    <div class="invalid-feedback">Please enter your message.</div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Send Message
                    </button>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-map-marked-alt me-2"></i>Our Location</h5>
                    <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d29400.381935851494!2d83.97538743479003!3d28.210047200000003!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3995951567de4b8d%3A0x7e9affacb5748e44!2sTrinetra%20Eye%20Care%20Center!5e0!3m2!1sen!2snp!4v1710400844037!5m2!1sen!2snp"
                            width="100%"
                            height="300px"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    <!-- <div id="map" class="mb-3" style="height: 300px;"></div> -->
                    <p class="card-text">
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i> Prithivichowk 8, Pokhara 33700<br>
                        <i class="fas fa-phone me-2 text-primary"></i> +977-9827157317<br>
                        <i class="fas fa-envelope me-2 text-primary"></i> eyecaretrinetra@gmail.com
                    </p>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-clock me-2"></i>Business Hours</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-calendar-day me-2 text-primary"></i> Sunday - Friday: 9:00 AM - 6:00 PM</li>
                        <li class="mb-2"><i class="fas fa-calendar-day me-2 text-primary"></i> Saturday: Closed</li>
                        
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Google Maps JavaScript -->
<!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB41DRUbKWJHPxaFjMAwdrzWzbVKartNGg&callback=initMap" async defer></script> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    
    fetch('api/contact-submit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Message sent successfully!');
            form.reset();
        } else {
            alert(data.message || 'An error occurred. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again later.');
    });
});
</script>
</body>
</html>

<?php
require_once __DIR__ . '/includes/footer.php';
?>