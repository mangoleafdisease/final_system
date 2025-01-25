<?php
session_start();
include 'db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate input fields
    $customer_name = trim($_POST['customer_name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if any field is empty
    if (empty($customer_name) || empty($username) || empty($password)) {
        $_SESSION['register_error'] = "All fields are required.";
        header("Location: register.php");
        exit();
    }

    // Check if username already exists
    $check_query = "SELECT * FROM customers WHERE username = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['register_error'] = "Username already exists.";
        header("Location: register.php");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new customer into the database
    $insert_query = "INSERT INTO customers (customer_name, username, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $result = $stmt->execute([$customer_name, $username, $hashed_password]);

    if ($result) {
        $_SESSION['register_success'] = "Registration successful. You can now log in.";
        header("Location: home.php");
        exit();
    } else {
        $_SESSION['register_error'] = "An error occurred. Please try again.";
        header("Location: register.php");
        exit();
    }
}

// Redirect to registration page if accessed directly
header("Location: register.php");
exit();
?>
