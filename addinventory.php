<?php

/* 
 * $_POST is an array that has key value pairs, for example if you have a textbox
 * with the name addInv, you can use it to pass the values from the textbox
 */
if(isset($_POST['addInv'])){

	// Since we are now working with Microsoft SQL server, which has a different PHP driver for connections
	// we may need to download them onto server from here so the code executes properly: 
	// https://docs.microsoft.com/en-us/sql/connect/php/microsoft-php-driver-for-sql-server?view=sql-server-ver15
	
	// $serverName is just the URL our database is hosted on:
	$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
	// $connection info is an array which can only take database, user & password:
	$connectionInfo = array( "Database"=>"yellowteam", "UID"=>"admin", "PWD"=>"$LUbx6*xTY957b6");
	$conn = sqlsrv_connect( $serverName, $connectionInfo);

	// Connects to server, or spits out error log if it fails
	if( $conn ) {
		 echo "Connection established.\n";
	}else{
		 echo "Connection could not be established.\n";
		 die( print_r( sqlsrv_errors(), true));
	}
	
	//------------------------------------------- 
	// itemID is (PK, int, not null)
	// productName is (varchar(60), null)
	// productSKU is (varchar(10), null)
	// itemDescription is (varchar(5000), null)
	// price is (smallmoney, null)
	//-------------------------------------------
	// placeholders (?) are used in SQL statements to prepare a statement & prevent SQL injection
	$sql = "INSERT INTO tableName (productName, productSKU, itemDescription, price)
			VALUES (?, ?, ?, ?)";
		
	//----------------------------------------------------------------------------------------------------------------  
	// Using $_POST['addInv'] to trigger form, and then using $_POST['name'] to pull user input into variables.
	//----------------------------------------------------------------------------------------------------------------  
	$productName = $_POST['p-name'];
	$productSKU = $_POST['p-sku'];
	$itemDescription = $_POST['p-desc'];
	$price = $_POST['p-price'];
	
	// prepares our statement with connection info, all variables inside placeholders in sql:
	$stmt = sqlsrv_prepare( $conn, $sql, array(&$productName, &$productSKU, &$itemDescription, &$price));
	
	// checks the statement for errors, sqlsrv_prepare returns false if there's an error:
	if( $stmt )  
	{  
		 echo "Statement prepared.\n";  
	}  
	else  
	{  
		 echo "Error in preparing statement.\n";  
		 die( print_r( sqlsrv_errors(), true));  
	} 
	
	// This actually uses the statement on the database, prints out errors if something happens:
	if( sqlsrv_execute( $stmt))  
	{  
		  echo "Statement executed.\n";  
	}  
	else  
	{  
		 echo "Error in executing statement.\n";  
		 die( print_r( sqlsrv_errors(), true));  
	}  
	
	// Closes the connection and also releases statement resources:
	sqlsrv_free_stmt( $stmt);  
	sqlsrv_close( $conn); 

}
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
  <title>Add Products</title>
  <meta name="author" content="Team Yellow">
  <meta name="description" content="Nuts and bolts hardware company add products page">
  <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, products, add, add products">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="css/addinventorystyle.css" rel="stylesheet">
</head>

<body>
<div class="header">
      <div class="links">
      <a class="active" href="index.html">Home</a>
      <a href="contactus.html">Contact Us</a>
      <a href="aboutus.html">FAQ</a>
	  <a href="products.html">Products</a>
      <a href="addinventory.php">Add Inventory</a>
	  <a href="updateinventory.php">Update Products</a>
      </div>
      <div class ="logo">
      <img src="inverselogo.png" alt="Italian Trulli" style="width:4.5%;height:4.5%;">
    </div>
    </div>
  
  <h1>Nuts and Bolts Hardware</h1>
  <h3>Welcome to the nuts and bolts add products page!</h3>
  

  <!-- edited action="" since we don't have a PHP file to forward to, added method="POST" -Tomas -->
  <form action="" method="POST">
  <label for="p-name">Product Name:</label><br>
  <input type="text" id="p-name" name="p-name" value=""><br>
  <label for="p-sku">Product SKU:</label><br>
  <input type="text" id="p-sku" name="p-sku" value=""><br><br>
    <label for="p-desc">Product Description:</label><br>
  <input type="text" id="p-desc" name="p-desc" value=""><br>
    <label for="p-price">Product Price:</label><br>
  <input type="text" id="p-price" name="p-price" value=""><br>
  <input type="submit" name="addInv" value="Submit">
  <!--i don't know how to make it so all this info goes into the database, but would like to learn -jeremy-->
</form> 

  

  <script src="js/script.js"></script>
</body>

</html>
