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

// Prepare SQL query with search and sorting
$all_items_stmt = $conn->prepare("
    SELECT i.id, i.item_code, i.name, i.size, i.quantity, i.price, COALESCE(SUM(s.quantity_sold), 0) AS total_sold
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
    SELECT i.id, i.item_code, i.name, i.size, i.quantity, i.price, COALESCE(SUM(s.quantity_sold), 0) AS total_sold
    FROM inventory i
    LEFT JOIN sales s ON i.id = s.product_id
    GROUP BY i.id
    HAVING total_sold > 1
    ORDER BY total_sold DESC
");
$popular_items_stmt->execute();
$popular_items = $popular_items_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <style>
        .status-green { background-color: #d4edda; }
        .status-yellow { background-color: #fff3cd; }
        .status-red { background-color: #f8d7da; }
        .legend { display: flex; flex-wrap: wrap; margin-top: 10px; }
        .legend-item { display: flex; align-items: center; margin-right: 15px; font-weight: 500; }
        .legend-color { width: 20px; height: 20px; margin-right: 5px; border: none; }
    </style>

    <?php if (isset($_SESSION['login_success'])): ?>
        <script>
            alert("<?php echo htmlspecialchars($_SESSION['login_success']); ?>");
        </script>
        <?php unset($_SESSION['login_success']); ?>
    <?php endif; ?>
</head>
<body>
    <div class="container">

        <h2>Stocks and Sold Items Chart</h2>
        <canvas id="combinedChart"></canvas>
        <div class="legend" id="combinedLegend"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const productNames = <?php echo json_encode(array_column($all_items, 'name')); ?>;
        const quantities = <?php echo json_encode(array_column($all_items, 'quantity')); ?>;
        const totalSoldValues = <?php echo json_encode(array_column($popular_items, 'total_sold')); ?>;

        const colors = [
            'rgba(54, 162, 235, 0.6)', // Color for stock
            'rgba(255, 99, 132, 0.6)'  // Color for sold items
        ];

        const ctxCombined = document.getElementById('combinedChart').getContext('2d');
        new Chart(ctxCombined, {
            type: 'bar',
            data: {
                labels: productNames,
                datasets: [
                    {
                        label: 'Remaining Stock',
                        data: quantities,
                        backgroundColor: colors[0],
                        borderColor: colors[0].replace('0.6', '1'),
                        borderWidth: 1
                    },
                    {
                        label: 'Total Sold',
                        data: totalSoldValues,
                        backgroundColor: colors[1],
                        borderColor: colors[1].replace('0.6', '1'),
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

        const combinedLegend = document.getElementById('combinedLegend');
        productNames.forEach((name, index) => {
            const legendItem = document.createElement('div');
            legendItem.className = 'legend-item';
            legendItem.innerHTML = `
                <div class="legend-color" style="background-color: ${colors[0]}"></div> ${name} (Stock: ${quantities[index]}) <br>
                <div class="legend-color" style="background-color: ${colors[1]}"></div> ${name} (Sold: ${totalSoldValues[index]})
            `;
            combinedLegend.appendChild(legendItem);
        });
    </script>
</body>
</html>
