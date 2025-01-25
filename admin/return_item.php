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
    <link rel="shortcut icon" href="../img/logo web normi.png">
    <title>Business Center Add Stock</title>
    <link rel="stylesheet" href="css/return_item.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        /* Styles for the suggestions dropdown */
        #item_codeSuggestions {
            border: 1px solid #ccc;
            display: none;
            max-height: 150px;
            overflow-y: auto;
            position: absolute;
            background-color: white;
            z-index: 1000;
            width: 100%;
            box-sizing: border-box;
        }

        .suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
        }

        .suggestion-item:hover {
            background-color: #f0f0f0;
        }

        /* Positioning the suggestions container */
        .form-group {
            position: relative; /* To position suggestions relative to the input */
        }
    </style>

    <script>
        function handleCancel() {
            document.getElementById('myForm').reset();
            document.getElementById('item_codeSuggestions').style.display = 'none';
            document.getElementById('errorMessage').textContent = '';
        }

        async function fetchItemDetails() {
            const itemCode = document.getElementById('item_code').value.trim();
            const errorMessage = document.getElementById('errorMessage');
            errorMessage.textContent = ''; // Clear any previous error message

            if (!itemCode) {
                // Clear item details if input is empty
                clearItemDetails();
                return;
            }

            console.log("Fetching details for item code:", itemCode);

            try {
                const response = await fetch('fetch_item_to_return.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ item_code: itemCode })
                });

                if (!response.ok) throw new Error("Network response was not ok");

                const data = await response.json();
                if (data.success) {
                    document.getElementById('name').value = data.item.name;
                    document.getElementById('size').value = data.item.size;
                    document.getElementById('year_level').value = data.item.year_level;
                    errorMessage.textContent = ''; // Clear the error message if item is found
                } else {
                    clearItemDetails();
                    errorMessage.textContent = data.message || 'Item not found';
                }
            } catch (error) {
                console.error('Error fetching item details:', error);
                errorMessage.textContent = 'Failed to fetch item details. Please try again.';
            }
        }

        async function fetchItemCodes(query) {
            if (!query) {
                document.getElementById('item_codeSuggestions').style.display = 'none';
                return;
            }

            try {
                const response = await fetch('fetch_item_codes.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query: query })
                });

                if (!response.ok) throw new Error("Network response was not ok");

                const data = await response.json();
                if (data.success && data.item_codes.length > 0) {
                    showSuggestions(data.item_codes);
                } else {
                    document.getElementById('item_codeSuggestions').style.display = 'none';
                }
            } catch (error) {
                console.error('Error fetching item codes:', error);
                document.getElementById('item_codeSuggestions').style.display = 'none';
            }
        }

        function showSuggestions(itemCodes) {
            const suggestionsDiv = document.getElementById('item_codeSuggestions');
            suggestionsDiv.innerHTML = ''; // Clear previous suggestions

            itemCodes.forEach(code => {
                const suggestion = document.createElement('div');
                suggestion.classList.add('suggestion-item');
                suggestion.textContent = code;
                suggestion.onclick = () => selectSuggestion(code);
                suggestionsDiv.appendChild(suggestion);
            });

            suggestionsDiv.style.display = 'block';
        }

        function selectSuggestion(code) {
            document.getElementById('item_code').value = code;
            document.getElementById('item_codeSuggestions').style.display = 'none';
            fetchItemDetails();
        }

        function clearItemDetails() {
            document.getElementById('name').value = '';
            document.getElementById('size').value = '';
            document.getElementById('year_level').value = '';
        }

        // Debounce function to limit the rate of fetchItemCodes calls
        function debounce(func, delay) {
            let debounceTimer;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => func.apply(context, args), delay);
            }
        }

        // Attach event listener with debounce to item_code input for fetching suggestions
        document.addEventListener('DOMContentLoaded', () => {
            const itemCodeInput = document.getElementById('item_code');
            itemCodeInput.addEventListener('input', debounce(function() {
                const query = this.value.trim();
                fetchItemCodes(query);
            }, 300)); // Adjust debounce delay as needed
        });

        // Close suggestions when clicking outside
        document.addEventListener('click', function(event) {
            const suggestions = document.getElementById('item_codeSuggestions');
            const itemCodeInput = document.getElementById('item_code');
            if (!suggestions.contains(event.target) && event.target !== itemCodeInput) {
                suggestions.style.display = 'none';
            }
        });
    </script>
    
        <?php if (isset($_SESSION['return_success'])): ?>
            <script>
                alert("<?php echo htmlspecialchars($_SESSION['return_success']); ?>");
            </script>
            <?php unset($_SESSION['return_success']); ?>
        <?php endif; ?>

</head>
<body>
    <?php include_once('navbar.php'); ?>
    <?php include_once('sidebar.php'); ?>

    <div class="container">
        <div class="toplabel">
            <h1>Return Item</h1>
            <p>Transaction | return</p>
        </div>

        <hr>

        <form id="myForm" action="return_item_process.php" method="POST">
            <div class="form-flex">
                <div class="form-group form-column-1">
                    <label for="customer_name">Customer Name:</label>
                    <input type="text" name="customer_name" id="customer_name" required>

                    <label for="item_code">Item Code:</label>
                    <input type="text" id="item_code" name="item_code" required>
                    <div id="item_codeSuggestions"></div>

                    <!-- Error message container for item code -->
                    <div id="errorMessage" style="color: red; font-size: 0.9em; margin-top: 5px;"></div>

                    <label for="name">Item Name:</label>
                    <input type="text" id="name" name="name" required readonly>
                </div>
                
                <div class="form-group form-column-2">
                    <label for="size">Size:</label>
                    <input type="text" id="size" name="size" required readonly>

                    <label for="year_level">Year Level:</label>
                    <input type="text" id="year_level" name="year_level" required readonly>

                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">Purchase</button>
                <button type="button" class="cancel-btn" onclick="handleCancel()">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html>
