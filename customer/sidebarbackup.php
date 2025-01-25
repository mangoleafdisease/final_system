<?php
session_start();

// Redirect to home page if the user is not logged in
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}

// Include database connection
include '../db.php';

// Query to fetch all products for table display and chart
$stmt = $conn->prepare("SELECT * FROM inventory");
$stmt->execute();
?>

<head>
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-container">
            <div class="menu">
                <a href="#" class="menuitem">
                    <i class="fa-solid fa-boxes-stacked"></i> Product
                </a>
                
                <div class="submenu itemlist">
                    <a href="#" class="productlist">
                        <i class="fa-solid fa-list"></i> Item List
                    </a>
                    
                    <div class="submenu products">
                        <a href="#" class="subproduct">- P.E</a>   
                        <a href="#" class="subproduct">- Uniform</a>

                        <?php
                            // Fetch each product and display in a link within the submenu
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                // Assuming 'product_name' is the column that holds the product name
                                echo '<a href="#" class="subproduct">• ' . htmlspecialchars($row['name']) . '</a>';
                            }
                        ?>
                    </div>
                </div>
            </div>

            <div class="menu">
                <a href="#" class="menuitem">
                    <i class="fa-solid fa-cart-shopping"></i> Purchase Item
                </a>
                <div class="submenu">
                    <a href="#" class="subitem">Cart</a>
                    <a href="#" class="subitem">Purchase History</a>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function(){
            $('.menuitem').click(function(){
                $(this).next('.submenu').slideToggle();
            });

            $('.productlist').click(function(event){
                event.stopPropagation(); 
                $(this).next('.products').slideToggle();
            });
        });
    </script>
</body>
</html>
