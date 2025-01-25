<?php
// Start a session only if it isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to home page if the user is not logged in
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}

// Include database connection
include '../db.php';

// Query to fetch products
$productStmt = $conn->prepare("SELECT name FROM inventory");
$productStmt->execute();

// Query to fetch year levels
$yearLevelStmt = $conn->prepare("SELECT DISTINCT year_level FROM inventory");
$yearLevelStmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/sidebar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-container">
        

            <div class="menu">
                <a href="#" class="menuitem">
                    <i class="fa-solid fa-boxes-stacked"></i> Product
                </a>
                <div class="submenu">
                    <a href="all_items.php" class="subitem">Items</a>
                </div>
            </div>

            <div class="menu">
                <a href="#" class="menuitem">
                    <i class="fa-solid fa-cart-shopping"></i> Purchase Item
                </a>
                <div class="submenu">
                    <a href="customer_added_cart.php" class="subitem">Cart</a>
                    <a href="purchase_history.php" class="subitem">Purchase History</a>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('.menuitem').click(function() {
                const submenu = $(this).next('.submenu');
                submenu.toggleClass('active');
            });

            $('.year_level').click(function(event) {
                event.stopPropagation();
                const submenu = $(this).next('.submenu');
                submenu.toggleClass('active');
            });
        });

    </script>
</body>
</html>
