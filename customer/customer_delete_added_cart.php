<?php
session_start();
if (!isset($_SESSION['all_logged_in']) || $_SESSION['user_role'] !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

include '../db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$itemId = $data['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM customer_cart WHERE id = :id AND customer_id = :customer_id");
    $stmt->execute([':id' => $itemId, ':customer_id' => $_SESSION['id']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Item deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found or not yours.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete item.']);
}
?>
