<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../home.php");
    exit();
}

session_regenerate_id(true);

// Include database connection
include '../db.php';

// Initialize variables for search, ordering, and direction
$searchTerm = $_GET['search'] ?? '';
$orderBy = $_GET['order_by'] ?? '';
$orderDirection = $_GET['order_direction'] ?? 'ASC';

// Validate inputs
$allowedOrders = ['elementary', 'Junior High School', 'Senior High School', 'College'];
if (!in_array($orderBy, $allowedOrders)) {
    $orderBy = '';
}
$orderDirection = ($orderDirection === 'DESC') ? 'DESC' : 'ASC';

// Calculate total stock of all products
$totalstocksallproductsStmt = $conn->prepare("SELECT SUM(quantity) AS total_stock FROM inventory");
$totalstocksallproductsStmt->execute();
$totalstocksallproducts = $totalstocksallproductsStmt->fetchColumn() ?: 0;

// Build the SQL query with filtering and ordering
$query = "SELECT * FROM inventory WHERE name LIKE :searchTerm";
if ($orderBy) {
    $query .= " AND year_level = :orderBy";
}
$query .= " ORDER BY year_level $orderDirection";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
if ($orderBy) {
    $stmt->bindValue(':orderBy', $orderBy);
}
$stmt->execute();

// Fetch the logged-in user's name
$username = $_SESSION['all_username'] ?? 'Guest';
$profilePic = '../img/default-profile.png'; // Default profile picture

$userStmt = $conn->prepare("SELECT username FROM admins WHERE username = :username");
$userStmt->bindParam(':username', $username, PDO::PARAM_STR);
$userStmt->execute();
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
if ($user) {
    $username = $user['username'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo web normi.png">
    <title>Business Center Total Stocks</title>
    <link rel="stylesheet" href="css/totalstocks.css">
    <!-- Include Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/navbar.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="../img/logohome.png" alt="Logo">
            <h1>NORMI Business Center</h1>
        </div>
        <div class="username">
            <div class="user">
                <div class="userpic">
                    <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="User Picture">
                </div>
                <p aria-label="Logged-in Username"><?php echo htmlspecialchars($username); ?></p> 
                <i class="fa-solid fa-caret-down" style="color: #fff"></i>
            </div>
            <div class="logout" style="display: none;">
                <a href="logout.php">
                    <i class="fa-solid fa-power-off"></i> Log out
                </a>
            </div>
        </div>
    </nav>

    <script type="text/javascript">
        $(document).ready(function () {
            // Toggle the logout menu when the username section is clicked
            $('.username').on('click', function () {
                $(this).find('.logout').slideToggle();
            });

            // Close the logout menu if clicked outside
            $(document).on('click', function (event) {
                if (!$(event.target).closest('.username').length) {
                    $('.logout').slideUp();
                }
            });
        });
    </script>

    <?php include_once('sidebar.php'); ?>

    <div class="container">
        <div class="toplabel">
            <h1>Stocks Overview</h1>
            <p>Stock | Total Stock</p>
        </div>
        <hr>

        <div class="displayallitems">
            <div class="total-stock-box">
                <h3>
                    Total Stock of All Products
                    <p><i class="fa-solid fa-box-open"></i>
                        <span id="total-stock"><?php echo number_format($totalstocksallproducts, 0); ?></span>
                    </p>
                </h3>
            </div>

            <div class="search-bar">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Search Item Name" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <select name="order_by">
                        <option value="" class="select_yl">-- Program --</option>
                        <option value="elementary" <?php if ($orderBy === 'elementary') echo 'selected'; ?>>Elementary</option>
                        <option value="Junior High School" <?php if ($orderBy === 'Junior High School') echo 'selected'; ?>>JHS</option>
                        <option value="Senior High School" <?php if ($orderBy === 'Senior High School') echo 'selected'; ?>>SHS</option>
                        <option value="College" <?php if ($orderBy === 'College') echo 'selected'; ?>>College</option>
                    </select>
                    <select name="order_direction"> 
                        <option value="DESC" <?php if ($orderDirection === 'DESC') echo 'selected'; ?>>Descending</option>
                        <option value="ASC" <?php if ($orderDirection === 'ASC') echo 'selected'; ?>>Ascending</option>
                    </select>
                    <button type="submit">Search</button>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Size</th>
                        <th>Stock</th>
                        <th>Program</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="product-table-body">
                    <?php
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['size']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['year_level']) . "</td>";
                            echo "<td><a href='add_stock.php?id=" . urlencode($row['id']) . "' class='add-stock-btn'>Add stock</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No data available</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
