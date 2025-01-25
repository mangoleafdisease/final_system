<?php
session_start();

// Ensure the user is logged in as a customer
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in first.']);
    header("Location: ../home.php");
    exit();
}

// Ensure customer-specific session variables are available
if (!isset($_SESSION['id']) || !isset($_SESSION['customer_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Customer information not found.']);
    exit();
}

// Regenerate session ID for security
session_regenerate_id(true);

// Include database connection
include '../db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Fetch items from the database
$items = [];
try {
    $stmt = $pdo->query("SELECT id, name, size, price, image FROM inventory");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching items: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="css/customer_cart.css">
</head>
<body>
<?php include_once('navbar.php'); ?>
<?php include_once('sidebar.php'); ?>
<div class="container-main">
    <div class="container">
        <h1>Shopping Cart</h1>
        
        <div class="items-container">   
            <h2>Available Items</h2>
            <hr>
            <div id="items" class="items-grid">
                <?php foreach ($items as $item): ?>
                    <div class="item-card">
                        <?php 
                            $imagePath = '../uploads/' . htmlspecialchars($item['image']);
                            if (!empty($item['image']) && file_exists($imagePath)): 
                        ?>
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image" onerror="this.onerror=null; this.src='uploads/default.jpg';">
                        <?php else: ?>
                            <img src="uploads/default.jpg" alt="Default Image" class="product-image">
                        <?php endif; ?>

                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-size">Size: <?php echo htmlspecialchars($item['size']); ?></div>
                            <div class="item-price">₱<?php echo htmlspecialchars($item['price']); ?></div>
                        </div>

                        <button class="add-to-cart-btn" onclick="addItemToCart('<?php echo htmlspecialchars($item['id']); ?>', '<?php echo htmlspecialchars($item['name']); ?>', '<?php echo htmlspecialchars($item['size']); ?>', <?php echo htmlspecialchars($item['price']); ?>)">
                            Add to Cart
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>


    <!-- Sidebar Cart -->
    <div class="cart-sidebar">
        <h2>Your Cart</h2>
        <ul id="cartItems"></ul>
        <div id="cartTotal" class="cart-total"></div>
        <button id="saveCartButton">Save Cart</button>
    </div>

</div>



<script>
    const cart = [];

    function addItemToCart(id, name, size, price) {
        const existingItem = cart.find(item => item.id === id && item.size === size);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ id, name, size, price, quantity: 1 });
        }
        displayCart();
    }

    function calculateTotal() {
        return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }

    function displayCart() {
        const cartItemsList = document.getElementById("cartItems");
        const cartTotal = document.getElementById("cartTotal");
        cartItemsList.innerHTML = "";

        cart.forEach((item, index) => {
            const listItem = document.createElement("li");
            listItem.className = "cart-item";
            listItem.innerHTML = `
                ${item.name} - Size: ${item.size} - ₱${item.price} (x${item.quantity})
                <button onclick="removeFromCart(${index})">Remove</button>
            `;
            cartItemsList.appendChild(listItem);
        });

        cartTotal.textContent = `Total: ₱${calculateTotal().toFixed(2)}`;
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        displayCart();
    }

    document.getElementById("saveCartButton").addEventListener("click", () => {
        if (cart.length === 0) {
            alert("Your cart is empty!");
            return;
        }

        fetch('save_cart_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(cart),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                cart.length = 0;
                displayCart();
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            alert(`An error occurred: ${error.message}`);
        });
    });

</script>

</body>
</html>

