<?php
session_start();
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}
include '../db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

$query = $pdo->query("SELECT * FROM inventory");
$products = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Inventory System</title>
    <link rel="shortcut icon" href="../img/logo web normi.png">
    <link rel="stylesheet" href="css/purchase.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <?php if (isset($_SESSION['purchase_success'])): ?>
        <script>
            alert("<?php echo htmlspecialchars($_SESSION['purchase_success']); ?>");
        </script>
        <?php unset($_SESSION['purchase_success']); ?>
    <?php endif; ?>

    <script>
        $(document).ready(function() {
            // Function to fetch matching barcodes and display suggestions
            $('#barcode').on('input', function() {
                var barcode = $(this).val();
                if (barcode.length > 0) {
                    $.ajax({
                        url: 'fetch_matching_barcodes.php',
                        type: 'POST',
                        data: { barcode: barcode },
                        success: function(response) {
                            var data = JSON.parse(response);
                            if (data.success) {
                                displayBarcodeSuggestions(data.barcodes, barcode);
                            } else {
                                $('#barcodeSuggestions').hide();
                            }
                        },
                        error: function() {
                            alert('Error fetching barcodes. Please try again.');
                        }
                    });
                } else {
                    $('#barcodeSuggestions').hide();
                }
            });

            function displayBarcodeSuggestions(barcodes, input) {
                var suggestionsHtml = '';
                barcodes.forEach(function(barcode) {
                    var highlighted = '<strong>' + barcode.substring(0, input.length) + '</strong>' + barcode.substring(input.length);
                    suggestionsHtml += '<div class="suggestion-item" style="padding: 5px; cursor: pointer;">' + highlighted + '</div>';
                });

                $('#barcodeSuggestions').html(suggestionsHtml).show();

                $('.suggestion-item').on('click', function() {
                    var selectedBarcode = $(this).text();
                    $('#barcode').val(selectedBarcode);
                    $('#barcodeSuggestions').hide();
                    fetchItemDetails(selectedBarcode);
                });
            }

            function fetchItemDetails(barcode) {
                $.ajax({
                    url: 'fetch_item.php',
                    type: 'POST',
                    data: { barcode: barcode },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.success) {
                            $('#item_code').val(data.item.item_code);
                            $('#name').val(data.item.name);
                            $('#size').val(data.item.size);
                            $('#price').val(data.item.price);
                            $('#message').text('');

                            if (data.item.image_url) {
                                $('#imagePreview').html('<img src="' + data.item.image_url + '" alt="Item Image" style="max-width: 100%; max-height: 100%;">');
                            } else {
                                $('#imagePreview').html('<p>No image available</p>');
                            }

                            calculateTotalPrice();
                        } else {
                            clearFields();
                            $('#message').text(data.message);
                        }
                    },
                    error: function() {
                        alert('Error fetching item details. Please try again.');
                    }
                });
            }

            function clearFields() {
                $('#item_code').val('');
                $('#name').val('');
                $('#size').val('');
                $('#price').val('');
                $('#total_price').val('');
                $('#imagePreview').html('');
            }

            $('#quantity').on('input', function() {
                calculateTotalPrice();
            });

            function calculateTotalPrice() {
                var price = parseFloat($('#price').val());
                var quantity = parseInt($('#quantity').val());
                if (!isNaN(price) && !isNaN(quantity) && quantity > 0) {
                    var totalPrice = price * quantity;
                    $('#total_price').val(totalPrice.toFixed(2));
                } else {
                    $('#total_price').val('');
                }
            }

            $('.menu-card').on('click', function() {
                var itemCode = $(this).data('code');
                var itemName = $(this).data('name');
                var itemSize = $(this).data('size');
                var itemPrice = $(this).data('price');
                var itemBarcode = $(this).data('barcode');

                $('#item_code').val(itemCode);
                $('#name').val(itemName);
                $('#size').val(itemSize);
                $('#price').val(itemPrice);
                $('#barcode').val(itemBarcode);
                $('#quantity').val(1);
                $('#message').text('');
                calculateTotalPrice();

                // Make barcode field optional
                $('#barcode').prop('required', false);
            });

            function handleCancel() {
                document.getElementById('myForm').reset();
                $('#message').text('');
                $('#barcode').prop('required', true);
            }

            $('.cancel-btn').on('click', handleCancel);
        });
    </script>
