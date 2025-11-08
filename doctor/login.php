<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
$error = '';

// Check if the user is already logged in
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    if ($user && $user['role'] === 'doctor') {
        header('Location: dashboard.php');
        exit;
    } else {
        // Invalid role, log them out and redirect
        $auth->logout();
        header('Location: login.php');
        exit;
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            $result = $auth->login($email, $password);
            if ($result['success']) {
                if ($result['role'] !== 'doctor') {
                    $error = 'Access denied. This login is for doctors only.';
                    $auth->logout();
                } else {
                    header('Location: dashboard.php');
                    exit;
                }
            
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
    <title>Doctor Login - Trinetra Eye Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include '../includes/header2.php'; ?>

    <div class="container py-5 mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-md fa-3x text-primary mb-3"></i>
                            <h2 class="text-primary">Doctor Login</h2>
                            <p class="text-muted">Access your medical dashboard</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="login" class="btn btn-primary btn-lg">
                                    
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer2.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>