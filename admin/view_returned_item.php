<?php
session_start();

// Redirect to home page if the user is not logged in
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}

// Include database connection
include '../db.php';

// Get the total number of returned items (using COUNT if no 'quantity' column)
$totalreturnitemsStmt = $conn->prepare("SELECT COUNT(*) AS total_return FROM return_item");
$totalreturnitemsStmt->execute();
$totalreturnitems = $totalreturnitemsStmt->fetchColumn() ?: 0; // Get total count or 0 if none

// Fetch return item details along with year_level from inventory (using item_code for join)
$stmt = $conn->prepare("
    SELECT 
        ri.customer_name, 
        ri.item_name, 
        i.size,
        i.year_level, 
        ri.return_date 
    FROM return_item ri
    JOIN inventory i ON ri.item_code = i.item_code
");
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo web normi.png">
    <title>Business Center Return Items</title>
    <link rel="stylesheet" href="css/totalstocks.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include_once('navbar.php'); ?>
    <?php include_once('sidebar.php'); ?>

    <div class="container">
        <div class="toplabel">
            <h1>Return Item Overview</h1>
            <p>Return | Returned Items</p>
        </div>
        <hr>

        <div class="displayallitems">
            <div class="total-stock-box">
                <h3>
                    Total Returned Items
                    <p><i class="fa-solid fa-box-open"></i>
                        <span id="total-stock"><?php echo number_format($totalreturnitems, 0); ?></span>
                    </p>
                </h3>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Item Name</th>
                        <th>Size</th>
                        <th>Year Level</th>
                        <th>Return Date</th>
                    </tr>
                </thead>
                <tbody id="product-table-body">
                    <?php
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['size']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['year_level']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['return_date']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
