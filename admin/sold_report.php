<?php
session_start();


if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}

include '../db.php'; 

    try {
    $itemsPerPage = 15; 
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($currentPage - 1) * $itemsPerPage;

  
    $totalStmt = $conn->prepare("SELECT COUNT(DISTINCT t.item_id) AS total FROM transactions t");
    $totalStmt->execute();
    $totalItems = $totalStmt->fetchColumn();
    $totalPages = ceil($totalItems / $itemsPerPage);

  
    $stmt = $conn->prepare("
    SELECT 
        t.item_id, 
        i.name AS item_name, 
        i.size AS item_size,  -- Add the size column here
        SUM(t.quantity) AS total_sold, 
        SUM(t.total_price) AS total_sales
    FROM transactions t
    JOIN inventory i ON t.item_id = i.id
    GROUP BY t.item_id, i.name, i.size
    ORDER BY total_sales DESC
    LIMIT :offset, :itemsPerPage
");


    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $soldItems = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $totalSummaryStmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT t.item_id) AS total_items,
            SUM(t.quantity) AS total_quantity,
            SUM(t.total_price) AS total_sales
        FROM transactions t
    ");
    $totalSummaryStmt->execute();
    $totalSummary = $totalSummaryStmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Error fetching sold items: " . $e->getMessage());
    }

    
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items Sold Report</title>
    <link rel="stylesheet" href="css/totalstocks.css">

    <style>
        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            border: 1px solidrgb(0, 255, 64);
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
        .print-button {
            margin: 20px 0;
            text-align: right;
        }

        .print-button button {
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            background-color:rgb(1, 75, 1);
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .print-button button:hover {
            background-color:rgb(5, 78, 2);
        }

        @media print {
            body * {
                visibility: hidden; 
            }

            table, table * {
                visibility: visible; 
            }

            table {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%; 
            }

            .print-button, .pagination, nav, aside {
                display: none; 
            }
        }
    </style>
        </head>
        <body>
            <?php include_once('navbar.php'); ?>
            <?php include_once('sidebar.php'); ?>

            <div class="container">
            <div class="toplabel">
                <h1>Item Sold Report</h1>
                <p>| Generate View Report</p>
            </div>

                <hr>

                <table>
                    <thead>
                        
                <div class="print-button">
                <button onclick="window.print()">Print Report</button>
                </div>
                        <tr>
                            <th>Item Name</th>
                            <th>Item Size</th>
                            <th>Total Quantity Sold</th>
                
                            <th>Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($soldItems)): ?>
                            <?php foreach ($soldItems as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['item_size']); ?></td>
                                    <td><?php echo htmlspecialchars($item['total_sold']); ?></td>
                                    
                                    <td>₱<?php echo number_format($item['total_sales'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No sales data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                <tr>
                    <th>Total</th>
                    <th></th>
                    <th><?php echo htmlspecialchars($totalSummary['total_quantity']); ?></th>
                    <th>₱<?php echo number_format($totalSummary['total_sales'], 2); ?></th>
                </tr>
            </tfoot>
        </table>
                
                <div class="pagination">
                    <?php if ($totalPages > 1): ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="<?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            </div>
        </body>
        </html>
