<?php

	$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
	// $connection info to the database.
	$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
	$conn = sqlsrv_connect( $serverName, $connectionInfo);

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
		$selected1 = $_POST['product'];
		$selected2 = $_POST['update'];
		
		// Check wich part the user want to change, and use the SQL query to update it in the database.
		
		if($selected2 == "description"){
			
			$selected3 = $_POST['newUpdate'];		
			sqlsrv_query( $conn, "UPDATE inventory SET itemDescription = '" .$selected3. "' WHERE productName='" .$selected1. "'");
								
		}elseif($selected2 == "price"){
			
			$selected3 = (float)$_POST['newUpdate'];
			
			sqlsrv_query( $conn, "UPDATE inventory SET price = '" .$selected3. "' WHERE productName ='" .$selected1. "'");
						
		}elseif($selected2 == "sku"){
			
			$selected3 = $_POST['newUpdate'];
			sqlsrv_query( $conn, "UPDATE inventory SET productSKU = '" .$selected3. "' WHERE productName ='" .$selected1. "'");
						
		}else{
			
			$selected3 = "Please select one.";
			
		}
		
	 // Function call 
	function_alert("Updated!  ". $selected1 . ", " . $selected2 . ", to: " . $selected3);
	
	}
}
?>

<!DOCTYPE html>

<html lang="en">

    <head>

        <!-- documentation at http://getbootstrap.com/docs/4.1/, alternative themes at https://bootswatch.com/ -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">

        <link href="./css/styles.css" rel="stylesheet">
        <link href="css/indexstyle.css" rel="stylesheet">
        <title>Update Products</title>

    </head>

    <body>

	<div class="header">
      <div class="links">
      <a class="active" href="index.html">Home</a>
	  <a href="products.php">Products</a>
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
				
			<div>
				<b> Update Products <b>
			</div>
			<br>

        <main class="container p-5">
		


			<form action="" method="POST">
			  <select name='product'>
			  
				<option value="">Choose the product to update</option>
			  
				
				<?php
				// using php in the <select> to show dynamically the product in the webpage.
				$sql = "SELECT * FROM inventory";

				$query = sqlsrv_query( $conn, $sql);
				if( $query === false ) {
					 die( print_r( sqlsrv_errors(), true));
				}
				// A loop function to display all the products in the database.
				while( $products = sqlsrv_fetch_array( $query, SQLSRV_FETCH_ASSOC) ) {
				
						$product=$products["productName"];?>
						<option value="<?php echo $product; ?>"><?php
							echo $product;?>
						</option>
					
				<?php
				}?>
								
			  </select>
			  <br><br>
			 

			  <select name="update">
				<option value="">Choose what to change from the product</option>
				<option value="sku">Product SKU</option>
				<option value="price">Price</option>
				<option value="description">Description</option>
			  </select>
			  <br><br>
			  <input type='text' name='newUpdate' id='newUpdate' placeholder="The New Update">
			  <br><br>
			  <button name="submit" class="btn btn-dark">Update</button>
			</form>
			<br></br>
	
        </main>

        <footer>
		<div>
            Service provided by YellowTeam 2021
		</div>
        </footer>

    </body>

</html>
