<?php
session_start();

// Check if the user is logged in and is a customer
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in first.']);
    header("Location: ../home.php");
    exit();
}

include '../db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all orders placed by the customer
    $stmt = $pdo->prepare("SELECT * FROM customer_orders WHERE customer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch order items for each order
    $orderDetails = [];
    foreach ($orders as $order) {
        $orderId = $order['id'];
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $orderDetails[$orderId] = $orderItems;
    }

} catch (PDOException $e) {
    die("Error fetching purchase history: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History</title>
    <link rel="stylesheet" href="css/purchase_history.css">
    
</head>
<body>

<?php include_once('navbar.php'); ?>
<?php include_once('sidebar.php'); ?>

<div class="container">
    <h1>Your Purchase History</h1>

    <?php if (empty($orders)): ?>
        <p class="empty-history-message">You have no purchase history yet.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-details">
                <div class="order-header">
                    <p>Order ID: <?php echo htmlspecialchars($order['id']); ?> | 
                       Date: <?php echo htmlspecialchars($order['created_at']); ?> | 
                       Total Amount: ₱<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?> | 
                       Status: 
                       <?php if ($order['status'] === 'paid'): ?>
                           <span class="status-paid">Paid</span>
                       <?php else: ?>
                           <span class="status-unpaid">Unpaid</span>
                       <?php endif; ?>
                    </p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Size</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total Price</th>
                            <th>Received Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderDetails[$order['id']] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['size']); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td>₱<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                                <td>₱<?php echo htmlspecialchars(number_format($item['quantity'] * $item['price'], 2)); ?></td>
                                <td>
                                    <?php if ($item['received_status'] === 'received'): ?>
                                        <span class="status-received">Received</span>
                                    <?php else: ?>
                                        <span class="status-not-received">Not Received</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
