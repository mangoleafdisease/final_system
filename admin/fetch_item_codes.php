<?php
session_start();
include '../db.php';

header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['query'])) {
        $query = htmlspecialchars(trim($input['query']));
        
        try {
            // Initialize PDO
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Prepare and execute the statement with wildcard for LIKE
            $stmt = $pdo->prepare("SELECT item_code FROM inventory WHERE item_code LIKE :query LIMIT 10");
            $likeQuery = $query . '%';
            $stmt->bindParam(':query', $likeQuery, PDO::PARAM_STR);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Extract item codes
            $itemCodes = array_map(function($row) {
                return $row['item_code'];
            }, $results);
            
            echo json_encode(['success' => true, 'item_codes' => $itemCodes]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No query provided']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
