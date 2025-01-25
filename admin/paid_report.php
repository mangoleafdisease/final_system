<?php
session_start();


if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in first.']);
    header("Location: ../home.php");
    exit();
}

include '../db.php';


$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';


$recordsPerPage = 10; // Items per page
$paidPage = isset($_GET['paid_page']) ? (int)$_GET['paid_page'] : 1;
$unpaidPage = isset($_GET['unpaid_page']) ? (int)$_GET['unpaid_page'] : 1;


$paidOffset = ($paidPage - 1) * $recordsPerPage;
$unpaidOffset = ($unpaidPage - 1) * $recordsPerPage;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $paidCountQuery = "SELECT COUNT(*) FROM customer_orders WHERE status = 'paid'";
    $unpaidCountQuery = "SELECT COUNT(*) FROM customer_orders WHERE status = 'unpaid'";
    $params = [];

    if (!empty($startDate) && !empty($endDate)) {
        $paidCountQuery .= " AND DATE(created_at) BETWEEN ? AND ?";
        $unpaidCountQuery .= " AND DATE(created_at) BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
    }

    $paidCountStmt = $pdo->prepare($paidCountQuery);
    $unpaidCountStmt = $pdo->prepare($unpaidCountQuery);

    $paidCountStmt->execute($params);
    $unpaidCountStmt->execute($params);

    $totalPaidOrders = $paidCountStmt->fetchColumn();
    $totalUnpaidOrders = $unpaidCountStmt->fetchColumn();

 
    $totalPaidPages = ceil($totalPaidOrders / $recordsPerPage);
    $totalUnpaidPages = ceil($totalUnpaidOrders / $recordsPerPage);

  
    $paidQuery = "SELECT * FROM customer_orders WHERE status = 'paid'";
    $unpaidQuery = "SELECT * FROM customer_orders WHERE status = 'unpaid'";

    if (!empty($startDate) && !empty($endDate)) {
        $paidQuery .= " AND DATE(created_at) BETWEEN ? AND ?";
        $unpaidQuery .= " AND DATE(created_at) BETWEEN ? AND ?";
    }

    $paidQuery .= " ORDER BY created_at DESC LIMIT $recordsPerPage OFFSET $paidOffset";
    $unpaidQuery .= " ORDER BY created_at DESC LIMIT $recordsPerPage OFFSET $unpaidOffset";

    $paidStmt = $pdo->prepare($paidQuery);
    $unpaidStmt = $pdo->prepare($unpaidQuery);

    $paidStmt->execute($params);
    $unpaidStmt->execute($params);

    $paidOrders = $paidStmt->fetchAll(PDO::FETCH_ASSOC);
    $unpaidOrders = $unpaidStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Report</title>
    <link rel="stylesheet" href="css/totalstocks.css">

    <style>
            .pagination {
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 20px 0;
                gap: 5px;
            }

            .pagination a {
                display: inline-block;
                padding: 8px 12px;
                font-size: 14px;
                color: #007bff;
                text-decoration: none;
                border: 1px solid #ddd;
                border-radius: 4px;
                transition: background-color 0.3s, color 0.3s;
            }

            .pagination a:hover {
                background-color: #007bff;
                color: #fff;
                border-color: #007bff;
            }

            .pagination a.active {
                background-color: #007bff;
                color: #fff;
                border-color: #007bff;
                font-weight: bold;
            }

    </style>
</head>
<body>

<?php include_once('navbar.php'); ?>
<?php include_once('sidebar.php'); ?>

<div class="container">
    <div class="toplabel">
        <h1>Orders Report</h1>
        <p>| Generate and view orders</p>
    </div>

    <hr>

   
    <form method="GET" action="" class="filter-form">
        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($startDate); ?>">

        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($endDate); ?>">

        <button type="submit" class="btn btn-filter">Filter</button>
        <a href="generate_pdf.php?start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" class="btn btn-download">Download PDF</a>
    </form>

    <hr>


    <h2>Paid Orders</h2>
    <?php if (empty($paidOrders)): ?>
        <p style="text-align: center; color: #555;">No paid orders found for the selected date range.</p>
    <?php else: ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Total Amount</th>
                    <th>Payment Status</th>
                    <th>Date Paid</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paidOrders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td>₱<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td><?php echo htmlspecialchars(date('F j, Y', strtotime($order['created_at']))); ?></td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPaidPages; $i++): ?>
                <a href="?start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>&paid_page=<?php echo $i; ?>&unpaid_page=<?php echo $unpaidPage; ?>" 
                   class="<?php echo $i == $paidPage ? 'active' : ''; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <hr>


    <h2>Unpaid Orders</h2>
    <?php if (empty($unpaidOrders)): ?>
        <p style="text-align: center; color: #555;">No unpaid orders found for the selected date range.</p>
    <?php else: ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Total Amount</th>
                    <th>Payment Status</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unpaidOrders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td>₱<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td><?php echo htmlspecialchars(date('F j, Y', strtotime($order['created_at']))); ?></td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalUnpaidPages; $i++): ?>
                <a href="?start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>&paid_page=<?php echo $paidPage; ?>&unpaid_page=<?php echo $i; ?>" 
                   class="<?php echo $i == $unpaidPage ? 'active' : ''; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
