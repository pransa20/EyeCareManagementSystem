<?php
session_start();
require_once __DIR__ . '/includes/Auth.php';

require_once __DIR__ . '/includes/Database.php';
$auth = new Auth();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = 'patient';

        // Server-side validation
        if (empty($email) || empty($password) || empty($name) || empty($phone)) {
            $error = 'All fields are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
            $error = 'Please enter a valid 10-digit phone number';
        } else {
            $result = $auth->register($email, $password, $name, $phone, $role);
            if ($result['success']) {
                $_SESSION['verification_email'] = $email;
                $_SESSION['success_message'] = $result['message'];
                header('Location: verifypatient.php');
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
    <title>Patient Register - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5 mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                    <div class="text-center mb-4">
                    <i class="fas fa-user fa-3x text-primary mb-3"></i>
                        <h2 class="text-center mb-4">Patient Register - Create Account</h2>
</div>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
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
                                Already have an account? <a href="patient/login.php">Login here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

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