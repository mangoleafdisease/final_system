<?php
session_start();

// Check if the user is logged in and is a cashier
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

    // Fetch only paid customer orders
    $query = "SELECT * FROM customer_orders WHERE status = 'paid'";
    if ($searchKeyword !== '') {
        $query .= " AND customer_name LIKE ?";
    }
    $query .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($query);
    if ($searchKeyword !== '') {
        $stmt->execute(["%$searchKeyword%"]);
    } else {
        $stmt->execute();
    }
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
    die("Error fetching orders: " . $e->getMessage());
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
            <p>| paid orders</p>
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
        <p style="text-align: center; color: #555;">No paid customer orders available.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                    <div class="order-header">
                        <h3>Order ID: <?php echo htmlspecialchars($order['id']); ?><span class="status-circle <?php echo $order['status']; ?>"></span></h3>
                        <form method="POST" class="form-inline">
                            <button 
                                type="submit" 
                                name="update_order_status" 
                                class="btn btn-success" 
                                <?php echo $order['status'] === 'paid' ? 'style="display:none;"' : ''; ?>
                            >
                                Update
                            </button>

                        </form>
                    </div>

                    <div class="order-details">
                        <p><strong>Customer ID:</strong> <?php echo htmlspecialchars($order['customer_id']); ?></p>
                        <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                        <p><strong>Total Amount:</strong> ₱<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></p>
                        <p><strong>Status:</strong>
                            <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                        </p>
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
                                    <td>
                                        <form method="POST" class="form-inline">
                                            <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                            
                                            
                                        </form>
                                    </td>
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
