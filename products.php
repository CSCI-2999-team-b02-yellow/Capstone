<?php

session_start();

	$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
	// $connection info to the database
	$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
	$conn = sqlsrv_connect( $serverName, $connectionInfo);

function checkCookie() {
    // TODO: Store cookieID & orderID in the database “cookie” table.
    // TODO: Add product to orders table using itemID (pulled from inventory table), orderID (pulled from cookie), and quantity (pulled from front end user input).
    // TODO: To meet story requirements we have to be able to add more than 1 of the same item from this page
    // TODO: If cookieID exists (locally), add product to orders table using itemID (pulled from inventory table), orderID (pulled from cookie), and quantity (pulled from front end user input).
    // TODO: If someone adds a product to a cart, check if a cookie exists with cookieID on the client computer.
    // need to reverse logic, cookie is going to be set most of the time, so why check for a condition that's nearly always true, check if it's not true first
    if(isset($_COOKIE[$cookie_name])) {
        $localID = $_COOKIE[$cookie_value]; // does this assume that our site's cookie is where the value is being looked for? or does it need cookie name?
    } else {
        // TODO: Store cookieID in the cookie (locally).
        // TODO: If a cookie does not exist create a cookieID and an orderID.
        // Basically call cookieID generator, store it locally & store it again in database, orderID is PK and auto generated
        setcookie($cookie_name, genCookieID(), time() + (86400 * 30), "/"); // 30-day expiration: 86400 is the seconds in a day
    }
}

// This function generates a pseudo-random 50-character alphanumeric string, our db stores 50 varchar cookieIDs
function genCookieID() {
    return bin2hex(random_bytes(50));
}

?>
	
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Products</title>
    <meta name="author" content="Team Yellow">
    <meta name="description" content="Nuts and bolts hardware company products page">
    <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, products, inventory">
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
<div><h3><b> Products <b></h3></div><br>
<table class="table">
  <thead class="thead-dark">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Product Name</th>
	  <th scope="col">SKU</th>
      <th scope="col">Description</th>
      <th scope="col">Price</th>
	  <th scope="col"> </th>
    </tr>
  </thead>
  <tbody>
	<?php

	$sql = "SELECT * FROM inventory";

	$query = sqlsrv_query( $conn, $sql);
	if( $query === false ) {
		 die( print_r( sqlsrv_errors(), true));
	}
	// A loop function to display all the products in the database.
	$number=0;
	while( $products = sqlsrv_fetch_array( $query, SQLSRV_FETCH_ASSOC) ) {  
		$number=$number+1;
		$product=$products["productName"];
		$sku=$products["productSKU"];
		$discription=$products["itemDescription"];
		$price=round($products["price"],2);
	
		?>
		<tr>
		  <th scope="row"><?php echo $number; ?></th>
		  <td><?php echo $product; ?></td>
		  <td><?php echo $sku; ?></td>
		  <td><?php echo $discription; ?></td>
		  <td><?php echo '$'.$price; ?></td>
		  <td>
			<button type="button" class="btn btn-dark">+ Add to Cart</button>
		  </td>
		</tr>
    <?php
	}?>
  </tbody>
</table>
</main>

</body>
</html>
