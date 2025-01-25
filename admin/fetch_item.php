<?php
session_start();
include '../db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Input sanitization
    $barcode = htmlspecialchars(trim($_POST['barcode']));

    // Fetch item details by barcode, including the image_url
    $stmt = $conn->prepare("SELECT item_code, name, size, price, image FROM inventory WHERE barcode = ?");
    $stmt->execute([$barcode]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        echo json_encode(['success' => true, 'item' => $item]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found!']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request!']);
}





