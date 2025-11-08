<?php
require_once __DIR__ . '/Auth.php';

$auth = new Auth();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password';
        } else {
            $result = $auth->login($email, $password);
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Login successful']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => $result['message']]);
                exit;
            }
        }
    } elseif (isset($_POST['register'])) {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = 'patient';

        if (empty($email) || empty($password) || empty($name) || empty($phone)) {
            $error = 'All fields are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
            $error = 'Please enter a valid 10-digit phone number';
        } else {
            $result = $auth->register($email, $password, $name, $phone, $role);
            if ($result['success']) {
                $_SESSION['verification_email'] = $email;
                echo json_encode(['success' => true, 'message' => 'Registration successful! Please verify your email.']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => $result['message']]);
                exit;
            }
        }
    }
}
?>
<div class="modal fade" id="loginRegisterModal" tabindex="-1" aria-labelledby="loginRegisterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <!-- Login Section -->
                    <div class="col-md-6 border-end">
                        <h2 class="mb-4">LOGIN</h2>
                        <form id="loginForm" method="POST" class="login-form">
                            <div class="mb-3">
                                <label for="login_email" class="form-label">Username or email address *</label>
                                <input type="email" class="form-control" id="login_email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="login_password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="login_password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <button type="submit" name="login" class="btn btn-dark w-100">LOG IN</button>
                            <div class="mt-3">
                                <a href="#" class="text-decoration-none text-dark">Lost your password?</a>
                            </div>
                        </form>
                    </div>

                    <!-- Register Section -->
                    <div class="col-md-6">
                        <h2 class="mb-4">REGISTER</h2>
                        <form id="registerForm" method="POST" class="register-form">
                            <div class="mb-3">
                                <label for="register_email" class="form-label">Email address *</label>
                                <input type="email" class="form-control" id="register_email" name="email" required>
                            </div>
                            <p class="text-muted mb-4">A link to set a new password will be sent to your email address.</p>
                            <div class="mb-3">
                                <label for="register_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="register_name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="register_phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="register_phone" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="register_password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="register_password" name="password" required>
                            </div>
                            <p class="text-muted small mb-4">Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our privacy policy.</p>
                            <button type="submit" name="register" class="btn btn-dark w-100">REGISTER</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '/includes/login-register.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            }
        });
    });

    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '/includes/login-register.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = '/verify.php';
                } else {
                    alert(response.message);
                }
            }
        });
    });
});
</script>