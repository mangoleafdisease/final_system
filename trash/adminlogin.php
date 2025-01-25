<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/adminlogin.css"> 
    <?php
        if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
            echo "<script>alert('Logout successful!');</script>";
        }
        ?>  


</head>
<body>
    <div class="container">
        <h1>Admin Login</h1>

        <!-- Display error messages -->
        <?php if (isset($_SESSION['login_error'])): ?>
            <script>
                alert("<?php echo htmlspecialchars($_SESSION['login_error']); ?>");
            </script>
            <?php unset($_SESSION['login_error']); // Clear the error message after displaying it ?>
        <?php endif; ?>

        <form action="log_process.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
            <a href="../buttonmain.php">Back</a>
        </form>
    </div>
</body>
</html>
