<?php
session_start();


if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in first.']);
    header("Location: ../home.php");
    exit();
}

include '../db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $itemsPerPage = 10;

   
    $currentPageReceived = isset($_GET['page_received']) ? (int)$_GET['page_received'] : 1;
    $currentPageNotReceived = isset($_GET['page_not_received']) ? (int)$_GET['page_not_received'] : 1;

    $offsetReceived = ($currentPageReceived - 1) * $itemsPerPage;
    $offsetNotReceived = ($currentPageNotReceived - 1) * $itemsPerPage;


    $totalReceivedStmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE received_status = 'received'");
    $totalReceivedStmt->execute();
    $totalReceivedItems = $totalReceivedStmt->fetchColumn();
    $totalReceivedPages = ceil($totalReceivedItems / $itemsPerPage);

    $totalNotReceivedStmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE received_status = 'not_received'");
    $totalNotReceivedStmt->execute();
    $totalNotReceivedItems = $totalNotReceivedStmt->fetchColumn();
    $totalNotReceivedPages = ceil($totalNotReceivedItems / $itemsPerPage);

  
    $receivedStmt = $pdo->prepare("
        SELECT co.customer_name, co.total_amount, oi.item_name, oi.quantity, oi.received_status
        FROM customer_orders co
        JOIN order_items oi ON co.id = oi.order_id
        WHERE oi.received_status = 'received'
        LIMIT :offset, :itemsPerPage
    ");
    $receivedStmt->bindParam(':offset', $offsetReceived, PDO::PARAM_INT);
    $receivedStmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $receivedStmt->execute();
    $receivedItems = $receivedStmt->fetchAll(PDO::FETCH_ASSOC);

 
    $notReceivedStmt = $pdo->prepare("
        SELECT co.customer_name, co.total_amount, oi.item_name, oi.quantity, oi.received_status
        FROM customer_orders co
        JOIN order_items oi ON co.id = oi.order_id
        WHERE oi.received_status = 'not_received'
        LIMIT :offset, :itemsPerPage
    ");
    $notReceivedStmt->bindParam(':offset', $offsetNotReceived, PDO::PARAM_INT);
    $notReceivedStmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $notReceivedStmt->execute();
    $notReceivedItems = $notReceivedStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching items: " . $e->getMessage());
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items Report</title>
    <link rel="stylesheet" href="css/totalstocks.css">

            <style>
                .pagination {
                    margin-top: 20px;
                    text-align: center;
                }

                .pagination a {
                    margin: 0 5px;
                    padding: 8px 12px;
                    border: 1px solid #007BFF;
                    color: #007BFF;
                    text-decoration: none;
                    border-radius: 5px;
                }

                .pagination a.active {
                    background-color: #007BFF;
                    color: white;
                }

                .pagination a:hover {
                    background-color: #0056b3;
                    color: white;
                }
            </style>
</head>
<body>

<?php include_once('navbar.php'); ?>
<?php include_once('sidebar.php'); ?>

<div class="container">
    <div class="toplabel">
        <h1>Items Report</h1>
    </div>

    <hr>

    <div id="report">
   
        <h2>Received Items</h2>
        <?php if (empty($receivedItems)): ?>
            <p style="text-align: center; color: #555;">No received items available.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Amount Paid</th>
                        <th>Item Name</th>
                        <th>Quantity Purchased</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receivedItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['customer_name']); ?></td>
                            <td>₱<?php echo htmlspecialchars(number_format($item['total_amount'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

          
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalReceivedPages; $i++): ?>
                    <a href="?page_received=<?php echo $i; ?>" class="<?php echo ($i === $currentPageReceived) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

   

   
        <h2>Not Received Items</h2>
        <?php if (empty($notReceivedItems)): ?>
            <p style="text-align: center; color: #555;">No not received items available.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Amount Paid</th>
                        <th>Item Name</th>
                        <th>Quantity Purchased</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notReceivedItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['customer_name']); ?></td>
                            <td>₱<?php echo htmlspecialchars(number_format($item['total_amount'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

 
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalNotReceivedPages; $i++): ?>
                    <a href="?page_not_received=<?php echo $i; ?>" class="<?php echo ($i === $currentPageNotReceived) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
