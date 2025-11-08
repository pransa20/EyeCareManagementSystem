<?php
session_start();
require_once __DIR__ . '/includes/Auth.php';

$auth = new Auth();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $result = $auth->sendPasswordResetEmail($email);
            if ($result['success']) {
                $success = $result['message'];
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_time'] = time();
                header("Refresh: 2; URL=resetpatient.php");
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            error_log('Password reset error: ' . $e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .forgot-password-container {
            max-width: 450px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="forgot-password-container">
            <div class="text-center mb-4">
                <img src="logo.png" alt="Trinetra Eye Care Logo" style="height: 80px; width: auto;">
            </div>
            <h2 class="text-center mb-4">Forgot Password</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <div class="text-center mt-3">
                        <a href="resetpatient.php" class="btn btn-primary">Continue to Reset Password</a>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-center mb-4">Enter your email address and we'll send you a verification code to reset your password.</p>

                <form method="POST" action="" onsubmit="return validateForm();">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address">
                        <div id="emailError" class="invalid-feedback"></div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">Send Reset Code</button>
                    </div>

                    <p class="text-center mt-4 mb-0">
                        <a href="patient/login.php">Back to Login</a>
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function validateForm() {
        const email = document.getElementById('email');
        const emailError = document.getElementById('emailError');
        const submitBtn = document.getElementById('submitBtn');

        // Reset previous error states
        email.classList.remove('is-invalid');
        emailError.textContent = '';

        // Email validation
        const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!email.value.trim()) {
            email.classList.add('is-invalid');
            emailError.textContent = 'Please enter your email address.';
            return false;
        } else if (!emailPattern.test(email.value.trim())) {
            email.classList.add('is-invalid');
            emailError.textContent = 'Please enter a valid email address.';
            return false;
        }

        // Disable submit button to prevent multiple submissions
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
        return true;
    }
    </script>
</body>
</html>