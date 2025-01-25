<?php
session_start();

// Redirect to home page if the user is not logged in
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}

// Include database connection
include '../db.php';

// Check if the database connection is established
if (!$conn) {
    die("Database connection failed: " . $conn->errorInfo());
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form values
    $customer_name = $_POST['customer_name'];
    $item_code = $_POST['item_code'];
    $item_name = $_POST['name'];
    $size = $_POST['size'];
    $quantity = $_POST['quantity'];
    $year_level = $_POST['year_level'];
    $return_date = date('Y-m-d'); // Current date as return date

    // Ensure quantity is greater than 0
    if ($quantity <= 0) {
        header("Location: return_item.php?message=Error: Quantity must be greater than 0");
        exit();
    }

    // ... (your existing code)

    try {
        // Step 1: Insert return item into the return_item table
        $sql = "INSERT INTO return_item (customer_name, item_name, return_date, item_code)
                VALUES (:customer_name, :item_name, :return_date, :item_code)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':customer_name', $customer_name, PDO::PARAM_STR);
        $stmt->bindValue(':item_name', $item_name, PDO::PARAM_STR);
        $stmt->bindValue(':return_date', $return_date, PDO::PARAM_STR);
        $stmt->bindValue(':item_code', $item_code, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Proceed to update the inventory if the return item was added
            $stmt = $conn->prepare("SELECT * FROM inventory WHERE name = :item_name AND size = :size");
            $stmt->bindValue(':item_name', $item_name, PDO::PARAM_STR);
            $stmt->bindValue(':size', $size, PDO::PARAM_STR);
            $stmt->execute();
            
            $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_item) {
                $new_quantity = $existing_item['quantity'] + $quantity;
                $update_stmt = $conn->prepare("UPDATE inventory SET quantity = :quantity WHERE id = :id");
                $update_stmt->bindValue(':quantity', $new_quantity, PDO::PARAM_INT);
                $update_stmt->bindValue(':id', $existing_item['id'], PDO::PARAM_INT);

                if ($update_stmt->execute()) {
                    $_SESSION['return_success'] = "Item returned successfully: $item_name.";
                    header("Location: return_item.php");
                    exit();
                }
            } else {
                $insert_stmt = $conn->prepare("INSERT INTO inventory (name, size, quantity, year_level, item_code) VALUES (:name, :size, :quantity, :year_level, :item_code)");
                $insert_stmt->bindValue(':name', $item_name, PDO::PARAM_STR);
                $insert_stmt->bindValue(':size', $size, PDO::PARAM_STR);
                $insert_stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
                $insert_stmt->bindValue(':year_level', $year_level, PDO::PARAM_STR);
                $insert_stmt->bindValue(':item_code', $item_code, PDO::PARAM_STR);

                if ($insert_stmt->execute()) {
                    $_SESSION['return_success'] = "Item returned and added to inventory: $item_name.";
                    header("Location: return_item.php");
                    exit();
                }
            }
        } else {
            header("Location: return_item.php?message=Error: Failed to process return item.");
            exit();
        }
    } catch (Exception $e) {
        header("Location: return_item.php?message=Error: " . $e->getMessage());
        exit();
    }

    // ...

} else {
    // Redirect if the request method is not POST
    header("Location: return_item.php");
    exit();
}
?>
