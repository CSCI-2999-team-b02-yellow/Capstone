<?php

	$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
	// $connection info to the database.
	$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
	$conn = sqlsrv_connect( $serverName, $connectionInfo);

// Introducing separate search functionality, which would generate a table inside of the HTML
if(isset($_POST['searchPress'])) {
	$searchOption = $_POST['searchOptions'];
	$sql = "SELECT * FROM yellowteam.dbo.inventory WHERE ".$searchOption." = ?";
	$userInput = $_POST['searchInput'];
	
	// sanitizing $searchOption (vulnerable radiobutton value) which can only be 4 strict strings:
	if ($searchOption == 'productName' ||
		$searchOption == 'productSKU' ||
		$searchOption == 'itemDescription' ||
		$searchOption == 'price') 
		{
		// prepares our statement with connection info, all variables inside placeholders in sql:
		$stmt = sqlsrv_prepare( $conn, $sql, array(&$userInput));
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
	
	echo "<script>alert('breakpoint reached!')</script>";
	// Starting table headers, which need to be outside the loop to not be repeated each iteration:
	/*echo "<table><tr>
				 <th>Product Name</th>
				 <th>Product SKU</th>
				 <th>Item Description></th>
				 <th>Price</th>
				 </tr>" */
				 
	// could make <tr name or id = SQL TABLE ID>, for easy grabbing for update functionality later?
	// $("#searchResults").html(""); or alternatively $("#searchResults").empty; should clear search results
	// now we need to load the results, generate a loop and have it echo off tables in the HTML here:
	/* <script>
	 * $(document).ready(function () {
	 *    $("#searchResults").append();
	 * });
	 * </script>
	 */
	 
	// TEST: NOT WORKING? to see jquery actually works: \ should escape ' characters
	echo '<script>$(document.body).append(\'hello\');</script>';
	
	// TEST VERSION: working -- prints to top of screen
		while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
		echo 
			 $row['productName'].", "
			 .$row['productSKU'].", "
			 .$row['itemDescription'].", "
			 .$row['price'].'<br>;';
	}
	
	/* DISABLED: not working trying to figure out syntax
	/* while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
		echo "<script>".
			 '$(document).ready(function () {'.
			 '$("#searchResults").append('.
			 '"<p>"'.$row['productName'].", "
			 .$row['productSKU'].", "
			 .$row['itemDescription'].", "
			 .$row['price'].'</p><br>"'.
			 ');'.
			 '});'.
			 "</script>";
	} */

	// Closes the connection and also releases statement resources:
	sqlsrv_free_stmt( $stmt);  
	sqlsrv_close( $conn); 
	}
	// No need to use an else statement as default is do nothing
}

// This will be restricted to updates to the database
if(isset($_POST['submit'])){
	
	function function_alert($message) { 
      
		// Display the alert box  
		echo "<script>alert('$message');</script>"; 
	}
	
	// Condition to check if there is no data given.
	if ($_POST['product'] == "" or $_POST['update']=="" or $_POST['newUpdate']==""){
		
		// Function call 
		function_alert("Missing Information. Please fulfill everything.");
	
	// Other conditions to update the database.	
	}else{
		// Will need to introduce a way to grab 
		$product = $_POST['product'];
		$selected2 = $_POST['update'];
		
		// Check wich part the user want to change, and use the SQL query to update it in the database.
		
		// TODO: implement prepared statement, load value from $_POST[update] into it
		//------------------------------------------- 
		// itemID is (PK, int, not null)
		// productName is (varchar(60), null)
		// productSKU is (varchar(10), null)
		// itemDescription is (varchar(5000), null)
		// price is (smallmoney, null)
		//-------------------------------------------
		// placeholders (?) are used in SQL statements to prepare a statement & prevent SQL injection
		// apparently you cannot bind columns to parameters in prepared statements, so we have to introduce a function to sanitize the radio buttons
		$sql = "UPDATE yellowteam.dbo.inventory SET ? VALUES (?)";
				
	}
}
?>

<!DOCTYPE html>

<html lang="en">

<head>

	<!-- documentation at http://getbootstrap.com/docs/4.1/, alternative themes at https://bootswatch.com/ -->
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">

	<link href="css/indexstyle.css" rel="stylesheet">

	<!-- jQuery from folder: -->
	<script src='js/jquery-3.5.1.min.js'></script>

	<title>Update Products</title>

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
<br><br>

<main class="container p-5">
	<div><b> Update Products <b></div><br>
	<form action="" method="POST">
      <fieldset>
       <p style="font-size:14px">Search Options:
		   <input type = "radio"
                 name = "searchOptions"
                 value = "productName"
				 id = "option4"
                 checked = "checked" />
          <label for = "option1">Product Name</label>
          <input type = "radio"
                 name = "searchOptions"
                 value = "productSKU"
				 id = "option1"
                 checked = "checked" />
          <label for = "option1">Product SKU (10 characters)</label>
          <input type = "radio"
                 name = "searchOptions"
                 value = "itemDescription" 
				 id = "option2" />
          <label for = "option2">Item Description</label>
          <input type = "radio"
                 name = "searchOptions"
                 id = "option3"
                 value = "price" />
          <label for = "option3">Price</label>
		  <br><br>
		  <input type="text" id="searchBox" name="searchInput" value=""><br>
		  <br>
		  <button name="searchPress" class="btn btn-dark">Search</button>
        </p>       
      </fieldset>     
    </form>
	<!-- Going to try to use jQuery to add dynamic result table inside this div later: -->
	<div id="searchResults"></div>
	<br></br>

</main>

	<footer>
	<div>
		Service provided by YellowTeam 2021
	</div>
	</footer>

</body>

</html>
