
<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}

include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    $name = $_POST['name'] ?? '';
    $size = $_POST['size'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 0);
    $year_level = $_POST['year_level'] ?? '';

   
    if ($quantity <= 0) {
        header("Location: add_stock.php?message=Error: Quantity must be greater than 0");
        exit();
    }

    try {
     
        $stmt = $conn->prepare("SELECT * FROM inventory WHERE name = ? AND size = ?");
        $stmt->execute([$name, $size]);
        $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_item) {
         
            $new_quantity = $existing_item['quantity'] + $quantity;

            $update_stmt = $conn->prepare("UPDATE inventory SET quantity = ? WHERE id = ?");
            if ($update_stmt->execute([$new_quantity, $existing_item['id']])) {
               
                header("Location: add_stock.php?message=Item Name $name, Size is $size: added successfully");
                exit();
            } else {
                // Handle update error
                header("Location: add_stock.php?message= Failed to update stock");
                exit();
            }
        } else {
          
            header("Location: add_stock.php?message= Item $name, size is $size or else does not exist");
            exit();
        }
    } catch (Exception $e) {
   
        header("Location: add_stock.php?message=Error: " . $e->getMessage());
        exit();
    }
}
?>