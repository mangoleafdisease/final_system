<?php
session_start();
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: ../login.php");  // Redirect to the login page
    exit();
}
?>
