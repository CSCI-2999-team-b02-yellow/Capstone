<?php

session_start();

$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
// $connection info to the database
$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
$conn = sqlsrv_connect( $serverName, $connectionInfo);

// What to do when the add to cart button is clicked:
if(isset($_POST["addToCart"])) {
    echo '<script>console.log("addToCart has been pressed")</script>';
    $itemID = $_POST["itemID"];
    echo '<script>console.log("itemID is: '.$itemID.'")</script>';
    $quantity = $_POST["quantity"];
    echo '<script>console.log("quantity is: '.$quantity.'")</script>';


    // TODO: If someone adds a product to a cart, check if a cookie exists with cookieID on the client computer.
    // Check if a cookie exists, which will also create one if we don't have one:
    $cookieID = checkCookie($conn);
    echo '<script>console.log("CookieID is: '.$cookieID.'")</script>';
    echo '<script>console.log("Started running addItem()")</script>';
    addItem($conn, $cookieID, $itemID, $quantity);
    echo '<script>console.log("Finished running addItem()")</script>';
}

function addItem($conn, $cookieID, $itemID, $quantity) {

    try {
        // get the orderID based on the cookieID from the cookie database table:
        $sql = "SELECT orderID FROM yellowteam.dbo.cookie WHERE cookieID = ?";
        $stmt = sqlsrv_prepare($conn, $sql, array($cookieID), array( "Scrollable" => "buffered"));
        sqlsrv_execute($stmt);
        $orderID = null;
        while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
            $orderID = $row['orderID'];
        }
        echo '<script>console.log("orderID is: '.$orderID.'")</script>';

        // check if the item already exists in the database, insert into if not, update if so
        $sql = "SELECT quantity FROM yellowteam.dbo.orders WHERE orderID = ? AND itemID = ?";
        $stmt = sqlsrv_prepare($conn, $sql, array($orderID, $itemID, $quantity), array( "Scrollable" => "buffered"));
        sqlsrv_execute($stmt);
        if(sqlsrv_num_rows($stmt) === 1) {
            while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
                $quantity += $row['quantity']; // we grab quality so we can reuse for update statement
            }
            $sql = ""; // need to write update set statement
        } else {
            $sql = "INSERT INTO yellowteam.dbo.orders (orderID, itemID, quantity) VALUES (?, ?, ?)";
        }

        // TODO: Add product to orders table using itemID, orderID (pulled from cookie table), and quantity:
        // add the item based on the order ID into the database orders table:
        $stmt = sqlsrv_prepare($conn, $sql, array($orderID, $itemID, $quantity), array( "Scrollable" => "buffered"));
        sqlsrv_execute($stmt);
        echo '<script>console.log("Added item ID '.$itemID.' to orderID '.$orderID.' with quantity '.$quantity.'")</script>';
    } catch (exception $e) {
        // Need to look up and introduce error handling logic here:
    } finally {
        //sqlsrv_free_stmt($stmt);
        //sqlsrv_close($conn);
    }
}

function checkCookie($conn) {
    $cookieID = null;
    if(!isset($_COOKIE['cookieID'])) {
        // TODO: If a cookie does not exist create a cookieID locally.
        $cookieID = genCookieID();
        setcookie('cookieID', $cookieID, time() + (86400 * 30), "/"); // 30-day expiration: 86400 is the seconds in a day
        echo '<script>console.log("New Cookie ID created: '.$cookieID.'")</script>';

        // if user is logged in, we get their username:
        $username = null;
        if(isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
        }

        // TODO: This may have some merging issues later, would probably have to check if a user has a cookie tied to their username already?
        // TODO: Guessing it would get solved on log off and log back in. But that's a crappy workaround.
        // TODO: Store cookieID & orderID(auto-generated PK) in the database “cookie” table. Add username if user is logged in.
        try {
            $sql = "INSERT INTO yellowteam.dbo.cookie (cookieID, username) VALUES (?, ?)";
            $stmt = sqlsrv_prepare($conn, $sql, array($cookieID, $username), array( "Scrollable" => "buffered"));
            sqlsrv_execute($stmt);
        } catch (exception $e) {
            // Need to look up and introduce error handling logic here:
        } finally {
            //sqlsrv_free_stmt($stmt);
            //sqlsrv_close($conn);
        }
    } else {
        $cookieID = $_COOKIE['cookieID']; // you can't get cookie data right after creating, only when you go to another page!!
    }
    return $cookieID;
}

// This function generates a pseudo-random 50-character alphanumeric string, our db stores 50 varchar cookieIDs
function genCookieID() {
    return bin2hex(random_bytes(25)); // Note: in hex a byte is 2 characters, so 25 hexes are actually 50 characters here
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
<div><h3><b> Products <b></h3></div><br>
<table class="table">
  <thead class="thead-dark">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Product Name</th>
	  <th scope="col">SKU</th>
      <th scope="col">Description</th>
      <th scope="col">Price</th>
	  <th scope="col">Quantity</th>
    </tr>
  </thead>
  <tbody>
	<?php

	$sqlTwo = "SELECT * FROM inventory";

	$query = sqlsrv_query($conn, $sqlTwo);
	if( $query === false ) {
		 die( print_r( sqlsrv_errors(), true));
	}
	// A loop function to display all the products in the database.
	$number=0;
	while( $products = sqlsrv_fetch_array( $query, SQLSRV_FETCH_ASSOC) ) {  
		$number=$number+1;
		$itemID=$products["itemID"];
		$product=$products["productName"];
		$sku=$products["productSKU"];
		$description=$products["itemDescription"];
		$price=round($products["price"],2);
	
		?>
		<tr>
		  <th scope="row"><?php echo $number; ?></th>
		  <td><?php echo $product; ?></td>
		  <td><?php echo $sku; ?></td>
		  <td><?php echo $description; ?></td>
		  <td><?php echo '$'.$price; ?></td>
		  <td><form action="" method="POST">
            <input type="text" name="quantity" value="1" class="form-control" />
            <input type="hidden" name="itemID" value="<?php echo $itemID; ?>" />
			<button type="submit" name="addToCart" class="btn btn-dark">+ Add to Cart</button>
          </form></td>
		</tr>
    <?php
	}?>
  </tbody>
</table>
</main>

</body>
</html>
