<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <?php
    if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
        echo "<script>alert('Logout successful!');</script>";
    }
    ?>
</head>
<body>
    <div class="container1">
        <div class="logo">
            <i class="fa-solid fa-cart-flatbed"></i>
            <h1>NORMI Bussiness Center</h1>
        </div>
        <nav>
            <a href="home.php" class="home">Home</a>
            <a href="#" class="login">Login</a>
        </nav>
    </div>

    <div class="container">
        <h1 class="signin">Sign In</h1>
        <hr>
        <!-- Display error messages -->
        <?php if (isset($_SESSION['login_error'])): ?>
            <script>
                alert("<?php echo htmlspecialchars($_SESSION['login_error']); ?>");
            </script>
            <?php unset($_SESSION['login_error']); // Clear the error message after displaying it ?>
        <?php endif; ?>

        <form action="log_process.php" method="POST">
            <div class="pass">
                <i class="fa-solid fa-user"></i>
                <input placeholder="Username" class="pass2" type="text" id="username" name="username" required>
            </div>
            <div class="pass">
                <i class="fa-solid fa-lock" style="color: #ffffff;"></i>
                <input placeholder="Password" class="pass2" type="password" id="password" name="password" required>
                <i id="eyeicon" class="fa-solid fa-eye"></i>
            </div>
            <button type="submit">Sign in</button>
            <a href="#" class="back">Forgot Password?</a>
        </form>
    </div>
    
    <script>
        let eyeicon = document.getElementById("eyeicon");
        let password = document.getElementById("password");

        eyeicon.onclick = function() {
            // Toggle password visibility
            if (password.type === "password") {
                password.type = "text";
                eyeicon.classList.remove("fa-eye");
                eyeicon.classList.add("fa-eye-slash");
            } else {
                password.type = "password";
                eyeicon.classList.remove("fa-eye-slash");
                eyeicon.classList.add("fa-eye");
            }
        }
    </script>

</body>
</html>
