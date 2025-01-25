<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['all_logged_in']) || !isset($_SESSION['id']) || !isset($_SESSION['customer_name'])) {
    // Return JSON error response if the user is not logged in or session info is missing
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in first.']);
    exit();
}

// Include database connection
include '../db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Process the cart data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart = json_decode(file_get_contents('php://input'), true);

    if (empty($cart) || !is_array($cart)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid cart data.']);
        exit();
    }

    try {
        // Prepare insert statement for cart data
        $stmt = $pdo->prepare("
            INSERT INTO customer_cart 
            (cart_id, customer_id, customer_name, item_id, item_name, size, quantity, price, added_date) 
            VALUES (:cart_id, :customer_id, :customer_name, :item_id, :item_name, :size, :quantity, :price, NOW())
        ");

        foreach ($cart as $item) {
            $stmt->execute([
                ':cart_id' => uniqid('', true),
                ':customer_id' => $_SESSION['id'], // Ensure customer ID is valid
                ':customer_name' => $_SESSION['customer_name'], // Ensure customer name is valid
                ':item_id' => $item['id'],
                ':item_name' => $item['name'],
                ':size' => $item['size'],
                ':quantity' => $item['quantity'] ?? 1,
                ':price' => $item['price'],
            ]);
        }

        // Return success response after saving the cart
        echo json_encode(['status' => 'success', 'message' => 'Cart saved successfully!']);
    } catch (PDOException $e) {
        // Return JSON error response if saving the cart fails
        echo json_encode(['status' => 'error', 'message' => 'Error saving cart: ' . $e->getMessage()]);
    }
    exit();
}
?>
