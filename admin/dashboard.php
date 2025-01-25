<?php
session_start();
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}


error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db.php';


$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$orderBy = isset($_GET['order_by']) && in_array($_GET['order_by'], ['name', 'price', 'quantity']) ? $_GET['order_by'] : 'name';
$orderDirection = isset($_GET['order_direction']) && in_array($_GET['order_direction'], ['ASC', 'DESC']) ? $_GET['order_direction'] : 'ASC';
$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : '';






$all_items_stmt = $conn->prepare("
    SELECT id, name, size, price, quantity
    FROM inventory
    WHERE name LIKE :search
    ORDER BY $orderBy $orderDirection
");
$all_items_stmt->execute(['search' => '%' . $searchTerm . '%']);
$all_items = $all_items_stmt->fetchAll();



$totalPurchaseDue = 0;
foreach ($all_items as $item) {
    $totalPurchaseDue += $item['quantity'] > 0 ? $item['price'] * $item['quantity'] : 0; 
}


$totalSalesDue = 0;
$sales_stmt = $conn->prepare("
    SELECT SUM(total_price) AS total_sales
    FROM items_sold
");
$sales_stmt->execute();
$totalSalesDue = $sales_stmt->fetchColumn() ?: 0;



$customers_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT customer_name) AS total_customers 
    FROM transactions
");
$customers_stmt->execute();
$totalCustomers = $customers_stmt->fetchColumn() ?: 0; 


$weeklyEarningsStmt = $conn->prepare("
    SELECT COALESCE(SUM(total_price), 0) AS weekly_earnings
    FROM items_sold
    WHERE sold_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
");
$weeklyEarningsStmt->execute();
$weeklyEarnings = $weeklyEarningsStmt->fetchColumn() ?: 0;

$query = "
    SELECT 
        YEARWEEK(sold_date, 1) AS week_number, 
        SUM(total_price) AS total_amount
    FROM items_sold
    GROUP BY YEARWEEK(sold_date, 1)
    ORDER BY YEARWEEK(sold_date, 1) DESC
";

$stmt = $conn->prepare($query);
$stmt->execute();
$weeklySalesData = $stmt->fetchAll(PDO::FETCH_ASSOC);







// report paid unpaid chart


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $queryPaid = "SELECT SUM(total_amount) AS total_paid FROM customer_orders WHERE status = 'paid'";
    $queryUnpaid = "SELECT SUM(total_amount) AS total_unpaid FROM customer_orders WHERE status = 'unpaid'";

    $stmtPaid = $pdo->prepare($queryPaid);
    $stmtUnpaid = $pdo->prepare($queryUnpaid);

    $stmtPaid->execute();
    $stmtUnpaid->execute();

    $totalPaid = $stmtPaid->fetch(PDO::FETCH_ASSOC)['total_paid'] ?? 0;
    $totalUnpaid = $stmtUnpaid->fetch(PDO::FETCH_ASSOC)['total_unpaid'] ?? 0;

} catch (PDOException $e) {
    die("Error fetching totals: " . $e->getMessage());
}


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $totalReceivedCountStmt = $pdo->prepare("SELECT COUNT(*) AS total_received FROM order_items WHERE received_status = 'received'");
    $totalNotReceivedCountStmt = $pdo->prepare("SELECT COUNT(*) AS total_not_received FROM order_items WHERE received_status = 'not_received'");
    $totalReceivedCountStmt->execute();
    $totalNotReceivedCountStmt->execute();
    $totalReceived = $totalReceivedCountStmt->fetch(PDO::FETCH_ASSOC)['total_received'] ?? 0;
    $totalNotReceived = $totalNotReceivedCountStmt->fetch(PDO::FETCH_ASSOC)['total_not_received'] ?? 0;

    $itemsStmt = $pdo->prepare("
        SELECT item_name, SUM(quantity) AS total_quantity 
        FROM order_items 
        GROUP BY item_name 
        ORDER BY total_quantity DESC
    ");
    $itemsStmt->execute();
    $itemsData = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching items: " . $e->getMessage());
}

try {
    $itemsPerPage = 15; 
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($currentPage - 1) * $itemsPerPage;

    // Fetch total number of unique items
    $totalStmt = $conn->prepare("SELECT COUNT(DISTINCT t.item_id) AS total FROM transactions t");
    $totalStmt->execute();
    $totalItems = $totalStmt->fetchColumn();
    $totalPages = ceil($totalItems / $itemsPerPage);


    $stmt = $conn->prepare("
        SELECT 
            t.item_id, 
            i.name AS item_name, 
            SUM(t.quantity) AS total_sold, 
            SUM(t.total_price) AS total_sales
        FROM transactions t
        JOIN inventory i ON t.item_id = i.id
        GROUP BY t.item_id, i.name
        ORDER BY total_sales DESC
        LIMIT :offset, :itemsPerPage
    ");

    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $soldItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching sold items: " . $e->getMessage());
}


$query = "
SELECT DATE_FORMAT(purchase_date, '%m') AS month, 
       SUM(total_price) AS monthly_total
FROM transactions
WHERE payment_status = 'paid'  -- Only count paid transactions
GROUP BY DATE_FORMAT(purchase_date, '%Y-%m')
ORDER BY DATE_FORMAT(purchase_date, '%Y-%m') ASC";

$stmt = $conn->prepare($query);
$stmt->execute();
$monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the chart
$months = [];
$totals = [];
foreach ($monthlyData as $row) {
$monthNum = $row['month']; // Numeric month
$months[] = date('F', mktime(0, 0, 0, $monthNum, 1)); 
$totals[] = $row['monthly_total']; 
}


$query = "
    SELECT item_name, SUM(quantity) AS total_quantity
    FROM items_sold
    GROUP BY item_name
    ORDER BY total_quantity DESC
";

$stmt = $conn->prepare($query);
$stmt->execute();
$itemsSoldData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the chart
$itemNames = [];
$totalQuantities = [];

foreach ($itemsSoldData as $row) {
    $itemNames[] = $row['item_name'];
    $totalQuantities[] = $row['total_quantity'];
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo web normi.png">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    
            <?php if (isset($_SESSION['login_success'])): ?>
                <script>
                    alert("<?php echo htmlspecialchars($_SESSION['login_success']); ?>");
                </script>
                <?php unset($_SESSION['login_success']); ?>
            <?php endif; ?>
        </head>
        <body>
            <?php include_once('navbar.php') ?>
            <?php include_once('sidebar.php') ?>
            
            <div class="container">


<div class="financial-summary">

    <div class="financial-box">
        <i class="fa-solid fa-bag-shopping"></i>
        <div class="sbox">      
            <p>₱<?php echo number_format($totalPurchaseDue, 2); ?></p>
            <h3>Total Purchase Due</h3>
        </div>
    </div>

    <div class="financial-box">
        <i class="fa-solid fa-money-bill"></i>
        <div class="sbox">      
            <p>₱<?php echo number_format($totalSalesDue, 2); ?></p>
            <h3>Total Sales Due</h3>
        </div>
    </div>  

    <div class="financial-box">
        <i class="fa-solid fa-arrow-up"></i>
        <div class="sbox">
            <p>₱<?php echo number_format($totalSalesDue, 2); ?></p>
            <h3>Total Sale Amount</h3>
        </div>
    </div>

    <div class="financial-box">
        <i class="fa-solid fa-person-military-pointing"></i>
        <div class="sbox">
            <p><?php echo number_format($totalCustomers); ?></p>
            <h3>Total Customers</h3>
        </div>
    </div>

    <div class="financial-box">
        <i class="fa-solid fa-peso-sign"></i>
        <div class="sbox"> 
            <p>₱<?php echo number_format($weeklyEarnings, 2); ?></p>
            <h3>Weekly Earnings</h3>
        </div>
    </div>
</div>


<div class="chartofstock">
    <div class="chrtstock">
        <h2>Stocks Overview</h2>
        <hr>
        <canvas id="stocksChart"></canvas>
        <div class="legend" id="stocksLegend"></div>
    </div>

    <div class="totalorderchrt">
        <h2>Total Amounts</h2>
        <canvas id="ordersChart"></canvas>
    </div>
</div>



<div class="chartofstocks">
    <div class="chrtstocks">
        <h2>Monthly Sales Overview</h2>
        <canvas id="monthlySalesChart"></canvas>
    </div>

    <div class="totalorderchrts">
        <h2>Item Quantities</h2>
        <canvas id="receivedChart"></canvas>
    </div>

    <div class="chrtstocks">
        <h2>Items Sold Weekly</h2>
        <canvas id="weeklySalesChart"></canvas>

    </div>
</div>



    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

   
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const months = <?php echo json_encode($months); ?>; 
        const totals = <?php echo json_encode($totals); ?>;

        const ctx = document.getElementById('monthlySalesChart').getContext('2d');
        const monthlySalesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months, 
                datasets: [{
                    label: 'Monthly Sales (₱)',
                    data: totals,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Sales (₱)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    });
</script>



   

    <script>
    
    const receivedData = {
        labels: ['Received', 'Not Received'],
        datasets: [{
            label: 'Items Count',
            data: [<?php echo $totalReceived; ?>, <?php echo $totalNotReceived; ?>],
            backgroundColor: ['#4caf50', '#f44336'],
            borderColor: ['#388e3c', '#d32f2f'],
            borderWidth: 1
        }]
    };

    const receivedConfig = {
        type: 'bar',
        data: receivedData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Status'
                    }
                }
            }
        }
    };

    const ctxReceived = document.getElementById('receivedChart').getContext('2d');
    new Chart(ctxReceived, receivedConfig);

 
    const itemNames = <?php echo json_encode(array_column($itemsData, 'item_name')); ?>;
    const itemQuantities = <?php echo json_encode(array_column($itemsData, 'total_quantity')); ?>;

    const itemsDataConfig = {
        labels: itemNames,
        datasets: [{
            label: 'Quantities',
            data: itemQuantities,
            backgroundColor: '#2196f3',
            borderColor: '#0d47a1',
            borderWidth: 1
        }]
    };

    const itemsConfig = {
        type: 'line',
        data: itemsDataConfig,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantity'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Items'
                    }
                }
            }
        }
    };

    const ctxItems = document.getElementById('itemQuantitiesChart').getContext('2d');
    new Chart(ctxItems, itemsConfig);
