<?php

session_start();

	$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
	// $connection info to the database.
	$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
	$conn = sqlsrv_connect( $serverName, $connectionInfo);
	
 // if statement when the Submit button is pushed.

if(!isset($_SESSION["username"])){
    header("location: login.php");
} else {
    if ($_SESSION["accesslevel"] < 2) {
        header("location: login.php");
    }
}

if(isset($_POST['submit'])){
	
	// function to display a message alert
	function function_alert($message) { 
      
		// Display the alert box  
		echo "<script>alert('$message');</script>"; 
	}
	
	// Condition to check if there is no data given.
	if ($_POST['price']=="" and $_POST['sku']=="" and $_POST['description']==""){
		
		// Function call 
		function_alert("Missing Information. Please fulfill everything.");
	
	// Other conditions to update the database.	
	}else{
		$selected1 = implode("', '", $_POST['product']); // use of implode for a list of words
		$price = $_POST['price'];
		$sku = $_POST['sku'];
		$description = $_POST['description'];
		
		// Check wich part the user want to change, and use the SQL query to update it in the database.
		
		if(!$description== ""){
					
			sqlsrv_query( $conn, "UPDATE inventory SET itemDescription = '" .$description. "' WHERE productSKU in ('" .$selected1. "')");
								
		}
			
		if(!$price == ""){
			
			sqlsrv_query( $conn, "UPDATE inventory SET price = '" .$price. "' WHERE productSKU in ('" .$selected1. "')");
						
		}
		
		if(!$sku == ""){
			
			sqlsrv_query( $conn, "UPDATE inventory SET productSKU = '" .$sku. "' WHERE productSKU in ('" .$selected1. "')");
						
		}
		else{
			
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
    <meta charset="UTF-8">
    <title>Update Products</title>
    <meta name="author" content="Team Yellow">
    <meta name="description" content="Nuts and bolts hardware company update products page">
    <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, update products, update inventory, update">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- documentation at http://getbootstrap.com/docs/4.1/, alternative themes at https://bootswatch.com/ -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet"> <!-- also makes page responsive -->
    <link href="css/main.css" rel="stylesheet">
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
            echo '<a href="logout.php">Logout</a>';
        }?>
    </div>
</div>

<main class="container p-5">
    <h2> Update Products </h2>
    <br><br>
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
                $product=$products["productName"];
                ?>
                <li><a>
                        <input type="checkbox" name="product[]" value="<?php echo $products["productSKU"]; ?>">
                        <label for=""> <?php echo $product." ".$products["productSKU"]."  $".round($products["price"],2);?> </label>
                    </a></li>
                <?php
            }?>
            <br>
				<details open>
				  <summary>
					Price
					<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
					<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
				  </summary>
					<input type='text' name='price' id='price' placeholder="The New Update">
				</details>
				<br>
				<details>
				  <summary>
					Product SKU
					<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
					<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
				  </summary>
				    <input type='text' name='sku' id='sku' placeholder="The New Update">
				</details>
				<br>
				<details>
				  <summary>
					Description
					<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
					<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
				  </summary>
				    <input type='text' name='description' id='description' placeholder="The New Update">
				</details>
				
            <br><br>
            <button name="submit" class="btn btn-dark">Update</button>
    </form>
    <br>
</main>

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
