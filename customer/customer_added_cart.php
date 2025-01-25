<?php  
session_start();

// Check if the user is logged in and is a customer
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in first.']);
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

// Fetch items from the customer's cart
$cartItems = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            customer_cart.id, 
            customer_cart.item_name, 
            customer_cart.size, 
            customer_cart.quantity, 
            customer_cart.price, 
            customer_cart.added_date, 
            inventory.image 
        FROM 
            customer_cart 
        INNER JOIN 
            inventory 
        ON 
            customer_cart.item_id = inventory.id 
        WHERE 
            customer_cart.customer_id = :customer_id
    ");
    $stmt->execute([':customer_id' => $_SESSION['id']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching cart items: " . $e->getMessage());
}

// Group items by added_date
$groupedItems = [];
foreach ($cartItems as $item) {
    $groupedItems[$item['added_date']][] = $item;
}

// Sort grouped items by date in descending order
krsort($groupedItems);

// Check if there are items in the cart
$isCartEmpty = empty($cartItems);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer's Cart</title>
    <link rel="stylesheet" href="css/customer_added_cart.css">
    <style>
        .product-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 10px;
            border-radius: 5px;
            vertical-align: middle;
        }
        .delete-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .delete-button:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>

<?php include_once('navbar.php'); ?>
<?php include_once('sidebar.php'); ?>

<div class="container">
    <h1>Your Cart</h1>

    <?php if ($isCartEmpty): ?>
    <p class="empty-cart-message">Your cart is empty. Please add items before proceeding.</p>
<?php else: ?>
    <form id="checkout-form" method="POST">
        <?php foreach ($groupedItems as $date => $items): ?>
            <div class="group-header">Date Added: <?php echo htmlspecialchars($date); ?></div>
            <table>
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" data-group="group-<?php echo htmlspecialchars($date); ?>"> Select Group
                        </th>
                        <th>Item</th>
                        <th>Size</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="group-<?php echo htmlspecialchars($date); ?>">
                    <?php foreach ($items as $item): 
                        $imagePath = '../uploads/' . htmlspecialchars($item['image'] ?? 'default.jpg');
                    ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_items[]" value="<?php echo htmlspecialchars($item['id']); ?>" class="select-item" data-group="group-<?php echo htmlspecialchars($date); ?>">
                            </td>
                            <td>
                                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" class="product-thumbnail" onerror="this.src='uploads/default.jpg';">
                                <?php echo htmlspecialchars($item['item_name']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['size']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                            <td>₱<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                            <td>
                                <button type="button" class="delete-button" data-id="<?php echo htmlspecialchars($item['id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>

        <div class="select-all-container">
            <input type="checkbox" name="select_all"> Select All
        </div>
        <div class="checkoutbottom">
            <button type="button" class="checkout-button" id="checkout-button">Proceed to Checkout</button>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
   <script>
    document.getElementById('checkout-button').addEventListener('click', function() {
        Swal.fire({
            title: 'Confirm Checkout',
            text: 'Are you sure you want to proceed to checkout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
               
                const formData = new FormData(document.getElementById('checkout-form'));

                
                $.ajax({
                    url: 'checkout_process.php', 
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        
                        Swal.fire({
                            title: 'Checkout Successful',
                            text: response.message || 'Your checkout has been completed!',
                            icon: 'success',
                            timer: 2000, 
                            showConfirmButton: false,
                        }).then(() => {
                            
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                      
                        Swal.fire({
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'An error occurred during checkout. Please try again.',
                            icon: 'error',
                        });
                    }
                });
            } else {
                Swal.fire('Cancelled', 'Your checkout process was canceled.', 'info');
            }
        });
    });
</script>

<?php endif; ?>


</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const selectAllCheckbox = document.querySelector("input[name='select_all']");
        const groupCheckboxes = document.querySelectorAll("input[type='checkbox'][data-group]");
        const itemCheckboxes = document.querySelectorAll(".select-item");
        const deleteButtons = document.querySelectorAll('.delete-button');
        
        selectAllCheckbox.addEventListener("change", function () {
            const isChecked = selectAllCheckbox.checked;
            itemCheckboxes.forEach(checkbox => checkbox.checked = isChecked);
            groupCheckboxes.forEach(groupCheckbox => groupCheckbox.checked = isChecked);
        });

        groupCheckboxes.forEach(groupCheckbox => {
            groupCheckbox.addEventListener("change", function () {
                const groupName = groupCheckbox.dataset.group;
                const groupItems = document.querySelectorAll(`.select-item[data-group="${groupName}"]`);
                groupItems.forEach(itemCheckbox => itemCheckbox.checked = groupCheckbox.checked);
                updateSelectAllState();
            });
        });

        itemCheckboxes.forEach(itemCheckbox => {
            itemCheckbox.addEventListener("change", function () {
                const groupName = itemCheckbox.dataset.group;
                const groupItems = document.querySelectorAll(`.select-item[data-group="${groupName}"]`);
                const groupCheckbox = document.querySelector(`input[type='checkbox'][data-group="${groupName}"]`);
                groupCheckbox.checked = Array.from(groupItems).every(checkbox => checkbox.checked);
                updateSelectAllState();
            });
        });

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const itemId = this.dataset.id;
                if (confirm("Are you sure you want to delete this item?")) {
                    fetch('customer_delete_added_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: itemId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            window.location.reload(); 
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An unexpected error occurred.');
                    });
                }
            });
        });

        function updateSelectAllState() {
            const allChecked = Array.from(itemCheckboxes).every(checkbox => checkbox.checked);
            const anyChecked = Array.from(itemCheckboxes).some(checkbox => checkbox.checked);
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = !allChecked && anyChecked;
        }
    });
</script>

</body>
</html>
