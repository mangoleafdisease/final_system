<?php
// Start a session only if it isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../home.php");
    exit();
}

// Regenerate session ID for security
session_regenerate_id(true);

// Database connection
include_once '../db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Could not connect to the database. Please try again later.");
}

// Fetch the logged-in user's name
if (isset($_SESSION['all_username'])) {
    $stmt = $pdo->prepare("SELECT username, cashier_name FROM cashier WHERE username = :username"); 
    $stmt->bindParam(':username', $_SESSION['all_username'], PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $username = $user['cashier_name']; 
        $profilePic = $user['profile_pic'] ?? '../img/default-profile.png';
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/navbar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* User Profile Image */
.userpic {
   width: 50px;
   height: 50px;
   border-radius: 50%;
   background-color: white;
   overflow: hidden; /* Ensures the image stays within the circular shape */
   display: flex;
   align-items: center;
   justify-content: center;
   border: 2px solid #fff; /* Adds a white border around the image */
   box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); /* Adds a subtle shadow for a 3D effect */
}

.userpic img {
   width: 100%; /* Ensures the image fills the circle */
   height: 100%;
   object-fit: cover; /* Maintains the aspect ratio of the image and covers the circle */
}

        </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="../img/logohome.png" alt="Logo">
            <h1>NORMI Business Center</h1>
        </div>
        <div class="username">
            <div class="user">
                <div class="userpic">
                <img src="../img/cashier.png" alt="User Picture">
                </div>
                <!-- Display the logged-in user's name -->
                <p aria-label="Logged-in Username"><?php echo htmlspecialchars($username); ?></p> 
                <i class="fa-solid fa-caret-down" style="color: #fff"></i>
            </div>
            <div class="logout" style="display: none;">
                <a href="logout.php">
                    <i class="fa-solid fa-power-off"></i> Log out
                </a>
            </div>
        </div>
    </nav>

    <script type="text/javascript">
        $(document).ready(function () {
            // Toggle the logout menu when the username section is clicked
            $('.username').on('click', function () {
                $(this).find('.logout').slideToggle();
            });

            // Close the logout menu if clicked outside
            $(document).on('click', function (event) {
                if (!$(event.target).closest('.username').length) {
                    $('.logout').slideUp();
                }
            });
        });
    </script>
</body>
</html>
