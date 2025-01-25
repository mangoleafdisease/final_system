<?php
session_start();
include '../db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Input sanitization
    $barcode = htmlspecialchars(trim($_POST['barcode'])) . '%'; // Add a wildcard for partial matching

    // Fetch matching barcodes from the database
    $stmt = $conn->prepare("SELECT barcode FROM inventory WHERE barcode LIKE ? LIMIT 10");
    $stmt->execute([$barcode]);
    $barcodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($barcodes) {
        echo json_encode(['success' => true, 'barcodes' => $barcodes]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No matching barcodes found!']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request!']);
}

?>
