<?php

session_start();

$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if(!isset($_SESSION["username"])){
    header("location: login.php");
} else {
    if ($_SESSION["accesslevel"] < 2) {
        header("location: login.php");
    }
}

if(isset($_POST['addInv'])){

	$sql = "INSERT INTO yellowteam.dbo.inventory (productName, productSKU, itemDescription, price, stock)
			VALUES (?, ?, ?, ?, ?)";

	$productName = $_POST['p-name'];
	$productSKU = $_POST['p-sku'];
	$itemDescription = $_POST['p-desc'];
	$price = $_POST['p-price'];
    $stock = $_POST['p-stock'];
	
	// prepares our statement with connection info, all variables inside placeholders in sql:
	$stmt = sqlsrv_prepare( $conn, $sql, array($productName, $productSKU, $itemDescription, $price, $stock));
    sqlsrv_execute($stmt);
	sqlsrv_free_stmt( $stmt);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Add Products</title>
  <meta name="author" content="Team Yellow">
  <meta name="description" content="Nuts and bolts hardware company add products page">
  <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, products, add, add products">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- documentation at http://getbootstrap.com/docs/4.1/, alternative themes at https://bootswatch.com/ -->
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet"> <!-- also makes page responsive -->
  <link href="css/index.css" rel="stylesheet">
  <!-- Generated using favicon.io, provides logo icon on browser tab; need these 4 lines to function: -->
  <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
  <link rel="manifest" href="img/site.webmanifest">
</head>

<body>
<div class="header">
    <div class="links">
         <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="cart.php">Cart</a>
        <?php if(isset($_SESSION["accesslevel"])) {
            if ($_SESSION["accesslevel"] > 1) {
                echo '<a href="addinventory.php">Add Inventory</a>';
            }
        }?>
        <?php if(isset($_SESSION["accesslevel"])) {
            if ($_SESSION["accesslevel"] > 1) {
                echo '<a href="updateinventory.php">Update Products</a>';
            }
        }?>
        <a href="contactus.php">Contact Us</a>
        <a href="aboutus.php">FAQ</a>
        <?php if(isset($_SESSION["accesslevel"])) {
            if ($_SESSION["accesslevel"] > 1) {
                echo '<a href="employees.php">Employees</a>';
            }
        }?>
        <?php if(!isset($_SESSION["username"])) {
            echo '<a href="login.php">Login</a>';
        }?>
        <?php if(isset($_SESSION["username"])) {
            echo '<a href="history.php">Order History</a>';
        }?>
        <?php if(isset($_SESSION["accesslevel"])) {
            if ($_SESSION["accesslevel"] > 1) {
                echo '<a href="weeklysales.php">Weekly Sales</a>';
            }
        }?>
        <?php if(isset($_SESSION["username"])) {
            echo '<a href="logout.php">Logout</a>';
        }?>
    </div>
</div>

<div class="main">
  <h3>Add Products</h3>
  <form action="" method="POST">
      <label for="p-name">Product Name:</label><br>
      <input type="text" id="p-name" name="p-name" value=""><br>
      <label for="p-sku">Product SKU:</label><br>
      <input type="text" id="p-sku" name="p-sku" value=""><br>
      <label for="p-desc">Product Description:</label><br>
      <input type="text" id="p-desc" name="p-desc" value=""><br>
      <label for="p-price">Product Price:</label><br>
      <input type="text" id="p-stock" name="p-stock" value=""><br>
      <label for="p-stock">Product Stock:</label><br>
      <input type="text" id="p-price" name="p-price" value=""><br><br>
      <input type="submit" name="addInv" value="Submit"><br><br>
  </form>
</div>

</body>
</html>
