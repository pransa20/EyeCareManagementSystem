<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
$auth->logout();

// Clear all session data
$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
session_destroy();

// Redirect to the patient login page
header('Location: login.php');
exit;