<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        .Register {
            text-align: center;
            background-color:  #ffffff;
            color: #1f582d;
            border: none;
            font-size: 13px;
            border-radius: 5px;
            padding: 2px 7px;
            font-weight:600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .Register:hover {
            background-color: #127e2d;
            color: #f0f4ff;
        }
    </style>

    <!-- SweetAlert2 Library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php
    if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
        echo "<script>Swal.fire('Success!', 'Logout successful!', 'success');</script>";
    }
    ?>

</head>
<body>
    <div class="containerhome">
        <div class="logo">
            <img src="img/logohome.png" alt="">
            <h1>NORMI Bussiness Center</h1>
        </div>

        <!-- login -->
        <div class="containerlogin">
            <!-- Display login success message -->
            <?php if (isset($_SESSION['login_success'])): ?>
    <script>
        Swal.fire({
            title: 'Success!',
            text: '<?php echo $_SESSION['login_success']; ?>',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
               
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    window.location.href = "admin/dashboard.php";
                <?php elseif ($_SESSION['user_role'] === 'cashier'): ?>
                    window.location.href = "cashier/customer_orders_list.php";
                <?php elseif ($_SESSION['user_role'] === 'customer'): ?>
                    window.location.href = "customer/all_items.php";
                <?php endif; ?>
            }
        });
    </script>
    <?php unset($_SESSION['login_success']);  ?>
<?php endif; ?>


            <!-- Display error messages -->
            <?php if (isset($_SESSION['login_error'])): ?>
                <script>
                    Swal.fire({
                        title: 'Error!',
                        text: '<?php echo $_SESSION['login_error']; ?>',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                </script>
                <?php unset($_SESSION['login_error']);  ?>
            <?php endif; ?>

            <form action="log_process.php" method="POST">
                <div class="pass">
                    <i class="fa-solid fa-user fa-lg" style="color: #ffffff;"></i>
                    <input placeholder="Username" class="pass2" type="text" id="username" name="username" required>
                </div>
                <div class="passs">
                    <i class="fa-solid fa-lock fa-lg" style="color: #ffffff;" ></i>
                    <input placeholder="Password" class="pass2" type="password" id="password" name="password" required>
                </div>
                <div class="eyee">
                    <i id="eyeicon" class="fa-regular fa-eye fa-sm" style="color: #ffffff;"></i>
                </div>

                <button type="submit">Log In</button>
                <a href="register.php" class="Register">Register</a>
                <a href="#" class="back">Forgot Password?</a>
            </form>
        </div>
    </div>

    <div class="containerhome1">
        <img src="img/logohome.png" alt="">
        <h1>Northern Mindanao Colleges Inc SY 2024-2025</h1>
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
