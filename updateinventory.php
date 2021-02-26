<?php

/* 
 * $_POST is an array that has key value pairs, for example if you have a textbox
 * with the name updateInv, you can use it to pass the values from the textbox
 */
if(isset($_POST['updateInv'])){

	// Since we are now working with Microsoft SQL server, which has a different PHP driver for connections
	// we may need to download them onto server from here so the code executes properly: 
	// https://docs.microsoft.com/en-us/sql/connect/php/microsoft-php-driver-for-sql-server?view=sql-server-ver15
	
	// $serverName is just the URL our database is hosted on:
	$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
	// $connection info is an array which can only take database, user & password:
	$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
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
	// Note: Update is NOT ALLOWED to change the productName according to the story requirements
	
	// placeholders (?) are used in SQL statements to prepare a statement & prevent SQL injection
	$sql = "UPDATE dbo.inventory
			SET productSKU = ?, itemDescription = ?, price = ?
			WHERE itemID = ?";
	// ** since productName & itemID are both unique, maybe productName is better to use for selecting search results from HTML
		
	//----------------------------------------------------------------------------------------------------------------  
	// This is where we will need to add something in introducing pulling user input from the HTML to fill
	// in the variables that are going into the $sql string. I think we just need to get some text fields created
	// and name in the HTML, which we then feed in somehow (I'll look into this more later). 
	//----------------------------------------------------------------------------------------------------------------  
	// Note: going to have to create a search that spits out rows of results also? 
	$productSKU = '';
	$itemDescription = '';
	$price = '';
	$itemID = '';
	
	// prepares our statement with connection info, all variables inside placeholders in sql:
	$stmt = sqlsrv_prepare( $conn, $sql, array( &$productSKU, &$itemDescription, &$price, &$itemID));
	
	// Checks that there's no issues with the statement, connection, parameters etc. once all pieced together:
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
  <title>Update Products</title>
  <meta name="author" content="Team Yellow">
  <meta name="description" content="Nuts and bolts hardware company search for/update products page">
  <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, products, search, update, update products, search products">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="css/updateinventorystyle.css" rel="stylesheet">
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
  <h3>Welcome to the nuts and bolts search for or update products page!</h3>
  
 
 
  <!--No idea how to search for stuff in the database. -jeremy-->
</form> 

  

  <script src="js/script.js"></script>
</body>

</html>
