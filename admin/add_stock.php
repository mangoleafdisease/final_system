<?php
session_start();

// Redirect to home page if the user is not logged in
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}

// Include database connection
include '../db.php';

// Get the item ID from the URL
$itemId = isset($_GET['id']) ? $_GET['id'] : null;

// Fetch the item details from the database
if ($itemId) {
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE id = :id");
    $stmt->bindParam(':id', $itemId);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the item does not exist
    if (!$item) {
        header("Location: add_stock.php?message=Error: Item not found in inventory");
        exit();
    }
} else {
    $item = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo web normi.png">
    <title>Business Center Add Stock</title>
    <link rel="stylesheet" href="css/add_stock.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <script>
       
        function showAlert(message) {
            alert(message);
        }

        
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const message = urlParams.get('message');
            if (message) {
                showAlert(message);
            }
        };

      
        function confirmSubmit(event) {
            const confirmation = confirm("Are you sure you want to submit the form?");
            if (!confirmation) {
                event.preventDefault(); 
            }
        }
    </script>
</head>
<body>
    <?php include_once('navbar.php') ?>
    <?php include_once('sidebar.php') ?>

    <div class="container">
        <div class="toplabel">
            <h1>Adding Stocks</h1>
            <p>Item List | Add stock</p>
        </div>

        <hr>

        <form id="myForm" action="add_stock_process.php" method="POST" onsubmit="confirmSubmit(event)">
            <div class="form-flex">
                <div class="form-group form-column-1">
                    <label for="name">Item Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>" required>

                    <label for="size">Size</label>
                    <input type="text" id="size" name="size" value="<?php echo htmlspecialchars($item['size'] ?? ''); ?>" required>
                </div>

                <div class="form-group form-column-2">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($item['quantity'] ?? ''); ?>" required>

                    <label for="year_level">Program</label>
                    <input type="text" id="year_level" name="year_level" value="<?php echo htmlspecialchars($item['year_level'] ?? ''); ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">Submit</button>
                    <button type="button" class="cancel-btn" onclick="handleCancel()">Cancel</button>
                </div>

                <script>
                    function handleCancel() {
                        // Reset the form
                        document.getElementById('myForm').reset();
                    }
                </script>
            </div>
        </form>
    </div>
</body>
</html>
