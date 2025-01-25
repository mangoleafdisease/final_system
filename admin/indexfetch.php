<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM items";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="item">';
        echo '<img src="' . htmlspecialchars($row["image_url"]) . '" alt="' . htmlspecialchars($row["name"]) . '">';
        echo '<div class="item-info">';
        echo '<h3>' . htmlspecialchars($row["name"]) . '</h3>';
        echo '<p>' . htmlspecialchars($row["description"]) . '</p>';
        echo '<p class="price">$' . number_format($row["price"], 2) . '</p>';
        echo '</div>';
        echo '</div>';
    }
} else {
    echo "No items found.";
}

$conn->close();
?>
