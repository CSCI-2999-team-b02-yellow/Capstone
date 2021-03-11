<?php
   
	$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
	// $connection info to the database.
	$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
	$conn = sqlsrv_connect( $serverName, $connectionInfo);
	
 // if condition when the Submit butoon is pushed. 
if(isset($_POST['submit'])){
	
	// function to display a message alert
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
		$selected1 = implode("', '", $_POST['product']);
		$selected2 = $_POST['update'];
		
		// Check wich part the user want to change, and use the SQL query to update it in the database.
		
		if($selected2 == "description"){
			
			$selected3 = $_POST['newUpdate'];
			
			sqlsrv_query( $conn, "UPDATE inventory SET itemDescription = '" .$selected3. "' WHERE productSKU in ('" .$selected1. "')");
								
		}elseif($selected2 == "price"){
			

			$selected3 = (float)$_POST['newUpdate'];
	
			sqlsrv_query( $conn, "UPDATE inventory SET price = '" .$selected3. "' WHERE productSKU in ('" .$selected1. "')");
						
		}elseif($selected2 == "sku"){
			
			$selected3 = $_POST['newUpdate'];
			sqlsrv_query( $conn, "UPDATE inventory SET productSKU = '" .$selected3. "' WHERE productSKU in ('" .$selected1. "')");
						
		}else{
			
			$selected3 = "Please select one.";
			
		}
		
	 // Function call when products are updated
	function_alert("Updated!");
	
	}
}
?>

<!DOCTYPE html>

<html lang="en">

    <head>

        <!-- documentation at http://getbootstrap.com/docs/4.1/, alternative themes at https://bootswatch.com/ -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">

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
	  <a href="employees.php">Employees</a>
      </div>
      
    </div>

	<br><br>

	
        <main class="container p-5">
		
<h2> Update Products </h2>

<input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search/Filter for a product.." title="Type in a Product Name"> <br>
			Please select the products to update<br><br>
			<form action="" method="POST">
			<ul id="myUL">
							
				<?php
				// using php in the <select> to show dynamically the product in the webpage.
				$sql = "SELECT * FROM inventory ORDER BY productName";

				$query = sqlsrv_query( $conn, $sql);
				if( $query === false ) {
					 die( print_r( sqlsrv_errors(), true));
				}
				// A loop function to display all the products in the database.
				while( $products = sqlsrv_fetch_array( $query, SQLSRV_FETCH_ASSOC) ) {
				
						$product=$products["productName"];?>
					 <li><a> 
						<input type="checkbox" name="product[]" value="<?php echo $products["productSKU"]; ?>">
						<label for=""> <?php echo $product. " " .$products["productSKU"];?> </label>
						</a></li>
						

		
				<?php
				}?>
								

			  <br>
			 
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
			<br>
	
        </main>

        <footer>
		<div>
            Service provided by YellowTeam 2021
		</div>
        </footer>

<script>
function myFunction() {
    var input, filter, ul, li, a, i, txtValue;
    input = document.getElementById("myInput");
    filter = input.value.toUpperCase();
    ul = document.getElementById("myUL");
    li = ul.getElementsByTagName("li");
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("a")[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}
</script>

    </body>

</html>
