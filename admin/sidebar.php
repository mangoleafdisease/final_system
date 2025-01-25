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

include '../db.php';
?>


<head>
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>
        <!-- sidddeebarrrr -->
        <div class="sidebar">
        <div class="sidebar-container">
            <div class="menu"><a href="dashboard.php" class="menu-link">
                <i class="fa-solid fa-chart-column"></i>
                Dashboard</a>
            </div>
            <div class="menu"><a href="#" class="menuitem">
                <i class="fa-solid fa-receipt"></i>
                Transaction</a>
                <div class="submenu">
                    <!-- <a href="purchase.php" class="subitem">Buy Item</a>
                    <a href="purchases.php" class="subitem">Buy Item</a> -->
                    <a href="view_transactions.php" class="subitem">Purchased Item</a>
                    <!-- <a href="return_item.php" class="subitem">Return Item</a> -->
                </div>
            </div>
            <div class="menu"><a href="#" class="menuitem">
                <i class="fa-solid fa-boxes-stacked"></i>
                Item List</a>
                <div class="submenu">
                    <a href="add_stock.php" class="subitem">Add Stock</a>
                    <a href="add_item.php" class="subitem">Add Item</a>
                    <!-- <a href="add_item.php" class="subitem">Item Code</a> -->
                    <a href="edit_price.php" class="subitem">Update Item Price</a>
                </div>  
            </div>
            <div class="menu"><a href="#" class="menuitem">
                <i class="fa-solid fa-box"></i>
                Stock</a>
                <div class="submenu">
                    <a href="totalstocks.php" class="subitem">Total Stocks</a>
                    <a href="view_returned_item.php" class="subitem">Returned Items</a>
                    <a href="#" class="subitem">Damage Item</a>
                </div>
            </div>
            <div class="menu"><a href="#" class="menuitem">
                <i class="fa-solid fa-cart-shopping"></i>   
                Purchase Item</a>
                <div class="submenu">
                    <!-- <a href="#" class="subitem">Purchase Chart</a> -->
                    <a href="customer_orders.php" class="subitem">Orders</a>
                    <a href="paid_items.php" class="subitem">Paid Orders</a>
                </div>
            </div>



            <div class="menu"><a href="all_user.php" class="menu-link">
                 <i class="fa fa-users"></i>
                Users</a>
            </div>

            <div class="menu"><a href="#" class="menuitem">
                <i class="fa-solid fa-flag"></i>
                Report</a>
                <div class="submenu">
                    <a href="sold_report.php" class="subitem">Item Sold</a>
                    <a href="paid_report.php" class="subitem">Item Paid</a>
                    <a href="received_item_report.php" class="subitem">Item Received</a>
           
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function(){
            $('.menuitem').click(function(){
                $(this).next('.submenu').slideToggle();
            });
        });
    </script>



</body> 
</html>