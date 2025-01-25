<?php
session_start();

// Ensure the user is logged in and has the cashier role
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'cashier') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in first.']);
    header("Location: ../home.php");
    exit();
}

include '../db.php';

$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle order status update
    if (isset($_POST['update_order_status'])) {
        $orderId = $_POST['order_id'];
        $newStatus = $_POST['status'];

        // Update the order status
        $stmt = $pdo->prepare("UPDATE customer_orders SET status = :status WHERE id = :order_id");
        $stmt->execute([':status' => $newStatus, ':order_id' => $orderId]);

        header("Location: unpaid_orders.php"); // Avoid resubmission
        exit();
    }

    // Fetch unpaid orders created today, optionally filtered by name
    if ($searchKeyword !== '') {
        $stmt = $pdo->prepare("SELECT * FROM customer_orders WHERE customer_name LIKE ? AND status = 'unpaid' AND DATE(created_at) = CURDATE() ORDER BY created_at DESC");
        $stmt->execute(["%$searchKeyword%"]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM customer_orders WHERE status = 'unpaid' AND DATE(created_at) = CURDATE() ORDER BY created_at DESC");
        $stmt->execute();
    }

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch order items for each order
    $orderDetails = [];
    foreach ($orders as $order) {
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $orderDetails[$order['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Center</title>
    <link rel="stylesheet" href="css/customer_orders_list.css">
</head>
<body>

<?php include_once('navbar.php'); ?>
<?php include_once('sidebar.php'); ?>

<div class="container">
    <div class="toplabel">
        <h1>Customer Order</h1>
        <p>| Unpaid Orders</p>
    </div>
    <hr>

    <!-- Search Bar -->
    <div class="search-bar">
        <form method="GET" action="" class="search-bar-box">
            <input type="text" name="search" class="search-input" placeholder="Search Customer Name..." value="<?php echo htmlspecialchars($searchKeyword); ?>">
            <button type="submit" class="search-button">Search</button>
        </form>
    </div>

    <?php if (empty($orders)): ?>
        <p style="text-align: center; color: #555;">No unpaid customer orders available today.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <h3>Order ID: <?php echo htmlspecialchars($order['id']); ?><span class="status-circle <?php echo htmlspecialchars($order['status']); ?>"></span></h3>
                    <form method="POST" class="form-inline">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                        <select name="status" class="select" <?php echo $order['status'] === 'paid' ? 'disabled' : ''; ?>>
                            <option value="paid" <?php echo $order['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="unpaid" <?php echo $order['status'] === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                        </select>
                        <button 
                            type="submit" 
                            name="update_order_status" 
                            class="btn btn-success" 
                            <?php echo $order['status'] === 'paid' ? 'disabled' : ''; ?>
                        >
                            Update
                        </button>
                    </form>
                </div>

                <div class="order-details">
                    <p><strong>Customer ID:</strong> <?php echo htmlspecialchars($order['customer_id']); ?></p>
                    <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>Total Amount:</strong> ₱<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($order['status'])); ?></p>
                </div>

                <div class="table-container">
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
                                    <td><?php echo htmlspecialchars($item['received_status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
