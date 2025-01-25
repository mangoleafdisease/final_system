<?php
session_start();
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}

// Enable error reporting for debugging (remove or comment this in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db.php';

// Initialize variables
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$orderBy = isset($_GET['order_by']) && in_array($_GET['order_by'], ['name', 'price', 'quantity']) ? $_GET['order_by'] : 'name';
$orderDirection = isset($_GET['order_direction']) && in_array($_GET['order_direction'], ['ASC', 'DESC']) ? $_GET['order_direction'] : 'ASC';
$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : '';

// Prepare SQL query with search and sorting
$all_items_stmt = $conn->prepare("
    SELECT i.id, i.item_code, i.name, i.size, i.quantity, i.price, 
           COALESCE(SUM(s.quantity_sold), 0) AS total_sold, 
           COALESCE(SUM(s.quantity_sold * i.price), 0) AS total_sales
    FROM inventory i
    LEFT JOIN sales s ON i.id = s.product_id
    WHERE i.name LIKE :search
    GROUP BY i.id
    ORDER BY $orderBy $orderDirection
");
$all_items_stmt->execute(['search' => '%' . $searchTerm . '%']);
$all_items = $all_items_stmt->fetchAll();

// Fetch popular items (products with more than 1 sold)
$popular_items_stmt = $conn->prepare("
    SELECT i.id, i.item_code, i.name, i.size, i.quantity, i.price, 
           COALESCE(SUM(s.quantity_sold), 0) AS total_sold
    FROM inventory i
    LEFT JOIN sales s ON i.id = s.product_id
    GROUP BY i.id
    HAVING total_sold > 1
    ORDER BY total_sold DESC
");
$popular_items_stmt->execute();
$popular_items = $popular_items_stmt->fetchAll();

// Calculate Total Purchase Due
$totalPurchaseDue = 0;
foreach ($all_items as $item) {
    $totalPurchaseDue += $item['quantity'] > 0 ? $item['price'] * $item['quantity'] : 0; // Calculate based on quantity
}

// Calculate Total Sales Due
$totalSalesDue = 0;
$sales_stmt = $conn->prepare("
    SELECT SUM(s.quantity_sold * i.price) AS total_sales
    FROM sales s
    JOIN inventory i ON s.product_id = i.id
");
$sales_stmt->execute();
$totalSalesDue = $sales_stmt->fetchColumn() ?: 0; // Get total sales amount or 0 if none

// Calculate Total Customers
$customers_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT customer_name) AS total_customers 
    FROM transactions
");
$customers_stmt->execute();
$totalCustomers = $customers_stmt->fetchColumn() ?: 0; // Get total customers or 0 if none

// Calculate Weekly Earnings
$weeklyEarningsStmt = $conn->prepare("
    SELECT COALESCE(SUM(s.quantity_sold * i.price), 0) AS weekly_earnings
    FROM sales s
    JOIN inventory i ON s.product_id = i.id
    WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
");
$weeklyEarningsStmt->execute();
$weeklyEarnings = $weeklyEarningsStmt->fetchColumn() ?: 0; // Get weekly earnings amount or 0 if none





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sssAdmin Dashboard</title>
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

         <!-- boxxxxesssss -->
            <!-- Financial Summary Section -->
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
            
            <div class="totalpiechrt">
                <h2>Total Sold Items Chart</h2>
                <hr>
                <canvas id="soldItemsChart"></canvas>
            </div>
        
        </div>

       

       



        
        



        


    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    


    <script>
        const productNames = <?php echo json_encode(array_column($all_items, 'name')); ?>;
        const quantities = <?php echo json_encode(array_column($all_items, 'quantity')); ?>;
        const totalSales = <?php echo json_encode(array_column($all_items, 'total_sales')); ?>;
        const productSizes = <?php echo json_encode(array_column($all_items, 'size')); ?>;

        const colors = [
            'rgba(54, 162, 235, 0.6)', 'rgba(255, 99, 132, 0.6)', 
            'rgba(75, 192, 192, 0.6)', 'rgba(255, 206, 86, 0.6)', 
            'rgba(153, 102, 255, 0.6)', 'rgba(255, 159, 64, 0.6)'
        ];

        const ctxStocks = document.getElementById('stocksChart').getContext('2d');
        new Chart(ctxStocks, {
            type: 'bar',
            data: {
                labels: productNames,
                
                datasets: [
                    {
                        label: 'Quantity',
                        data: quantities,
                        backgroundColor: colors,
                        borderColor: colors.map(color => color.replace('0.6', '6')),
                        borderWidth: 1,
                        pointStyle: 'rectRot'
                        
                    },
                    {
                        label: 'Sales',
                        data: totalSales,
                        backgroundColor: colors.map(color => color.replace('0.6', '0.8')),
                        borderColor: colors.map(color => color.replace('0.6', '1')),
                        borderWidth: 1
                    }
                ]
            },
            options: { 
                scales: { 
                    y: { 
                        beginAtZero: true 
                    } 
                } 
            }
        });

        const stocksLegend = document.getElementById('stocksLegend');
        productNames.forEach((name, index) => {
            const legendItem = document.createElement('div');
            legendItem.className = 'legend-item';
            legendItem.innerHTML = `<div class="legend-color" style="background-color: ${colors[index % colors.length]}"></div>${name} (${productSizes[index]})`;
            stocksLegend.appendChild(legendItem);
        });



        // Data for the pie chart
        const totalSold = <?php echo json_encode(array_column($all_items, 'total_sold')); ?>;


     const filteredData = totalSold.map((value, index) => {
        return value > 0 ? { name: productNames[index], value, color: colors[index] } : null;
    }).filter(item => item !== null);

    const filteredLabels = filteredData.map(item => item.name);
    const filteredValues = filteredData.map(item => item.value);
    const filteredColors = filteredData.map(item => item.color);

    const ctxSoldItems = document.getElementById('soldItemsChart').getContext('2d');

    new Chart(ctxSoldItems, {
        type: 'pie',
        data: {
            labels: filteredLabels, // Only include item names with non-zero sales
            datasets: [{
                label: 'Total Sold',
                data: filteredValues, // Only include non-zero sold quantities
                backgroundColor: filteredColors, // Only include colors for non-zero sales
                borderColor: filteredColors.map(color => color.replace('0.6', '1')), // Adjust border colors
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    reverse: true, // Display the legend items from bottom to top
                    labels: {
                        boxWidth: 20 // Adjust the width of the legend items
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return `${tooltipItem.label}: ${tooltipItem.raw}`; // Format tooltip with item name and sold quantity
                        }
                    }
                },
                datalabels: {
                    color: '#fff', // Text color
                    font: {
                        weight: 'bold'
                    },
                    formatter: function(value, context) {
                        return `${context.chart.data.labels[context.dataIndex]}: ${value}`; // Format displayed labels with item name and sold quantity
                    }
                }
            }
        },
        plugins: [ChartDataLabels] // Register the datalabels plugin
    });


       



    </script>

</body>
</html>
