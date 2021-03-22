<?php
session_start();
?>

<!DOCTYPE html>
<!--Yellow Team
Jeremy Wellman
Kyle Snyder
Nicholas Seelbach
Rassim Yahioune
Thomas Rothwell
Tomas Kasparaitis
2/16/2021-->
<!--Contact us page-->
<html>

<head>
  <meta charset="utf-8">
  <title>Contact Us</title>
  <meta name="author" content="Team Yellow">
  <meta name="description" content="Nuts and bolts hardware contact info">
  <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, contact, contact us">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="css/indexstyle.css" rel="stylesheet">


</head>

<body>
<div class="header">
    <div class="links">
        <a class="active" href="index.php">Home</a>
        <a href="products.php">Products</a>
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
        <a href="login.php">Login</a>
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
  

  <script src="js/script.js"></script>
</body>

</html>
