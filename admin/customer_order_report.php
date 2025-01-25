<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'admin') {
    echo "Unauthorized access. Please log in first.";
    exit();
}

include '../db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all orders
    $stmt = $pdo->prepare("SELECT * FROM customer_orders ORDER BY created_at DESC");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders Report</title>
    <link rel="stylesheet" href="css/totalstocks.css">

</head>
<body>

<?php include_once('navbar.php'); ?>
<?php include_once('sidebar.php'); ?>


<div class="container">
<h1>Customer Orders Report</h1>

<?php if (empty($orders)): ?>
    <p>No orders available.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Customer ID</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Date Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_id']); ?></td>
                    <td>₱<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($order['status'])); ?></td>
                    <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>

</body>
</html>