</script>

    <script>
    
    const ctx = document.getElementById('ordersChart').getContext('2d');
    const ordersChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Paid', 'Unpaid'], 
            datasets: [{
                label: 'Total Amount (₱)',
                data: [<?php echo $totalPaid; ?>, <?php echo $totalUnpaid; ?>], 
                backgroundColor: ['#4caf50', '#f44336'], 
                borderColor: ['#388e3c', '#d32f2f'], 
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₱' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount (₱)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Payment Status'
                    }
                }
            }
        }
    });
</script>
    
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Data for inventory and items sold
        const productNames = <?php echo json_encode(array_column($all_items, 'name')); ?>;
        const quantities = <?php echo json_encode(array_column($all_items, 'quantity')); ?>;
        const productSizes = <?php echo json_encode(array_column($all_items, 'size')); ?>;
        const totalQuantitiesSold = <?php echo json_encode($totalQuantities); ?>;

        // Colors for datasets
        const colors = [
            'rgba(54, 162, 235, 0.6)', // Blue for inventory
            'rgba(255, 99, 132, 0.6)'  // Red for sold items
        ];

        const ctx = document.getElementById('stocksChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: productNames, // Shared labels
                datasets: [
                    {
                        label: 'Current Inventory',
                        data: quantities, // Data for inventory
                        backgroundColor: colors[0],
                        borderColor: colors[0].replace('0.6', '1'),
                        borderWidth: 1
                    },
                    {
                        label: 'Total Quantity Sold',
                        data: totalQuantitiesSold, // Data for sold items
                        backgroundColor: colors[1],
                        borderColor: colors[1].replace('0.6', '1'),
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Item Name'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return `${tooltipItem.dataset.label}: ${tooltipItem.raw}`;
                            }
                        }
                    }
                }
            }
        });

        // Generate custom legend for the inventory chart
        const stocksLegend = document.getElementById('stocksLegend');
        productNames.forEach((name, index) => {
            const legendItem = document.createElement('div');
            legendItem.className = 'legend-item';
            legendItem.innerHTML = `
                <div class="legend-color" style="background-color: ${colors[index % colors.length]}"></div>
                ${name} (${productSizes[index]})
            `;
            stocksLegend.appendChild(legendItem);
        });
    });
