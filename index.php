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
<html>
<head>
  <meta charset="utf-8">
  <title>Homepage</title>
  <meta name="author" content="Team Yellow">
  <meta name="description" content="Nuts and bolts hardware company homepage">
  <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, home, homepage">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="css/indexstyle.css" rel="stylesheet">

<style>
.header {
  background-color: black;
  overflow: hidden;
  position: fixed; 
  top: 0; 
  width: 100%;
}

.header a {
  float: left;
  color: #f2f2f2;
  text-align: center;
  padding: 2% 1%;
  text-decoration: none;
  font-size: 17px;
  font-family: "Lucida Console", "Courier New", monospace;
}

.header a:hover {
  background-color: #ddd;
  color: black;
}

.header a.active {
  background-color: white;
  color: black;
}
.logo {
  margin: 0px auto;
  position: relative;
  right:-69%;
  
}
body {
    background-color: #B6B6B6;
    background-image: url('logo.png');
    background-repeat:no-repeat;
    background-attachment: fixed;
    background-position: 50% 50%;
    background-size: 45%; 
  }
.main {
  border: 5px dotted lightgray;
  background-color: black;    
  text-align: center;
  display: inline-block;
  padding-right: 20px;
  padding-left: 20px; 
  position: absolute; 
  left: 21%; 
  top: 35%; 
}
.main h1 {
  color: lightgray;
  font-size: 60px;
  font-family: "Lucida Console", "Courier New", monospace;
}
.main h3{
  color: lightgray;
  font-size: 30px;
  font-family: "Lucida Console", "Courier New", monospace;
}

</style>

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
        <?php if(!isset($_SESSION["username"])) {
            echo '<a href="login.php">Login</a>';
        }?>
        <?php if(isset($_SESSION["username"])) {
            echo '<a href="logout.php">Logout</a>';
        }?>
    </div>
</div>

<div class='main'>
  <h1>Nuts and Bolts Hardware</h1>
  <h3>Welcome to the nuts and bolts homepage!</h3>
</div>
  <script src="js/script.js"></script>
</body>

</html>
