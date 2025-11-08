<?php
session_start();
require_once __DIR__ . '/includes/Auth.php';

$auth = new Auth();
// Store the role before logout for redirection
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$auth->logout();

// Determine the appropriate login page based on role
switch ($role) {
    case 'doctor':
        $login_page = '/project_eye_care/doctor/login.php';
        break;
    case 'admin':
        $login_page = '/project_eye_care/admin/login.php';
        break;
    case 'patient':
        $login_page = '/project_eye_care/login.php';
        break;
    case 'customer':
            $login_page = '/project_eye_care/customer-login.php';
            break;
    default:
        $login_page = '/project_eye_care/login.php';
}

header('Location: ' . $login_page);
exit;