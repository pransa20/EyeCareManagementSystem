<?php
require_once __DIR__ . '/includes/Auth.php';
$auth = new Auth();
$currentUser = $auth->isLoggedIn() ? $auth->getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - Trinetra Eye Care</title>
   <link href="bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="fontawesome/css/all.min.css">
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
            <a class="navbar-brand" href="#">
                <img src="logo.png" alt="Trinetra Eye Care Logo"> <span class="text-primary"></span> 
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.html">About Us</a></li>
                    
                    <li class="nav-item"><a class="nav-link" href="doctors.php">Our Doctors</a></li>
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
        <h1 class="text-center mb-5">Our Eye Care Services</h1>
        
        <div class="row g-4" id="services-container">
            <!-- Service cards will be dynamically populated -->
        </div>
    </div>

    <!-- Service Details Modal -->
    <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="serviceModalBody">
                </div>
            </div>
        </div>
    </div>

    <script>
    const services = [
        {
            icon: 'fa-eye',
            title: 'Comprehensive Eye Examination',
            shortDesc: 'Complete eye health evaluation with advanced diagnostic techniques.',
            fullDesc: 'Our comprehensive eye examination includes detailed assessment of visual acuity, refraction, eye pressure measurement, retinal examination, and screening for common eye diseases. We use state-of-the-art equipment to ensure accurate diagnosis and appropriate treatment recommendations.'
        },
        {
            icon: 'fa-eye-dropper',
            title: 'Ocular Prosthesis Clinic',
            shortDesc: 'Specialized care for patients requiring artificial eyes and cosmetic solutions.',
            fullDesc: 'Our ocular prosthesis clinic provides expert care in the fitting and maintenance of artificial eyes. We offer customized prosthetic solutions that match your natural eye appearance, ensuring comfort and aesthetic satisfaction.'
        },
        {
            icon: 'fa-clinic-medical',
            title: 'Cataract Surgery',
            shortDesc: 'Advanced surgical procedures for cataract removal and lens replacement.',
            fullDesc: 'We offer state-of-the-art cataract surgery using the latest techniques and premium intraocular lenses. Our experienced surgeons provide personalized care throughout your treatment journey, from initial consultation to post-operative care.'
        },
        {
            icon: 'fa-microscope',
            title: 'Retina Service',
            shortDesc: 'Specialized care for retinal diseases and conditions.',
            fullDesc: 'Our retina service provides comprehensive care for conditions affecting the retina, including diabetic retinopathy, macular degeneration, and retinal detachment. We use advanced imaging and treatment technologies for optimal outcomes.'
        },
        {
            icon: 'fa-search',
            title: 'Glaucoma Clinic',
            shortDesc: 'Expert management of glaucoma with advanced monitoring.',
            fullDesc: 'Our glaucoma clinic offers comprehensive care including pressure monitoring, visual field testing, and advanced imaging. We provide both medical and surgical management options tailored to your specific condition.'
        },
        {
            icon: 'fa-brain',
            title: 'Vision Therapy/Amblyopia Therapy',
            shortDesc: 'Specialized exercises and treatments to improve visual function.',
            fullDesc: 'Our vision therapy program includes customized exercises and treatments to improve visual skills, depth perception, and eye coordination. We specialize in treating amblyopia (lazy eye) and other binocular vision disorders.'
        },
        {
            icon: 'fa-network-wired',
            title: 'Neuro Ophthalmology Service',
            shortDesc: 'Treatment of visual problems related to the nervous system.',
            fullDesc: 'Our neuro-ophthalmology service diagnoses and treats visual problems related to the brain, nerves, and muscles that control vision. We work closely with neurologists to provide comprehensive care for complex conditions.'
        },
        {
            icon: 'fa-child',
            title: 'Pediatric Clinic',
            shortDesc: 'Specialized eye care services for children.',
            fullDesc: 'Our pediatric clinic provides comprehensive eye care for children of all ages. We offer vision screening, amblyopia treatment, and management of pediatric eye conditions in a child-friendly environment.'
        },
        {
            icon: 'fa-eye',
            title: 'Cornea and External Eye Disease Clinic',
            shortDesc: 'Specialized care for corneal and external eye conditions.',
            fullDesc: 'We provide expert care for conditions affecting the cornea and external eye, including infections, injuries, and degenerative conditions. Our services include corneal transplantation and advanced treatment options.'
        },
        {
            icon: 'fa-microscope',
            title: 'Uveitis Clinic',
            shortDesc: 'Specialized care for inflammatory eye conditions.',
            fullDesc: 'Our uveitis clinic provides comprehensive care for inflammatory eye conditions. We offer advanced diagnostic testing and personalized treatment plans to manage both acute and chronic inflammation.'
        },
        {
            icon: 'fa-camera',
            title: 'Ocular Imaging',
            shortDesc: 'Advanced imaging technologies for precise diagnosis.',
            fullDesc: 'We utilize state-of-the-art imaging technologies including OCT, fundus photography, and fluorescein angiography to provide accurate diagnosis and monitoring of eye conditions.'
        },
        {
            icon: 'fa-glasses',
            title: 'Contact Lens Clinic',
            shortDesc: 'Expert fitting and care for all types of contact lenses.',
            fullDesc: 'Our contact lens clinic provides comprehensive services including fitting of specialty lenses, management of complex cases, and ongoing care for contact lens wearers.'
        },
        {
            icon: 'fa-user-md',
            title: 'Oculoplasty Clinic',
            shortDesc: 'Cosmetic and reconstructive eye surgery services.',
            fullDesc: 'Our oculoplastic surgery services include both cosmetic and reconstructive procedures for the eyelids, tear ducts, and orbital area. We provide personalized care for optimal aesthetic and functional outcomes.'
        },
        {
            icon: 'fa-low-vision',
            title: 'Low Vision Service',
            shortDesc: 'Specialized care for patients with reduced vision.',
            fullDesc: 'Our low vision service helps patients with reduced vision maximize their remaining sight through specialized devices, training, and rehabilitation strategies.'
        },
        {
            icon: 'fa-eye',
            title: 'Corneal Clinic',
            shortDesc: 'Specialized care for corneal conditions and treatments.',
            fullDesc: 'Our corneal clinic provides comprehensive care for corneal diseases, including advanced treatments, corneal transplantation, and management of complex cases.'
        }
    ];

    function createServiceCard(service) {
        return `
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm service-card" style="cursor: pointer;" onclick="showServiceDetails('${service.title}', '${service.fullDesc}')">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas ${service.icon} fa-4x text-primary"></i>
                        </div>
                        <h5 class="card-title">${service.title}</h5>
                        <p class="card-text">${service.shortDesc}</p>
                        <div class="text-primary mt-3">
                            <small>Click for more details</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function showServiceDetails(title, description) {
        document.getElementById('serviceModalLabel').textContent = title;
        document.getElementById('serviceModalBody').textContent = description;
        new bootstrap.Modal(document.getElementById('serviceModal')).show();
    }

    // Populate services
    document.getElementById('services-container').innerHTML = services.map(service => createServiceCard(service)).join('');
    </script>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>