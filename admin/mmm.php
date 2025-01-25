<?php
session_start();
include '../db.php'; // Include your database connection file

try {
    // Establish database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize it
    $customer_name = htmlspecialchars(trim($_POST['customer_name']));
    $product_name = htmlspecialchars(trim($_POST['product_name']));
    $product_size = htmlspecialchars(trim($_POST['product_size']));
    $quantity = intval($_POST['quantity']);
    $description = htmlspecialchars(trim($_POST['description']));
    $total_price = floatval($_POST['total_price']); // Assuming this is sent from the client-side

    // Validate the total price based on quantity and some unit price
    // Assuming you have a function to get the unit price of the product
    $unit_price = getUnitPrice($product_name, $product_size); // You'll need to define this function
    $calculated_total_price = $unit_price * $quantity;

    // Check if the total price is correct
    if ($total_price !== $calculated_total_price) {
        echo "Total price does not match. Please check your calculations.";
        exit;
    }

    // Here, you could insert the purchase information into a database
    // For example:
    // $conn = new mysqli($servername, $username, $password, $dbname);
    // $stmt = $conn->prepare("INSERT INTO purchases (customer_name, product_name, product_size, quantity, total_price, description) VALUES (?, ?, ?, ?, ?, ?)");
    // $stmt->bind_param("sssiis", $customer_name, $product_name, $product_size, $quantity, $total_price, $description);
    // $stmt->execute();
    // $stmt->close();
    // $conn->close();

    // Provide feedback to the user
    echo "Thank you, $customer_name! Your purchase of $quantity x $product_name (Size: $product_size) for a total of $$total_price has been confirmed.";
}

// Function to get the unit price of a product (example implementation)
function getUnitPrice($product_name, $product_size) {
    // This function would typically look up the price in a database based on the product name and size
    // For demonstration purposes, let's return a fixed price
    return 20.00; // Example unit price
}
?>
<script>
    const unitPrice = 20.00; // Replace with the actual unit price from your system

    document.getElementById('quantity').addEventListener('input', function() {
        const quantity = this.value;
        const totalPriceField = document.getElementById('total_price');
        const totalPrice = (unitPrice * quantity).toFixed(2);
        totalPriceField.value = totalPrice;
        document.getElementById('hidden_product_name').value = document.getElementById('purchaseProductName').innerText;
        document.getElementById('hidden_product_size').value = document.getElementById('purchaseProductSize').innerText;
    });
</script>
