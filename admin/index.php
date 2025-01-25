<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item List</title>
    <style>
        * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

body {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f0f4f8;
}

.item-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    width: 80%;
}

.item {
    background-color: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.item img {
    width: 100%;
    height: auto;
}

.item-info {
    padding: 16px;
}

.item-info h3 {
    color: #333;
    font-size: 18px;
    margin-bottom: 8px;
}

.item-info p {
    color: #666;
    font-size: 14px;
}

.item-info .price {
    font-weight: bold;
    color: #2d8ceb;
    font-size: 16px;
}

    </style>
</head>
<body>
    <div class="item-list">
        <?php
        include 'indexfetch.php';
        ?>
    </div>
</body>
</html>
