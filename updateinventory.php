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

if(isset($_POST['submit'])) {
    // the array introduced here allows us to bypass a tricky situation, which is namely that:
    // prepared statements allow us to use placeholders for values, but not columns
    // for example this is valid: "UPDATE inventory SET price WHERE itemID in ?" price is the name of the column
    // however this is not valid: "UPDATE inventory SET ? WHERE itemID in ?" because the first placeholder refers to a column
    // since our column names are derived from an associate array where they are they key they don't need sanitized (it's server side)
    // if this were not the case, we could introduce sql injection into a prepared statement by a logical loophole!
    $columnValues = array();
    //$columnValues['productSKU'] = isset($_POST['sku']) ? $_POST['sku'] : null;
    //$columnValues['itemDescription'] = isset($_POST['description']) ? $_POST['description'] : null;
    //$columnValues['price'] = isset($_POST['price']) ? $_POST['price'] : null;
    $columnValues['productSKU'] = $_POST['sku'];
    $columnValues['itemDescription'] = $_POST['description'];
    $columnValues['price'] = $_POST['price'];
    // $columnValues['stock'] = $_POST['stock']; TODO: needs stock implemented later

    // https://stackoverflow.com/questions/33205087/sql-update-where-in-list-or-update-each-individually
    // foreach ($arrayName as $key => $value) https://www.w3schools.com/php/php_arrays_associative.asp
    // since we are using a modular approach we can now use a single update statement:
    $count = 0;
    foreach ($columnValues as $column => $userInput) {
        if(!$userInput == "") { // TODO: frustrating php data types why does strict === not work, only ==
            $selection = implode("', '", $_POST['selection']); // $_POST['selection'] comes from name="selection[]" in li input
            echo "<script>console.log('selection is: $selection column is: $column userInput is: $userInput')</script>";
            $sql = "UPDATE yellowteam.dbo.inventory SET ".$column." = ? WHERE itemID in (?)";
            $stmt = sqlsrv_prepare($conn, $sql, array($userInput, $selection));
            sqlsrv_execute($stmt);
            $count++;
        }
    }
    // this is called a ternary operator, it helps us keep code smaller following this logic:
    // condition to be tested ? do this if true : do this if false;
    $count > 0 ? displayAlert("Updated!") : displayAlert("At least one update field must be filled out.");
}

// helper function to display a javascript alert messages:
function displayAlert($message) {
    echo "<script>alert('$message');</script>";
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
    <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search/Filter for a product.." title="Type in a Product Name"> <br>
    Please select the products to update<br><br>
    <form action="" method="POST">
        <ul id="myUL">
            <?php
            // I'm choosing to not allow search by the number left in stock, but we can introduce this if needed
            $sql = "SELECT * 
                    FROM yellowteam.dbo.inventory 
                    ORDER BY productName";
            $stmt = sqlsrv_prepare($conn, $sql);
            sqlsrv_execute($stmt);
            while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
                // name="selection[]" explanation: https://stackoverflow.com/questions/4688880/html-element-array-name-something-or-name-something
                // this stores the itemID values for checkmarked boxes in a list(array) called selection[]
                // this list of itemIDs is later used in an UPDATE SET WHERE IN statement, where a single field is updated for every itemID
                ?>
            <li><a>
                    <input type="checkbox" name="selection[]" value="<?php echo $row["itemID"]; ?>" />
                    <label for="">
                        <?php echo
                            $row["productName"]." "
                            .$row["productSKU"]."  $"
                            .round($row["price"],2)." "
                            .$row["itemDescription"]." In Stock: "
                            .$row["stock"];
                        ?>
                    </label>
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
