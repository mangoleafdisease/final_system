<?php
session_start();
session_destroy(); // Destroy all session data


// Redirect to the login page with a success message in the URL
header("Location: ../home.php?logout=success");
exit();
?>