<?php
session_start();
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}

include '../db.php'; // Database connection

// Initialize variables for search and filtering
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$orderBy = isset($_GET['order_by']) && in_array($_GET['order_by'], ['name', 'price', 'quantity', 'purchase_date']) ? $_GET['order_by'] : 'purchase_date';
$orderDirection = isset($_GET['order_direction']) && in_array(strtoupper($_GET['order_direction']), ['ASC', 'DESC']) ? strtoupper($_GET['order_direction']) : 'ASC';
$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : '';
$startDate = '';
$endDate = '';

// Prepare SQL query with search and order functionality
$query = "
    SELECT t.id AS transaction_id, t.purchase_date, i.name AS item_name, t.quantity, t.total_price, 
           t.customer_name, t.description, i.item_code, i.size
    FROM transactions t
    JOIN inventory i ON t.item_id = i.id
    WHERE (i.name LIKE :searchTerm OR t.customer_name LIKE :searchTerm)
";

// Handle date range filtering if specified
if (!empty($dateRange)) {
    $dates = explode(' to ', $dateRange);
    if (count($dates) === 2) {
        $startDate = trim($dates[0]);
        $endDate = trim($dates[1]);
        $query .= " AND DATE(t.purchase_date) BETWEEN :startDate AND :endDate";
    }
}

$query .= " ORDER BY $orderBy $orderDirection";

// Prepare and execute the query
$transactions_stmt = $conn->prepare($query);
$transactions_stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
if (!empty($dateRange) && !empty($startDate) && !empty($endDate)) {
    $transactions_stmt->bindValue(':startDate', $startDate);
    $transactions_stmt->bindValue(':endDate', $endDate);
}
$transactions_stmt->execute();
$transactions = $transactions_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link rel="stylesheet" href="css/samle.css"> <!-- Fixed typo in CSS file name -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0"></script>
</head>
<body>

    <div class="container">
        <h1>Transaction History</h1>

        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search items..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <select name="order_by">
                    <option value="name" <?php if ($orderBy === 'name') echo 'selected'; ?>>Name</option>
                    <option value="price" <?php if ($orderBy === 'price') echo 'selected'; ?>>Price</option>
                    <option value="quantity" <?php if ($orderBy === 'quantity') echo 'selected'; ?>>Quantity</option>
                    <option value="purchase_date" <?php if ($orderBy === 'purchase_date') echo 'selected'; ?>>Time</option>
                </select>
                <select name="order_direction">
                    <option value="ASC" <?php if ($orderDirection === 'ASC') echo 'selected'; ?>>Ascending</option>
                    <option value="DESC" <?php if ($orderDirection === 'DESC') echo 'selected'; ?>>Descending</option>
                </select>
                
                <!-- Date Range Selection -->
                <input  type="text" id="date_range" name="date_range" placeholder="Select Date Range" value="<?php echo htmlspecialchars($dateRange); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                flatpickr("#date_range", {
                    mode: "range",
                    dateFormat: "Y-m-d",
                    onOpen: function (selectedDates, dateStr, instance) {
                        instance.calendarContainer.style.backgroundColor = "#f0f8ff"; // Set background color
                    }
                });

                // Sorting functionality using SortableJS
                const table = document.querySelector("table");
                new Sortable(table.querySelector("tbody"), {
                    animation: 150,
                    ghostClass: "sortable-ghost",
                    onEnd: function (evt) {
                        // Optional: Update sort order in URL or AJAX request if needed
                    }
                });
            });
        </script>

        <!-- Transactions Table -->
        <table border="1">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Customer Name</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Size</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Purchase Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['item_code']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['size']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['quantity']); ?></td>
                            <td>₱<?php echo number_format($transaction['total_price'], 2); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d / h:i A', strtotime($transaction['purchase_date']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
