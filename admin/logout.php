<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
$auth->logout();

// Always redirect to admin login page when logging out from admin area
header('Location: login.php');
exit;