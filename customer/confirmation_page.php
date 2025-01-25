<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'customer') {
    header("Location: ../home.php");
    exit();
}


$orderId = $_GET['order_id'] ?? null;

if (!$orderId || !ctype_digit($orderId)) {
    die("Invalid order ID.");
}

echo "<h1>Order Confirmation</h1>";
echo "<p>Thank you for your purchase! Your order ID is: " . htmlspecialchars($orderId) . "</p>";
echo '<a href="customer_added_cart.php">OK</a>';
?>