</script>



<script>
    document.addEventListener("DOMContentLoaded", function () {
        const itemNames = <?php echo json_encode($itemNames); ?>;
        const totalQuantities = <?php echo json_encode($totalQuantities); ?>;
        

        const ctx = document.getElementById('itemsSoldChart').getContext('2d');
        const itemsSoldChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: itemNames,  // Item names as the labels
                datasets: [{
                    label: 'Total Quantity Sold',
                    data: totalQuantities,  // Corresponding quantity sold data
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',  // Blue color for the bars
                    borderColor: 'rgba(54, 162, 235, 1)',  // Darker blue border
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Quantity Sold'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Item Name'
                        }
                    }
                }
            }
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const weeklySalesData = <?php echo json_encode($weeklySalesData); ?>;

        const weekLabels = weeklySalesData.map(data => `Week ${data.week_number}`);
        const salesAmount = weeklySalesData.map(data => data.total_amount);

        const ctx = document.getElementById('weeklySalesChart').getContext('2d');
        const weeklySalesChart = new Chart(ctx, {
            type: 'line', 
            data: {
                labels: weekLabels, 
                datasets: [{
                    label: 'Total Sales (Weekly)',
                    data: salesAmount, 
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true, 
                    borderWidth: 2,
                    tension: 0.4 
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Sales (Amount)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toFixed(2); 
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Week'
                        }
                    }
                }
            }
        });
    });
</script>



</body>
</html>