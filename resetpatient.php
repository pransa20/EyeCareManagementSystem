<?php
session_start();
require_once __DIR__ . '/includes/Auth.php';

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_time'])) {
    header('Location: forgot.php');
    exit;
}

// Check if reset token has expired (1 hour limit)
if (time() - $_SESSION['reset_time'] > 3600) {
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_time']);
    header('Location: forgot.php?expired=1');
    exit;
}

$auth = new Auth();
$error = $success = '';
$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_code'])) {
        $code = $_POST['verification_code'] ?? '';
        $password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($code) || empty($password) || empty($confirm_password)) {
            $error = 'Please fill in all fields.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            $result = $auth->resetPassword($email, $code, $password);
            if ($result['success']) {
                $success = $result['message'];
                unset($_SESSION['reset_email']);
                header("Refresh: 2; URL=patient/login.php");
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
    <title>Reset Password - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .reset-password-container {
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
        <div class="reset-password-container">
            <div class="text-center mb-4">
                <img src="logo.png" alt="Trinetra Eye Care Logo" style="height: 80px; width: auto;">
            </div>
            <h2 class="text-center mb-4">Reset Password</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <div class="text-center mt-3">
                        <a href="patient/login.php" class="btn btn-primary">Proceed to Login</a>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-center mb-4">
                    We've sent a verification code to your email address:<br>
                    <strong><?php echo htmlspecialchars($email); ?></strong>
                </p>

                <form method="POST" action="" onsubmit="return validateForm();">
                    <div class="mb-3">
                        <label for="verification_code" class="form-label">Verification Code</label>
                        <input type="text" class="form-control" id="verification_code" name="verification_code" required maxlength="6" pattern="[0-9]{6}" placeholder="Enter 6-digit code">
                        <div id="codeError" class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters">
                        <div id="passwordError" class="invalid-feedback"></div>
                        <div class="form-text">Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.</div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                        <div id="confirmError" class="invalid-feedback"></div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="verify_code" class="btn btn-primary" id="submitBtn">Reset Password</button>
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
        const code = document.getElementById('verification_code');
        const password = document.getElementById('new_password');
        const confirm = document.getElementById('confirm_password');
        const submitBtn = document.getElementById('submitBtn');
        let isValid = true;

        // Reset previous error states
        [code, password, confirm].forEach(input => input.classList.remove('is-invalid'));

        // Verification code validation
        if (!/^\d{6}$/.test(code.value)) {
            code.classList.add('is-invalid');
            document.getElementById('codeError').textContent = 'Please enter a valid 6-digit code.';
            isValid = false;
        }

        // Password validation
        const passwordPattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
        if (!passwordPattern.test(password.value)) {
            password.classList.add('is-invalid');
            document.getElementById('passwordError').textContent = 'Password must meet the requirements.';
            isValid = false;
        }

        // Confirm password validation
        if (password.value !== confirm.value) {
            confirm.classList.add('is-invalid');
            document.getElementById('confirmError').textContent = 'Passwords do not match.';
            isValid = false;
        }

        if (isValid) {
            // Disable submit button to prevent multiple submissions
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        }

        return isValid;
    }
    </script>
</body>
</html>