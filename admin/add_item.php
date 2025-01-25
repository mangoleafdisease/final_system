<?php
session_start();
if (!isset($_SESSION['all_logged_in']) || !$_SESSION['all_logged_in']) {
    header("Location: ../home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo_web_normi.png">
    <title>Business Center Add Item</title>
    <link rel="stylesheet" href="css/add_item.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Display alert if a message exists in URL parameters
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const message = urlParams.get('message');
            if (message) {
                alert(message);
            }
        };

 
        function previewImage(event) {
            const file = event.target.files[0];
            const previewContainer = document.getElementById('imagePreview');
            previewContainer.innerHTML = ''; 

            if (file) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imgElement = document.createElement('img');
                        imgElement.src = e.target.result;
                        imgElement.style.maxWidth = '100%';
                        imgElement.style.height = 'auto';
                        previewContainer.appendChild(imgElement);
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.textContent = 'The selected file is not an image.';
                }
            } else {
                previewContainer.textContent = 'No file selected.';
            }
        }

   
        function handleCancel() {
            document.getElementById('myForm').reset();
            document.getElementById('imagePreview').innerHTML = '';
        }

      
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById('myForm');
            form.onsubmit = function(event) {
                const isConfirmed = confirm("Are you sure you want to Add this Item?");
                if (!isConfirmed) {
                    event.preventDefault(); 
                }
            };
        });
    </script>
</head>
<body>
    <?php include_once('navbar.php'); ?>
    <?php include_once('sidebar.php'); ?>

    <div class="container">
        <div class="toplabel">
            <h1>New Item</h1>
            <p>Item List | Add Item</p>
        </div>
        <hr>

        <form id="myForm" action="add_item_process.php" method="POST" enctype="multipart/form-data">
            <div class="form-flex">
                <div class="form-group form-column-1">
                    <label for="productName">Product Name</label>
                    <input type="text" id="productName" name="name" placeholder="Enter product name" required>
                    
                    <label for="barcode">Barcode</label>
                    <input type="text" id="barcode" name="barcode" value="<?php echo 'BC' . rand(1000000, 9999999); ?>" readonly>
                    
                    <label for="itemCode">Item Code</label>
                    <input type="text" id="itemCode" name="item_code" value="<?php echo 'ITM' . uniqid(); ?>" readonly>

                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" required>
                </div>
                
                <div class="form-group form-column-2">
                    <label for="itemSize">Item Size</label>
                    <select id="itemSize" name="size" required>
                        <option value="XS">XS</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                    </select>
                    
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" required>
                    
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>

                    <label for="year_level">Year Level</label>
                    <select id="year_level" name="year_level" required>
                        <option value="">-- Select Year Level --</option>
                        <option value="First Year">First Year</option>
                        <option value="Second Year">Second Year</option>
                        <option value="Third Year">Third Year</option>
                        <option value="Fourth Year">Fourth Year</option>
                        <option value="Senior High">Senior High</option>
                        <option value="Junior High">Junior High</option>
                    </select>

                </div>
                
                <div class="form-group form-column-3">
                    <div class="form-group full-width">
                        <label>Image Preview</label>
                        <div id="imagePreview" class="image-preview"></div>
                    </div>
                    <label for="imageUpload">Product Image</label>
                    <div class="upload-area">
                        Choose a file or drag it here
                        <input type="file" accept="image/*" id="imageUpload" name="image" onchange="previewImage(event)">
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="submit-btn">Submit</button>
                <button type="button" class="cancel-btn" onclick="handleCancel()">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html>
