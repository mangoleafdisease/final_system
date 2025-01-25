<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="sidebar.css">
    <!-- Add jQuery CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

</head>
<body>

    <div class="sidebar">
        <h1>Capstone Project Business Center</h1>
        <hr>
        <div class="sidebar-container">
            <div class="menu"><a href="#" class="menu-link">Dashboard</a></div>

            <div class="menu"><a href="#" class="menuitem">Transaction</a>
                <div class="submenu">
                    <a href="#" class="subitem">Purchase Item</a>
                </div>
            </div>

            <div class="menu"><a href="#" class="menuitem">Inventory</a>
                <div class="submenu">
                    <a href="#" class="subitem">Add Item</a>
                    <a href="#" class="subitem">Register Item</a>
                </div>
            </div>

            <div class="menu"><a href="#" class="menuitem">Report</a>
                <div class="submenu">
                    <a href="#" class="subitem">Item Sold</a>
                    <a href="#" class="subitem">Lost Item</a>
                    <a href="#" class="subitem">Return Item</a>
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
