<?php

session_start();

	$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
	// $connection info to the database
	$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
	$conn = sqlsrv_connect( $serverName, $connectionInfo);

?>
	
<!DOCTYPE html>

<html lang="en">

    <head>

        <!-- documentation at http://getbootstrap.com/docs/4.1/, alternative themes at https://bootswatch.com/ -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">

         <link href="css/indexstyle.css" rel="stylesheet">
        <title>Products</title>

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

	<br><br>


    <main class="container p-5">
			<div>
				<b> Products <b>
			</div>
			<br>

<table class="table">
  <thead class="thead-dark">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Product Name</th>
	  <th scope="col">SKU</th>
      <th scope="col">Description</th>
      <th scope="col">Price</th>
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
		  <td><?php echo $price; ?></td>
		</tr>
    <?php
	}?>
					
  </tbody>
</table>
</main>

</body>
</html>
