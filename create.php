<?php
// Insert default admin user into the database
include 'db.php';

$username = 'gwapo123';
$customer_name = 'gwapo123';
$password = password_hash('gwapo123', PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO customers (username, password, customer_name) VALUES (?, ?, ?)");
$stmt->execute([$username, $password, $customer_name]);

echo "Admin user created successfully.";
?>
