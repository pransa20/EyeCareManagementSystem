<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// class Auth {
//     private $conn;
//     private $mail;

//     public function __construct() {
//         global $conn;
//         $this->conn = $conn;
//         $this->ensureConnection();
//         $this->initializeMailer();
//     }

//     private function initializeMailer() {
//         try {
//             $this->mail = new PHPMailer(true);
//             $this->mail->isSMTP();
//             $this->mail->Host = 'smtp.gmail.com';
//             $this->mail->SMTPAuth = true;
//             $this->mail->Username = 'eyecaretrinetra@gmail.com';
//             $this->mail->Password = 'pomm yfiu rtvd crdm'; // Updated Gmail App Password
//             $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//             $this->mail->Port = 587;
//             $this->mail->setFrom('eyecaretrinetra@gmail.com', 'Trinetra Eye Care');
//             $this->mail->SMTPDebug = 2; // Enable moderate debugging
//             $this->mail->Debugoutput = function($str, $level) {
//                 error_log("SMTP Debug: $str");
//             };
//             $this->mail->Timeout = 30; // Reduced timeout for faster failure detection
//             $this->mail->SMTPKeepAlive = true;
//             $this->mail->CharSet = 'UTF-8';
//             $this->mail->Encoding = 'base64';
//             $this->mail->isHTML(true);
//             $this->mail->SMTPOptions = array(
//                 'ssl' => array(
//                     'verify_peer' => true,
//                     'verify_peer_name' => true,
//                     'allow_self_signed' => false
//                 )
//             );
            
