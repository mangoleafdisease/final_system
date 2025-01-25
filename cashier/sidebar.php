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

            <div class="menu"><a href="customer_orders_list.php" class="menuitem">
                <i class="fa-solid fa-receipt"></i>
                All Order</a>
            </div>
            <div class="menu"><a href="unpaid_orders.php" class="menuitem">
                <i class="fa-solid fa-receipt"></i>
                Today Order</a>
            </div>
            <div class="menu"><a href="paid_orders.php" class="menuitem">
                <i class="fa-solid fa-receipt"></i>
                Paid Order</a>
            </div>
        </div>
    </div>


</body> 
</html>