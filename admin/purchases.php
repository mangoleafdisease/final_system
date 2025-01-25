<?php
session_start();
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}

session_regenerate_id(true);

include '../db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

$query = $pdo->query("SELECT * FROM inventory");
$allProducts = $query->fetchAll(PDO::FETCH_ASSOC);
$products = $allProducts;


$groupedProducts = [];
foreach ($allProducts as $product) {
    $name = $product['name'];
    if (!isset($groupedProducts[$name])) {
        $groupedProducts[$name] = $product;
        $groupedProducts[$name]['sizes'] = explode(',', $product['size']);
    } else {
        $additionalSizes = explode(',', $product['size']);
        $groupedProducts[$name]['sizes'] = array_unique(array_merge($groupedProducts[$name]['sizes'], $additionalSizes));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $quantity_sold = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
        $customer_name = htmlspecialchars(trim($_POST['customer_name']));
        $description = htmlspecialchars(trim($_POST['description']));
        $product_size = htmlspecialchars(trim($_POST['product_size']));
        $product_name = htmlspecialchars(trim($_POST['product_name']));

        if ($quantity_sold <= 0 || empty($customer_name) || empty($product_size) || empty($product_name)) {
            throw new Exception('Invalid input data! Please check your entries.');
        }

        $stmt = $pdo->prepare("SELECT * FROM inventory WHERE name = :product_name AND size LIKE :product_size");
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindValue(':product_size', '%' . $product_size . '%');
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            throw new Exception('Error: Item not found!');
        }

        $item_id = $item['id'];
        $item_quantity = intval($item['quantity']);
        $item_price = floatval($item['price']);

        if ($item_quantity < $quantity_sold) {
            throw new Exception('Error: Insufficient stock!');
        }

        $pdo->beginTransaction();

        $new_quantity = $item_quantity - $quantity_sold;
        $update_stmt = $pdo->prepare("UPDATE inventory SET quantity = :new_quantity WHERE id = :item_id");
        $update_stmt->bindParam(':new_quantity', $new_quantity, PDO::PARAM_INT);
        $update_stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $update_stmt->execute();

        $sales_stmt = $pdo->prepare("INSERT INTO sales (product_id, quantity_sold) VALUES (:item_id, :quantity_sold)");
        $sales_stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $sales_stmt->bindParam(':quantity_sold', $quantity_sold, PDO::PARAM_INT);
        $sales_stmt->execute();

        $total_price = $quantity_sold * $item_price;
        $transactions_stmt = $pdo->prepare(
            "INSERT INTO transactions (item_id, quantity, total_price, purchase_date, customer_name, description, size) 
            VALUES (:item_id, :quantity, :total_price, NOW(), :customer_name, :description, :size)"
        );
        $transactions_stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $transactions_stmt->bindParam(':quantity', $quantity_sold, PDO::PARAM_INT);
        $transactions_stmt->bindParam(':total_price', $total_price, PDO::PARAM_STR);
        $transactions_stmt->bindParam(':customer_name', $customer_name, PDO::PARAM_STR);
        $transactions_stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $transactions_stmt->bindParam(':size', $product_size, PDO::PARAM_STR);
        $transactions_stmt->execute();

        $pdo->commit();
        $_SESSION['purchase_success'] = "Purchase completed: {$item['name']} for ₱" . number_format($total_price, 2);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['purchase_error'] = $e->getMessage();
    }
    header("Location: purchases.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Center</title>
    <link rel="stylesheet" href="css/purchases.css">

    <?php if (isset($_SESSION['purchase_success'])): ?>
        <script>
            alert("<?php echo htmlspecialchars($_SESSION['purchase_success']); ?>");
        </script>
        <?php unset($_SESSION['purchase_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['purchase_error'])): ?>
        <script>
            alert("<?php echo htmlspecialchars($_SESSION['purchase_error']); ?>");
        </script>
        <?php unset($_SESSION['purchase_error']); ?>
    <?php endif; ?>
</head>
<body>
    <?php include_once('navbar.php'); ?>
    <?php include_once('sidebar.php'); ?>



    <div class="container">
        <div class="toplabel">
            <h1>Purchase</h1>
            <p>Transaction | </p>
            <p>purchase item</p>
        </div>
        <hr>

        <div class="container-display">
            <div class="search-bar">
                    <input type="text" id="search" placeholder="Search for items..." onkeyup="filterProducts()">
                    <div class="spinner" id="loading-spinner" style="display: none;"></div>
                </div>

                <div class="product-list" id="product-list">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $item): ?>
                            <div class="menu-card" 
                                data-name="<?php echo strtolower(htmlspecialchars($item['name'])); ?>" 
                                data-size="<?php echo htmlspecialchars($item['size']); ?>">
                                <!-- Card content -->
                            </div> 
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No products available</p>
                    <?php endif; ?>
                </div>


                <script>
                    function filterProducts() {
                        const searchInput = document.getElementById('search').value.toLowerCase().trim();
                        const products = document.querySelectorAll('.menu-card');
                        const spinner = document.getElementById('loading-spinner');

                        spinner.style.display = 'block';

                        setTimeout(() => {
                            let foundMatch = false;
                            products.forEach(product => {
                                const productName = product.getAttribute('data-name');
                                if (productName.includes(searchInput)) {
                                    product.style.display = 'block'; // Show matched products
                                    foundMatch = true;
                                } else {
                                    product.style.display = 'none'; // Hide non-matching products
                                }
                            });

                            spinner.style.display = 'none';

                            // Show message if no products match
                            if (!foundMatch) {
                                document.getElementById('no-products-message').style.display = 'block';
                            } else {
                                document.getElementById('no-products-message').style.display = 'none';
                            }
                        }, 300);
                    }


                </script>
        </div>




        
        <!-- --------------------------------------------------------------------------------------------------- -->
        <div class="modal-backdrop" id="modalBackdrop" onclick="closeModal()"></div>

        <div class="container-display"> <!-- Add this div to wrap the cards -->
            <?php foreach ($groupedProducts as $index => $product): ?>
                <div class="card" onclick="toggleSizes('<?php echo htmlspecialchars($index); ?>')">
                    <?php 
                        $imagePath = '../uploads/' . htmlspecialchars($product['image']);
                        if (!empty($product['image']) && file_exists($imagePath)): 
                    ?>
                        <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null; this.src='uploads/default.jpg';">
                    <?php else: ?>
                        <img src="uploads/default.jpg" alt="Default Image">
                    <?php endif; ?>
                    <div class="card-title"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="sizes" id="sizes-<?php echo htmlspecialchars($index); ?>" style="display:none;">
                        <?php 
                            $item_price = floatval($product['price']);
                            foreach ($product['sizes'] as $size): ?>
                                <button data-price="<?php echo htmlspecialchars($item_price); ?>" onclick="selectSize(event, '<?php echo htmlspecialchars($size); ?>', '<?php echo htmlspecialchars($product['name']); ?>')">
                                    <?php echo htmlspecialchars($size); ?>
                                </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div> <!-- End of container-display div -->

        <div id="purchaseModal" style="display:none;">
            <form method="POST" action="" onsubmit="return validateSizeSelection()">
                <div class="img-displaying">
                    <img class="modalProductImage" id="modalProductImage" src="" alt="Selected Product Image" style="width:100%;height:auto;display:block;margin-bottom:15px;border-radius:20px;">

                </div>
            <div class="inputing-display">
                <input type="hidden" name="product_name" id="modalProductName" value="">
                <label for="">Size:</label>
                <input type="text" name="product_size" id="modalProductSize" required readonly>

                <label for="">Price:</label>
                <input type="text" name="price" id="modalProductPrice" required readonly>

                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" min="1" required id="quantityInput" onchange="calculateTotalPrice()">

                <label for="">Total Price:</label>
                <input type="text" name="total_price" id="totalPrice" required readonly>

                <label for="customer_name">Customer Name:</label>
                <input type="text" name="customer_name" required>

                <label for="description">Description:</label>
                <textarea name="description"></textarea>    

                <button type="submit">Purchase</button>
                <button type="button" onclick="closeModal()">Cancel</button>
            </div>   
                
            </form>
        </div>
    </div>

<script>
    let selectedSize = null;

    function toggleSizes(index) {
        const sizesDiv = document.getElementById('sizes-' + index);
        sizesDiv.style.display = sizesDiv.style.display === 'none' ? 'flex' : 'none';
    }

    function selectSize(event, size, productName) {
        selectedSize = size; 
        document.getElementById('modalProductSize').value = size;
        document.getElementById('modalProductName').value = productName;

        // Get the price from the button that was clicked
        const price = event.target.getAttribute('data-price');
        document.getElementById('modalProductPrice').value = price;

        // Get the product image and set it in the modal
        const productImage = event.target.closest('.card').querySelector('img').src;
        document.getElementById('modalProductImage').src = productImage;

        // Reset quantity input and total price
        document.getElementById('quantityInput').value = 1; // Reset to 1 or any default value
        calculateTotalPrice(); // Update total price based on the selected price and default quantity

        openModal();
    }



    function openModal() {
        document.getElementById('purchaseModal').style.display = 'block';
        document.getElementById('modalBackdrop').style.display = 'block'; // Show backdrop
    }

    function closeModal() {
        document.getElementById('purchaseModal').style.display = 'none';
        document.getElementById('modalBackdrop').style.display = 'none'; // Hide backdrop
        selectedSize = null;
    }

    function validateSizeSelection() {
        const formSize = document.getElementById('modalProductSize').value;
        if (formSize !== selectedSize) {
            alert("Error: Selected size does not match. Please try again.");
            return false;
        }
        return true;
    }

    function calculateTotalPrice() {
        const price = parseFloat(document.getElementById('modalProductPrice').value) || 0;
        const quantity = parseInt(document.getElementById('quantityInput').value) || 0;
        const totalPrice = price * quantity;

        document.getElementById('totalPrice').value = totalPrice.toFixed(2); // Set total price formatted to 2 decimal places
    }

</script>
</body>
</html>
