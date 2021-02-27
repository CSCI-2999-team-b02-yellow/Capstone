<?php

// checks if HTML element by the name of 'addInv' has been clicked
// Note: PHP supports try catch blocks, error handling should be later rewritten using this logic
if(isset($_POST['addInv'])){
	
	// SQLSRV are Microsoft developed drivers, PDO_SQLSRV or PDO are community developed drivers for MSSQL
	// $serverName is just the URL our database is hosted on:
	$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
	// $connection info is an array which can only take database, user & password:
	$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
	$conn = sqlsrv_connect( $serverName, $connectionInfo);

	// Connects to server, or spits out error log if it fails
	if( $conn ) {
		 echo '<script>console.log("Connection established.\n")</script>';
	}else{
		 echo '<script>console.log("Connection could not be established.\n");</script>';
		 /* Giving up on passing PHP arrays, which is what i think sqlsrv_errors() returns
		  * to javascript. Either syntax is off, or we need something installed for json_encode
		  * to work. Too much trouble for what it's worth for this sprint. We can just introduce
		  * a server-side error log for more details if needed later. Leaving old code for reference:
		  *
		  *	 $log = print_r( sqlsrv_errors(), true);
		  *	 echo '<script>
		  *	 var jArray = <?php echo json_encode($log); ?>; 
		  *	 for(var i=0; i<jArray.length; i++) {
		  *		 console.log(jArray[i]));
		  *	 }
		  *	 </script>';
		  */
	}
	
	//------------------------------------------- 
	// itemID is (PK, int, not null)
	// productName is (varchar(60), null)
	// productSKU is (varchar(10), null)
	// itemDescription is (varchar(5000), null)
	// price is (smallmoney, null)
	//-------------------------------------------
	// placeholders (?) are used in SQL statements to prepare a statement & prevent SQL injection
	$sql = "INSERT INTO yellowteam.dbo.inventory (productName, productSKU, itemDescription, price)
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
		 echo '<script>console.log("Statement prepared.\n")</script>';  
	}  
	else  
	{  
		 echo '<script>console.log("Error in preparing statement.\n")</script>';  
		 // $log = print_r( sqlsrv_errors(), true);  
	} 
	
	// This actually uses the statement on the database, prints out errors if something happens:
	if( sqlsrv_execute( $stmt))  
	{  
		  echo '<script>console.log("Statement executed.\n")</script>';  
	}  
	else  
	{  
		 echo '<script>console.log("Error in executing statement.\n")</script>';  
		 // $log = print_r( sqlsrv_errors(), true);   
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

  <link href="css/indexstyle.css" rel="stylesheet">
</head>

<body>
<div class="header">
      <div class="links">
      <a class="active" href="index.html">Home</a>
      <a href="products.html">Products</a>

      <a href="addinventory.php">Add Inventory</a>
	  <a href="updateinventory.php">Update Products</a>
	  <a href="contactus.html">Contact Us</a>	  
	  <a href="aboutus.html">FAQ</a>
	  

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
