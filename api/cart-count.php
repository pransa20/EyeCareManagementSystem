<?php
session_start();
require_once __DIR__ . '/../includes/Cart.php';

header('Content-Type: application/json');

$cart = new Cart();
$count = $cart->getItemCount();

echo json_encode(['count' => $count]);