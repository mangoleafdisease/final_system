<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in and is a customer
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

include '../db.php';

// Read the JSON data sent via POST
$inputData = json_decode(file_get_contents('php://input'), true);

// Debugging: Log the input data for debugging purposes
error_log("Received data: " . print_r($inputData, true));

// Sanitize and validate selected items input
$selectedItems = array_filter($inputData['selected_items'] ?? [], 'ctype_digit'); // Ensure IDs are digits

// Debugging: Check the value of selectedItems
error_log("Selected Items: " . print_r($selectedItems, true));

if (empty($selectedItems)) {
    echo json_encode(['status' => 'error', 'message' => 'No items selected for checkout.']);
    exit();
}

try {
    // Establish database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Begin transaction for safety
    $pdo->beginTransaction();

    // Fetch selected cart items
    $placeholders = rtrim(str_repeat('?,', count($selectedItems)), ',');
    $stmt = $pdo->prepare("SELECT * FROM customer_cart WHERE id IN ($placeholders) AND customer_id = ?");
    $stmt->execute(array_merge($selectedItems, [$_SESSION['id']]));
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debugging: Check if items were fetched from the database
    error_log("Fetched Cart Items: " . print_r($cartItems, true));

    if (empty($cartItems)) {
        throw new Exception("No valid items found for checkout.");
    }

    // Calculate total amount
    $totalAmount = array_reduce($cartItems, function ($sum, $item) {
        return $sum + ($item['price'] * $item['quantity']);
    }, 0);

    // Retrieve customer's name
    $stmt = $pdo->prepare("SELECT customer_name FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['id']]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    $customerName = $customer['customer_name'] ?? 'Unknown';

    // Insert order into customer_orders table
    $stmt = $pdo->prepare("INSERT INTO customer_orders (customer_id, customer_name, total_amount) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['id'], $customerName, $totalAmount]);

    // Get the last inserted order ID
    $orderId = $pdo->lastInsertId();

    // Insert items into order_items table
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, item_id, item_name, item_size, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($cartItems as $item) {
        $stmt->execute([$orderId, $item['item_id'], $item['item_name'], $item['item_size'], $item['quantity'], $item['price']]);
    }

    // Commit the transaction
    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Order placed successfully!']);

} catch (Exception $e) {
    // Rollback on error
    $pdo->rollBack();
    // Log detailed error for debugging
    error_log("Error during checkout: " . $e->getMessage());
    // Return error with detailed message
    echo json_encode(['status' => 'error', 'message' => 'An error occurred during checkout. ' . $e->getMessage()]);
}
?>