//             // Test SMTP connection
//             if (!$this->mail->smtpConnect()) {
//                 throw new Exception('SMTP Connection Failed: ' . $this->mail->ErrorInfo);
//             }
//         } catch (Exception $e) {
//             error_log('Mailer initialization failed: ' . $e->getMessage());
//             throw new Exception('Email service is currently unavailable. Please try again later.');
//         }
//     }
class Auth {
    private $conn;
    private $mail;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
        $this->ensureConnection();
        // Don't initialize mailer in constructor
    }

    private function initializeMailer() {
        if ($this->mail !== null) {
            return; // Return if already initialized
        }

        try {
            $this->mail = new PHPMailer(true);
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'eyecaretrinetra@gmail.com';
            $this->mail->Password = 'pomm yfiu rtvd crdm';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 587;
            $this->mail->setFrom('eyecaretrinetra@gmail.com', 'Trinetra Eye Care');
            $this->mail->SMTPDebug = 0; // Disable debugging
            $this->mail->Timeout = 30;
            $this->mail->SMTPKeepAlive = true;
            $this->mail->CharSet = 'UTF-8';
            $this->mail->Encoding = 'base64';
            $this->mail->isHTML(true);
            $this->mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false
                )
            );
            
            // Remove SMTP connection test
        } catch (Exception $e) {
            error_log('Mailer initialization failed: ' . $e->getMessage());
            // Don't throw exception, just log it
        }
    }
    public function registerCustomer($email, $password, $name, $phone = '') {
        return $this->register($email, $password, $name, $phone, 'customer');
    }

    public function loginCustomer($email, $password) {
        $result = $this->login($email, $password);
        if ($result['success'] && $result['role'] === 'customer') {
            return $result;
        }
        return ['success' => false, 'message' => 'Invalid credentials or not a customer account'];
    }

    public function register($email, $password, $name, $phone = '', $role = 'patient') {
        try {
            // Validate and sanitize email
            $email = trim($email);
            
            // Remove any whitespace and validate the email format
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Please enter a valid email address'];
            }
            
            // Additional validation for common email providers
            $domain = strtolower(substr(strrchr($email, "@"), 1));
            $commonDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
            if (!in_array($domain, $commonDomains) && !checkdnsrr($domain, 'MX')) {
                return ['success' => false, 'message' => 'Please enter a valid email address with a working mail server'];
            }

            // Validate password
            if (strlen($password) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters long'];
            }

            // Validate phone number
            if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
                return ['success' => false, 'message' => 'Please enter a valid 10-digit phone number'];
            }

            if ($this->emailExists($email)) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            $this->conn->begin_transaction();

            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $verificationCode = sprintf('%06d', mt_rand(0, 999999));

                $stmt = $this->conn->prepare("INSERT INTO users (email, password, name, phone, role, verification_code, email_verified) VALUES (?, ?, ?, ?, ?, ?, FALSE)");
                $stmt->bind_param("ssssss", $email, $hashedPassword, $name, $phone, $role, $verificationCode);

                if (!$stmt->execute()) {
                    throw new Exception('Failed to create user account');
                }

                $this->sendVerificationEmail($email, $verificationCode);
                $this->conn->commit();

                return ['success' => true, 'message' => 'Registration successful. Please check your email for verification code.'];
            } catch (Exception $e) {
                $this->conn->rollback();
                throw $e;
            }
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again later.'];
        }
    }

    public function login($email, $password) {
        try {
            // Clean and validate input
            $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            // Special handling for admin and doctor accounts
            if ($email === 'admin@admin.com' && $password === 'pranisha') {
                $hashedPassword = password_hash('pranisha', PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare("UPDATE users SET password = ?, email_verified = TRUE WHERE email = 'admin@admin.com'");
                $stmt->bind_param('s', $hashedPassword);
                $stmt->execute();
            }
         

            // Check if user exists and get their details
            $stmt = $this->conn->prepare("SELECT id, password, role, email_verified, name FROM users WHERE email = ? AND role != 'disabled'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Start a new session
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    session_regenerate_id(true);

                    // Set common session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['LAST_ACTIVITY'] = time();
                    $_SESSION['is_authenticated'] = true; // Set authenticated by default

                    // Set role-specific timeout durations
                    switch($user['role']) {
                        case 'admin':
                            $_SESSION['TIMEOUT_DURATION'] = 900; // 15 minutes
                            break;
                        case 'doctor':
                            $_SESSION['TIMEOUT_DURATION'] = 7200; // 2 hours
                            break;
                        default:
                            $_SESSION['TIMEOUT_DURATION'] = 3600; // 1 hour
                    }

                    // Role-specific verification checks
                    if ($user['role'] !== 'doctor' && !$user['email_verified']) {
                        $_SESSION['is_authenticated'] = false;
                        return ['success' => false, 'message' => 'Please verify your email first'];
                    }

                    return ['success' => true, 'role' => $user['role']];
                }
            }

            return ['success' => false, 'message' => 'Invalid email or password'];
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login. Please try again.'];
        }
    }

    public function isLoggedIn() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Check session timeout for all users
        if (isset($_SESSION['LAST_ACTIVITY']) && isset($_SESSION['TIMEOUT_DURATION'])) {
            $inactive = time() - $_SESSION['LAST_ACTIVITY'];
            if ($inactive >= $_SESSION['TIMEOUT_DURATION']) {
                $this->logout();
                return false;
            }
            $_SESSION['LAST_ACTIVITY'] = time();
        } else {
            // If session variables are not set properly, consider the session invalid
            $this->logout();
            return false;
        }

        // Additional check for session validity
        if (!isset($_SESSION['user_role'])) {
            $this->logout();
            return false;
        }

        return true;
    }
    public function getCurrentUser () {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function verifyEmail($email, $code) {
        if (empty($email) || empty($code)) {
            return false;
        }

        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND verification_code = ? AND email_verified = FALSE");
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt = $this->conn->prepare("UPDATE users SET email_verified = TRUE, verification_code = NULL WHERE email = ?");
            $stmt->bind_param("s", $email);
            $success = $stmt->execute();
            
            if ($success) {
                // Clear verification session data
                if (isset($_SESSION['verification_email'])) {
                    unset($_SESSION['verification_email']);
                }
                return true;
            }
        }

        return false;
    }

    private function ensureConnection() {
        if (!$this->conn->ping()) {
            error_log("MySQL connection lost. Attempting to reconnect...");
            $this->conn->close();
            global $conn;
            $conn = connectDatabase();
            $this->conn = $conn;
        }
    }

    public function emailExists($email) {
        $this->ensureConnection();
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private function sendVerificationEmail($email, $code) {
        try {
            // Initialize mailer if not already initialized
            $this->initializeMailer();
            
            if (!$this->mail) {
                error_log('Mailer not initialized properly');
                throw new Exception('Email service is not properly configured');
            }

            // Rest of the method remains the same
            $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error_log('Invalid email address: ' . $email);
                throw new Exception('Invalid email address provided');
            }

            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->addAddress($email);
            $this->mail->Subject = 'Email Verification - Trinetra Eye Care';
            
            $emailBody = "<html><body>";
            $emailBody .= "<h2>Welcome to Trinetra Eye Care!</h2>";
            $emailBody .= "<p>Thank you for registering. Please use the verification code below to complete your registration:</p>";
            $emailBody .= "<h1 style='color: #4CAF50; font-size: 32px; letter-spacing: 2px;'>$code</h1>";
            $emailBody .= "<p>This code will expire in 24 hours.</p>";
            $emailBody .= "<p>If you did not request this verification, please ignore this email.</p>";
            $emailBody .= "<hr>";
            $emailBody .= "<p style='font-size: 12px; color: #666;'>This is an automated message, please do not reply.</p>";
            $emailBody .= "</body></html>";
            
            $this->mail->isHTML(true);
            $this->mail->Body = $emailBody;
            $this->mail->AltBody = "Your verification code is: $code\n\nPlease enter this code to verify your email address.";
            
            if (!$this->mail->send()) {
                error_log('Email sending failed. SMTP Error: ' . $this->mail->ErrorInfo);
                throw new Exception('Failed to send verification email. Please try again later.');
            }

            return true;

        } catch (Exception $e) {
            error_log('Error in sendVerificationEmail: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
    public function sendPasswordResetEmail($email) {
        if (!$this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email address not found.'];
        }

        $verificationCode = sprintf('%06d', mt_rand(0, 999999));
        $stmt = $this->conn->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
        $stmt->bind_param("ss", $verificationCode, $email);

        if ($stmt->execute()) {
            try {
                $this->mail->clearAddresses();
                $this->mail->clearAttachments();
                $this->mail->addAddress($email);
                $this->mail->Subject = 'Password Reset - Trinetra Eye Care';
                
                $emailBody = "<html><body>";
                $emailBody .= "<h2>Password Reset Request</h2>";
                $emailBody .= "<p>We received a request to reset your password. Please use the verification code below:</p>";
                $emailBody .= "<h1 style='color: #4CAF50; font-size: 32px; letter-spacing: 2px;'>$verificationCode</h1>";
                $emailBody .= "<p>This code will expire in 1 hour.</p>";
                $emailBody .= "<p>If you did not request this reset, please ignore this email.</p>";
                $emailBody .= "<hr>";
                $emailBody .= "<p style='font-size: 12px; color: #666;'>This is an automated message, please do not reply.</p>";
                $emailBody .= "</body></html>";
                
                $this->mail->isHTML(true);
                $this->mail->Body = $emailBody;
                $this->mail->AltBody = "Your password reset code is: $verificationCode\n\nPlease enter this code to reset your password.";
                
                if ($this->mail->send()) {
                    return ['success' => true, 'message' => 'Password reset code has been sent to your email.'];
                }
            } catch (Exception $e) {
                error_log('Password reset email exception: ' . $e->getMessage());
            }
        }
        
        return ['success' => false, 'message' => 'Failed to send reset code. Please try again later.'];
    }

    public function resetPassword($email, $code, $newPassword) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND verification_code = ?");
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE users SET password = ?, verification_code = NULL WHERE email = ?");
            $stmt->bind_param("ss", $hashedPassword, $email);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Password has been reset successfully!'];
            }
        }
        
        return ['success' => false, 'message' => 'Invalid or expired verification code.'];
    }

    public function logout() {
        // Store the user's role and logout time for proper redirection
        if (isset($_SESSION['user_role'])) {
            $_SESSION['last_role'] = $_SESSION['user_role'];
            $_SESSION['logout_time'] = time();
        }
        $last_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'patient';
        
        // Clear all session data
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Start a new session for temporary redirect data
        session_start();
        session_regenerate_id(true);
        $_SESSION['last_role'] = $last_role;
        $_SESSION['logout_time'] = time();
    }

    public function hasRole($role) {
        return $this->isLoggedIn() && $_SESSION['user_role'] === $role;
    }
}