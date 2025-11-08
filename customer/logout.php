<?php
session_start();
session_destroy();

$redirect = $_GET['redirect'] ?? '../customer-login.php'; // Fallback if none
header("Location: $redirect");
exit;
