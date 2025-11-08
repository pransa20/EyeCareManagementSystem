<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
$auth->logout();

// Always redirect to the main login page for doctors
header('Location: login.php');
exit;