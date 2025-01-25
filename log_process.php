<?php
session_start();
include 'db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure that both username and password are provided
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $_SESSION['login_error'] = "Username and password are required.";
        header("Location: home.php");
        exit();
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare queries for each role
    $queries = [
        'admin' => "SELECT * FROM admins WHERE username = ?",
        'cashier' => "SELECT * FROM cashier WHERE username = ?",
        'customer' => "SELECT * FROM customers WHERE username = ?"
    ];

    // Loop through each role and check if the user exists
    foreach ($queries as $role => $query) {
        $stmt = $conn->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // If a user is found and the password matches
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['all_logged_in'] = true;
            $_SESSION['all_username'] = $user['username'];
            $_SESSION['user_role'] = $role; // Store the user's role

            // Store additional user information if needed
            if ($role === 'customer') {
                $_SESSION['id'] = $user['id']; // Store the customer ID for use in other pages
                $_SESSION['customer_name'] = $user['customer_name']; // Store customer name for personalized content
            } elseif ($role === 'cashier') {
                $_SESSION['id'] = $user['id']; // Store the cashier ID for use in other pages
                $_SESSION['cashier_name'] = $user['cashier_name']; // Store cashier name for personalized content
            } elseif ($role === 'admin') {
                $_SESSION['id'] = $user['id']; // Store the admin ID for use in other pages
                $_SESSION['admin_name'] = $user['admin_name']; // Store admin name for personalized content
            }

            // Set success message with user ID
            $_SESSION['login_success'] = "Login successful! Welcome, " . $user['username'] . ".";

            // Redirect to home page for SweetAlert
            header("Location: home.php");
            exit();
        }
    }

    // If no match was found, login fails
    $_SESSION['login_error'] = "Invalid username or password.";
    header("Location: home.php");
    exit();
}
?>
