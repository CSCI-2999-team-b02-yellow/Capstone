<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Contact Us</title>
  <meta name="author" content="Team Yellow">
  <meta name="description" content="Nuts and bolts hardware contact info">
  <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, contact, contact us">
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
		<a href="printCart.php">Shopping Cart</a>
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
            echo '<a href="logout.php">Logout</a>';
        }?>
    </div>
</div>

<div class="main">   
  <h1>Nuts and Bolts Hardware</h1>
  <h2>Contact Info</h2>
  <p>Address: 1234 Easy Street, Hardwareville FL 9001</p>
  <p>Phone number: 867-530-9999</p>
  <p> Email us at <a href="mailto:nuts_and_bolts_hardware@fake.web">nuts_and_bolts_hardware@fake.web</a>.</p>
</div>

</body>
</html>
