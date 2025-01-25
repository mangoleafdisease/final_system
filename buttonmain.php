


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="css/global.css">
  <link rel="stylesheet" href="css/txt.css">
  <link rel="stylesheet" href="css/guibtn.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="css/buttonmain.css">
  
  <title>M.POS - Main Menu</title>
</head>
<body>
  <div class="main">
    <a href="inventory/add_items.php">
      <div class="gbtn cblue">
          <i class="fa-solid fa-warehouse fa-4x"></i>
        <h2>Inventory</h2>
        <p>
          Manage your remaining products & items here.
          Represent your inventory by tallying product quantities.
          Shows status of inventory like item quantity.
        </p>
      </div>
    </a>
  
    <a href="cashier/cashierlogin.php">
      <div class="gbtn cred">
        <i class="fa-solid fa-user fa-4x"></i>
        <h2>Cashier</h2> <p> Record products during sales. 
          Automatically update your inventory with each transaction. 
          This feature also provides basic calculations to facilitate the checkout process. </p>
      </div>
    </a>
  
    <a href="admin/adminlogin.php">
      <div class="gbtn cgreen">
      <i class="fa-solid fa-user-tie fa-4x"></i>
      <h2>Admin</h2> <p> Enhance your data presentation with graphical charts. 
          Gain valuable visual insights into your sales performance. </p>
      </div>
    </a>
  </div>
</body>
</html>