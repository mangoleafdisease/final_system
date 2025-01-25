<?php
session_start();
include '../db.php'; // Include your database connection file

try {
    // Establish database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Input sanitization
        $barcode = htmlspecialchars(trim($_POST['barcode']));
        $quantity_sold = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
        $customer_name = htmlspecialchars(trim($_POST['customer_name']));
        $description = htmlspecialchars(trim($_POST['description']));

        // Validate inputs
        if (empty($barcode) || !$quantity_sold || empty($customer_name)) {
            $_SESSION['purchase_error'] = 'Invalid input data!';
            header("Location: purchase.php");
            exit();
        }

        // Fetch item by barcode
        $stmt = $pdo->prepare("SELECT * FROM inventory WHERE barcode = :barcode");
        $stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            $item_id = $item['id'];
            $item_name = htmlspecialchars($item['name']);
            $item_quantity = intval($item['quantity']);
            $item_price = floatval($item['price']);

            // Check if there is enough quantity in stock
            if ($item_quantity >= $quantity_sold) {
                $total_price = $quantity_sold * $item_price;

                // Update the inventory after sale
                $new_quantity = $item_quantity - $quantity_sold;
                $update_stmt = $pdo->prepare("UPDATE inventory SET quantity = :new_quantity WHERE id = :item_id");
                $update_stmt->bindParam(':new_quantity', $new_quantity, PDO::PARAM_INT);
                $update_stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);

                if ($update_stmt->execute()) {
                    // Record the sale in the sales table
                    $sales_stmt = $pdo->prepare("INSERT INTO sales (product_id, quantity_sold) VALUES (:item_id, :quantity_sold)");
                    $sales_stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
                    $sales_stmt->bindParam(':quantity_sold', $quantity_sold, PDO::PARAM_INT);

                    if ($sales_stmt->execute()) {
                        // Record the transaction
                        $transactions_stmt = $pdo->prepare(
                            "INSERT INTO transactions (item_id, quantity, total_price, purchase_date, customer_name, description) 
                            VALUES (:item_id, :quantity, :total_price, NOW(), :customer_name, :description)"
                        );
                        $transactions_stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
                        $transactions_stmt->bindParam(':quantity', $quantity_sold, PDO::PARAM_INT);
                        $transactions_stmt->bindParam(':total_price', $total_price, PDO::PARAM_STR);
                        $transactions_stmt->bindParam(':customer_name', $customer_name, PDO::PARAM_STR);
                        $transactions_stmt->bindParam(':description', $description, PDO::PARAM_STR);

                        if ($transactions_stmt->execute()) {
                            // Set a success message
                            $_SESSION['purchase_success'] = "Purchase completed: $item_name for ₱" . number_format($total_price, 2);
                        } else {
                            $_SESSION['purchase_error'] = 'Error recording the transaction!';
                        }
                    } else {
                        $_SESSION['purchase_error'] = 'Error recording the sale!';
                    }
                } else {
                    $_SESSION['purchase_error'] = 'Error updating inventory!';
                }
            } else {
                $_SESSION['purchase_error'] = 'Error: Insufficient stock!';
            }
        } else {
            $_SESSION['purchase_error'] = 'Error: Item not found!';
        }
    } catch (PDOException $e) {
        $_SESSION['purchase_error'] = 'Database error: ' . $e->getMessage();
    }

    header("Location: purchase.php");
    exit();
}
?>
