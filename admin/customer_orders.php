<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in first.']);
    header("Location: ../home.php");
    exit();
}

include '../db.php';

$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch customer orders filtered by name if a search keyword is provided
    if ($searchKeyword !== '') {
        $stmt = $pdo->prepare("SELECT * FROM customer_orders WHERE customer_name LIKE ? ORDER BY created_at DESC");
        $stmt->execute(["%$searchKeyword%"]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM customer_orders ORDER BY created_at DESC");
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

    // Handle updating received status
    if (isset($_POST['update_item_status'])) {
        $itemId = $_POST['item_id'];
        $receivedStatus = $_POST['received_status'];

        // Fetch the item details (quantity and item name)
        $itemQuery = $pdo->prepare("SELECT item_name, quantity FROM order_items WHERE id = ?");
        $itemQuery->execute([$itemId]);
        $itemDetails = $itemQuery->fetch(PDO::FETCH_ASSOC);

        if ($itemDetails && $receivedStatus === 'received') {
            $itemName = $itemDetails['item_name'];
            $itemQuantity = $itemDetails['quantity'];

            // Begin transaction
            $pdo->beginTransaction();

            try {
                // Update the received status of the item
                $updateStmt = $pdo->prepare("UPDATE order_items SET received_status = ? WHERE id = ?");
                $updateStmt->execute([$receivedStatus, $itemId]);

                // Decrease the quantity in the inventory
                $updateInventoryStmt = $pdo->prepare("UPDATE inventory SET quantity = quantity - ? WHERE name = ?");
                $updateInventoryStmt->execute([$itemQuantity, $itemName]);

                // Commit the transaction
                $pdo->commit();
            } catch (Exception $e) {
                // Roll back the transaction if something fails
                $pdo->rollBack();
                die("Error updating inventory: " . $e->getMessage());
            }
        }

        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
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
        <p>| orders</p>
    </div>
    
    <hr>
    <!-- Search Bar -->
    <div class="search-bar">
        <form method="GET" action="" class="search-bar-box">
            <input type="text" name="search" class="search-input" placeholder="Search by customer name..." value="<?php echo htmlspecialchars($searchKeyword); ?>">
            <button type="submit" class="search-button">Search</button>
        </form>
    </div>

    <?php if (empty($orders)): ?>
        <p style="text-align: center; color: #555;">No customer orders available.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <h3>Order ID: <?php echo htmlspecialchars($order['id']); ?>
                        <span class="status-circle <?php echo $order['status']; ?>"></span>
                    </h3>
                    <p>
                        <strong class="readyto">
                            <?php 
                                if ($order['status'] === 'paid') {
                                    echo 'Ready to Release';
                                } elseif ($order['status'] === 'released') {
                                    echo 'Released';
                                }
                            ?>
                        </strong>
                    </p>
                </div>

                <div class="order-details">
                    <p><strong>Customer ID:</strong> <?php echo htmlspecialchars($order['customer_id']); ?></p>
                    <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>Total Amount:</strong> ₱<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></p>
                    <p><strong>Status:</strong>
                        <?php 
                            echo ucfirst($order['status']) === 'paid' ? 'Ready to Release' : ucfirst($order['status']);
                        ?>
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
                                <th>Action</th>
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

                                            <?php if ($item['received_status'] === 'received'): ?>
                                                <span class="received-text">Received</span>
                                            <?php else: ?>
                                                <select name="received_status" class="select">
                                                    <option value="received" <?php echo $item['received_status'] === 'received' ? 'selected' : ''; ?>>Received</option>
                                                    <option value="not_received" <?php echo $item['received_status'] === 'not_received' ? 'selected' : ''; ?>>Not Received</option>
                                                </select>
                                                <button type="submit" name="update_item_status" class="btn btn-update">Update</button>
                                            <?php endif; ?>
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

<script>
   
    document.addEventListener("DOMContentLoaded", function () {
        const updateButtons = document.querySelectorAll(".btn-update");

        updateButtons.forEach(button => {
            button.addEventListener("click", function (event) {
                const form = this.closest("form"); 
                const selectedStatus = form.querySelector("select[name='received_status']").value;

              
                const confirmUpdate = confirm("Are you sure you want to update the status to '" + selectedStatus + "'?");
                if (!confirmUpdate) {
                    event.preventDefault(); 
                }
            });
        });
    });
</script>

</body>
</html>
