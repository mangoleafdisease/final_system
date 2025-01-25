<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if the user is logged in and is a customer
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

include '../db.php';

// Validate selected items
$selectedItems = $_POST['selected_items'] ?? [];
if (empty($selectedItems)) {
    echo json_encode(['status' => 'error', 'message' => 'No items selected for checkout.']);
    exit();
}

// Filter and validate item IDs
$selectedItems = array_filter($selectedItems, 'ctype_digit');
if (empty($selectedItems)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid items selected.']);
    exit();
}

try {
    // Database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Begin transaction
    $pdo->beginTransaction();

    // Fetch selected cart items
    $placeholders = rtrim(str_repeat('?,', count($selectedItems)), ',');
    $stmt = $pdo->prepare("SELECT * FROM customer_cart WHERE id IN ($placeholders) AND customer_id = ?");
    $stmt->execute(array_merge($selectedItems, [$_SESSION['id']]));
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        throw new Exception("No valid items found for checkout.");
    }

    // Calculate total amount
    $totalAmount = array_reduce($cartItems, function ($sum, $item) {
        return $sum + ($item['price'] * $item['quantity']);
    }, 0);

    $itemNames = implode(', ', array_map(function($item) {
        return $item['item_name'];
    }, $cartItems));
    
    // Insert order into customer_orders
    $stmt = $pdo->prepare("INSERT INTO customer_orders (customer_id, customer_name, total_amount, item_name, order_date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['id'], $_SESSION['customer_name'], $totalAmount, $itemNames]);
    $orderId = $pdo->lastInsertId(); 

   
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, item_name, size, quantity, price) VALUES (?, ?, ?, ?, ?)");
    foreach ($cartItems as $item) {
        $stmt->execute([
            $orderId,
            $item['item_name'],
            $item['size'],
            $item['quantity'],
            $item['price']
        ]);
        
               
        $transactionStmt = $pdo->prepare("INSERT INTO transactions (order_id, item_id, customer_name, customer_id, item_name, size, quantity, total_price, purchase_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $transactionStmt->execute([
            $orderId,                               
            $item['item_id'],                     
            $_SESSION['customer_name'],             
            $_SESSION['id'],                        
            $item['item_name'],                                     
            $item['size'],  
            $item['quantity'],                      
            $item['price'] * $item['quantity'],     
        ]);

    }

   
    $stmt = $pdo->prepare("DELETE FROM customer_cart WHERE id IN ($placeholders)");
    $stmt->execute($selectedItems);

   
    $pdo->commit();

   
    header("Location: confirmation_page.php?order_id=" . $orderId);
    exit();
} catch (Exception $e) {
    
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    
    die("Error during checkout: " . $e->getMessage());
}
?>
