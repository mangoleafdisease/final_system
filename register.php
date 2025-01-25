<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <div class="containerhome">
        <div class="logo">
            <img src="img/logohome.png" alt="">
            <h1>NORMI Business Center</h1>
        </div>

        <!-- Registration form -->
        <div class="containerlogin">
            <!-- Display error messages -->
            <?php if (isset($_SESSION['register_error'])): ?>
                <script>
                    alert("<?php echo htmlspecialchars($_SESSION['register_error']); ?>");
                </script>
                <?php unset($_SESSION['register_error']); ?>
            <?php endif; ?>

            <!-- Display success messages -->
            <?php if (isset($_SESSION['register_success'])): ?>
                <script>
                    alert("<?php echo htmlspecialchars($_SESSION['register_success']); ?>");
                </script>
                <?php unset($_SESSION['register_success']); ?>
            <?php endif; ?>

            <form action="process_register.php" method="POST">
                <div class="pass">
                    <i class="fa-solid fa-user fa-lg" style="color: #ffffff;"></i>
                    <input placeholder="Full Name" class="pass2" type="text" id="customer_name" name="customer_name" required>
                </div>
                <div class="pass">
                    <i class="fa-solid fa-user fa-lg" style="color: #ffffff;"></i>
                    <input placeholder="Username" class="pass2" type="text" id="username" name="username" required>
                </div>
                <div class="pass">
                    <i class="fa-solid fa-lock fa-lg" style="color: #ffffff;"></i>
                    <input placeholder="Password" class="pass2" type="password" id="password" name="password" required>
                </div>

                <button type="submit">Register</button>
                <a href="home.php" class="back">Back to Login</a>
            </form>
        </div>
    </div>

    <div class="containerhome1">
        <img src="img/logohome.png" alt="">
        <h1>Northern Mindanao Colleges Inc
        SY 2024-2025</h1>
    </div>
</body>
</html>
