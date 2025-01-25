<?php


// Check if the user is logged in and is a customer
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in first.']);
    header("Location: ../home.php");
    exit();
}
include '../db.php';

// Calculate total stock of all products
$totalstocksallproductsStmt = $conn->prepare("SELECT SUM(quantity) AS total_stock FROM inventory");
$totalstocksallproductsStmt->execute();
$totalstocksallproducts = $totalstocksallproductsStmt->fetchColumn() ?: 0; // Get total stock or 0 if none

// Query to fetch all products
$stmt = $conn->prepare("SELECT * FROM inventory");
$stmt->execute();

// Fetch product data
$products = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $products[] = $row;
}

// Return data as JSON
echo json_encode([
    'total_stock' => number_format($totalstocksallproducts, 0),
    'products' => $products
]);
