<?php
session_start();
if (!isset($_SESSION['all_logged_in'])) {
    header("Location: home.php");  // Redirect to the login page
    exit();
}
?>