</head>
<body>
    <?php include_once('navbar.php'); ?>
    <?php include_once('sidebar.php'); ?>

    <div class="container">
        <div class="toplabel">
            <h1>Purchase</h1>
            <p>Transaction | </p>
            <p>purchace item</p>
        </div>
        <hr>
        <div id="message" style="color: red; margin-bottom: 10px;"></div>

        <form id="myForm" action="purchase_process.php" method="POST" enctype="multipart/form-data">
            <div class="form-flex">
                <div class="form-group form-column-1">
                    <label for="customer_name">Customer Name:</label>
                    <input type="text" name="customer_name" id="customer_name" required>
                    
                    <label for="barcode">Barcode:</label>
                    <input type="text" id="barcode" name="barcode">
                    <div id="barcodeSuggestions" style="border: 1px solid #ccc; display: none; max-height: 150px; overflow-y: auto; position: absolute; background-color: white; z-index: 1;"></div>

                    <label for="item_code">Item Code:</label>
                    <input type="text" id="item_code" name="item_code" required readonly>
                    
                    <label for="name">Item Name:</label>
                    <input type="text" id="name" name="name" required readonly>
                </div>
                
                <div class="form-group form-column-2">
                    <label for="size">Size:</label>
                    <input type="text" id="size" name="size" required readonly>

                    <label for="price">Price:</label>
                    <input type="text" id="price" name="price" required readonly>

                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" required>

                    <label for="total_price">Total Price:</label>
                    <input type="text" id="total_price" name="total_price" required readonly>
                </div>
                
                <div class="form-group form-column-1">                   
                    <label for="description">Description:</label>
                    <input type="text" name="description" id="description" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="submit-btn">Purchase</button>
                <button type="button" class="cancel-btn">Cancel</button>
            </div>
        </form>

        <div class="container-display">
            <div class="search-bar">
                <input type="text" id="search" placeholder="Search for items..." onkeyup="filterProducts()">
                <div class="spinner" id="loading-spinner" style="display: none;"></div>
            </div>
            
            <div class="product-list" id="product-list">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $item): ?>
                        <div class="menu-card" data-name="<?php echo strtolower(htmlspecialchars($item['name'])); ?>" data-code="<?php echo htmlspecialchars($item['item_code']); ?>" data-size="<?php echo htmlspecialchars($item['size']); ?>" data-price="<?php echo htmlspecialchars($item['price']); ?>" data-barcode="<?php echo htmlspecialchars($item['barcode']); ?>">
                            <div class="image-display">
                                <?php 
                                    $imagePath = 'uploads/' . htmlspecialchars($item['image']);
                                    if (!empty($item['image']) && file_exists($imagePath)): 
                                ?>
                                    <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" onerror="this.onerror=null; this.src='uploads/default.jpg';">
                                <?php else: ?>
                                    <img src="uploads/default.jpg" alt="Default Image">
                                <?php endif; ?>
                            </div>


                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div class="d-p-s">
                                    <p class="price">₱<?php echo number_format($item['price'], 2); ?></p>
                                    <span class="size"><?php echo htmlspecialchars($item['size']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No products available</p>
                <?php endif; ?>
            </div>

                <script>
                    function filterProducts() {
                        const searchInput = document.getElementById('search').value.toLowerCase();
                        const products = document.querySelectorAll('.menu-card');
                        const spinner = document.getElementById('loading-spinner');

                        spinner.style.display = 'block';
                        setTimeout(() => {
                            products.forEach(product => {
                                const productName = product.getAttribute('data-name');
                                product.style.display = productName.includes(searchInput) ? '' : 'none';
                            });
                            spinner.style.display = 'none';
                        }, 300);
                    }
                </script>
        </div>


    </div>
</body>
</html>
