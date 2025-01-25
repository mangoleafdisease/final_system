<?php
session_start();
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}

include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $item_code = $_POST['item_code'] ?? '';
    $name = $_POST['name'] ?? '';
    $barcode = $_POST['barcode'] ?? '';
    $size = $_POST['size'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $year_level = $_POST['year_level'] ?? '';
    $image = $_FILES['image'] ?? null;

    $imagePath = null;

    // Validate and upload the image file
    if ($image && $image['error'] == 0) {
        $fileName = basename($image['name']);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $allowedTypes = ['jpeg', 'png', 'gif', 'jpg'];

        if (in_array(strtolower($ext), $allowedTypes)) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . $fileName;
            $imagePath = $uploadDir . $fileName;

            if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
                header("Location: add_item.php?message=Error: Failed to upload image");
                exit();
            }
        } else {
            header("Location: add_item.php?message=Error: Invalid image type");
            exit();
        }
    } else {
        header("Location: add_item.php?message=Error: No image uploaded or upload error");
        exit();
    }

    // Check if barcode already exists
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE barcode = ?");
    $stmt->execute([$barcode]);
    $existing_product = $stmt->fetch();

    if ($existing_product) {
        header("Location: add_item.php?message=Error: Barcode already exists");
        exit();
    } else {
        // Insert new product into the inventory table
        $stmt = $conn->prepare("INSERT INTO inventory (item_code, name, barcode, size, quantity, price, year_level, image, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$item_code, $name, $barcode, $size, $quantity, $price, $year_level, $fileName])) {
            header("Location: add_item.php?message=Product $name added successfully");
            exit();
        } else {
            header("Location: add_item.php?message=Error: Failed to add product");
            exit();
        }
    }
}
