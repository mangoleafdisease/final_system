<?php
session_start();


if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in first.']);
    header("Location: ../home.php");
    exit();
}

include '../db.php';

try {
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all items from the inventory
    $stmt = $pdo->prepare("SELECT * FROM inventory");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

  
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       
        $itemId = $_POST['item_id'];
        $newPrice = $_POST['new_price'];

       
        if (empty($itemId) || empty($newPrice) || !is_numeric($newPrice)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input. Please provide a valid item ID and price.']);
            exit();
        }

        
        $sql = "UPDATE inventory SET old_price = price, price = :new_price, price_updated_at = NOW() WHERE id = :item_id";
        $stmt = $pdo->prepare($sql);

        
        $stmt->bindParam(':new_price', $newPrice, PDO::PARAM_STR);
        $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);

        
        if ($stmt->execute()) {
            
            $stmt = $pdo->prepare("SELECT name FROM inventory WHERE id = :item_id");
            $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'message' => 'Successfully updated the price for item: ' . htmlspecialchars($item['name'])]);
            exit(); 
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update the price. Please try again.']);
            exit();
        }
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    exit();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Item Price</title>
    <link rel="stylesheet" href="css/edit_price.css">
    <style>
        .success-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: #4CAF50;
            color: white;
            font-size: 18px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
        }

        .success-message.show {
            display: block;
        }
    </style>
</head>
<body>

<?php include_once('navbar.php'); ?>
<?php include_once('sidebar.php'); ?>
<div class="container">
    <div class="toplabel">
        <h1>Edit Price</h1>
        <p>| update item price</p>
    </div>
    <hr>

    <!-- Success Message -->
    <div class="success-message" id="successMessage"></div>

    <!-- Modal for editing item price -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <form method="post" action="" id="priceForm">
                <label for="item_id">Item ID:</label>
                <input type="text" id="item_id" name="item_id" required readonly><br><br>

                <label for="current_price">Current Price:</label>
                <input type="text" id="current_price" name="current_price" readonly><br><br>

                <label for="new_price">New Price:</label>
                <input type="text" id="new_price" name="new_price" required><br><br>

                <button type="button" id="updateButton">Update Price</button>
            </form>
        </div>
    </div>

    <h2>All Items</h2>
    <div class="white_container">
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Item Name</th>
                    <th>Old Price</th>
                    <th>Price</th>
                    <th>Year Level</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr data-item-id="<?php echo $item['id']; ?>">
                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['old_price']); ?></td>
                        <td data-item-id="<?php echo htmlspecialchars($item['id']); ?>"><?php echo htmlspecialchars($item['price']); ?></td>
                        <td><?php echo htmlspecialchars($item['year_level']); ?></td>
                        <td>
                            <button type="button" onclick="editItem(<?php echo $item['id']; ?>, '<?php echo addslashes($item['price']); ?>')">Edit</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>    

<script>
    var modal = document.getElementById("myModal");
    var span = document.getElementsByClassName("close")[0];
    var updateButton = document.getElementById("updateButton");

    
    function editItem(itemId, itemPrice) {
        document.getElementById('item_id').value = itemId;
        document.getElementById('current_price').value = itemPrice;
        document.getElementById('new_price').value = '';
        modal.style.display = "block";
    }

  
    span.onclick = function() {
        modal.style.display = "none";
    }


    updateButton.onclick = function() {
        var form = document.getElementById('priceForm');
        var formData = new FormData(form);

        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);
        xhr.onload = function() {
            var response = JSON.parse(xhr.responseText);
            var successMessage = document.getElementById("successMessage");
            if (response.status === 'success') {
                successMessage.textContent = response.message;
                successMessage.classList.add("show");

                
                setTimeout(function() {
                    successMessage.classList.remove("show"); 
                    location.reload(); 
                }, 3000);
                modal.style.display = "none"; 
            } else {
                alert(response.message); 
            }
        };
        xhr.send(formData);
    }

    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>
