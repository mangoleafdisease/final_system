<?php

include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

 
    if (empty($role) || empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    try {
  
        $tableMap = [
            'Admin' => 'admins',
            'Cashier' => 'cashier',
            'Customer' => 'customers',
        ];

        if (!array_key_exists($role, $tableMap)) {
            echo json_encode(['success' => false, 'message' => 'Invalid role selected.']);
            exit;
        }

        $table = $tableMap[$role];

      
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE username = :username");
        $checkStmt->bindParam(':username', $username, PDO::PARAM_STR);
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            echo json_encode(['success' => false, 'message' => 'Username already exists in the selected role.']);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO $table (username, password) VALUES (:username, :password)");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => "$role account created successfully."]);
        } else {
            echo json_encode(['success' => false, 'message' => "Failed to create $role account."]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
