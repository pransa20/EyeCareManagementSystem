<?php
session_start();
require_once __DIR__ . '/includes/Auth.php';

if (!isset($_SESSION['verification_email'])) {
    $_SESSION['error_message'] = 'Please register first to verify your email.';
    header('Location: register.php');
    exit;
}

$auth = new Auth();
$error = $success = '';
$email = $_SESSION['verification_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify'])) {
        $code = trim($_POST['verification_code'] ?? '');
        
        if (empty($code)) {
            $error = 'Please enter the verification code.';
        } elseif ($auth->verifyEmail($email, $code)) {
            $success = 'Email verified successfully! You can now login.';
            $_SESSION['success_message'] = 'Email verified successfully! Please login to continue.';
            header('Location: customer-login.php');
            exit();
        } else {
            $error = 'Invalid verification code. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5 mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Verify Your Email</h2>
                        
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

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="verification_code" class="form-label">Verification Code</label>
                                    <input type="text" class="form-control" id="verification_code" name="verification_code" required maxlength="6" pattern="[0-9]{6}" placeholder="Enter 6-digit code">
                                </div>

                                <div class="d-grid">
                                    <button type="submit" name="verify" class="btn btn-primary">Verify Email</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>