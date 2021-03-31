<?php

session_start();

	$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
	// $connection info to the database.
	$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
	$conn = sqlsrv_connect( $serverName, $connectionInfo);

if(!isset($_SESSION["username"])){
    header("location: login.php");
} else {
    if ($_SESSION["accesslevel"] < 2) {
        header("location: login.php");
    }
}

// TODO: if no checkbox is selected and you for example have price 25.58, and click update, you are logged out
// TODO: figure out what is breaking, also why you become logged out during this process....

if(isset($_POST['filterSubmit'])) {
    // implode is PHP-version of split, where it splits the strings from the selection array
    // for example it would look something like this: 13 42 27 18
    // those numbers represent the itemIDs split into the $selection string after using implode on the posted array
    $selection = implode("', '", $_POST['selection']);

    // the array introduced here allows us to bypass a tricky situation, which is namely that:
    // prepared statements allow us to use placeholders for values, but not columns
    // for example this is valid: "UPDATE inventory SET price WHERE itemID in ?" price is the name of the column
    // however this is not valid: "UPDATE inventory SET ? WHERE itemID in ?" because the first placeholder refers to a column
    // since our column names are derived from an associate array where they are they key they don't need sanitized (it's server side)
    // if this were not the case, we could introduce sql injection into a prepared statement by a logical loophole!
    $columnValues = array();
    $columnValues['productSKU'] = $_POST['sku'];
    $columnValues['itemDescription'] = $_POST['description'];
    $columnValues['price'] = $_POST['price'];
    $columnValues['stock'] = $_POST['stock'];

    // https://stackoverflow.com/questions/33205087/sql-update-where-in-list-or-update-each-individually
    // foreach ($arrayName as $key => $value) https://www.w3schools.com/php/php_arrays_associative.asp
    // since we are using a modular approach we can now use a single update statement:
    $count = 0;
    foreach ($columnValues as $column => $userInput) {
        if(!$userInput === "") {
            /* If input isn't empty, run the update statement */
            $sql = "UPDATE inventory SET ".$column." = ? WHERE itemID in ?";
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
    <h3> Update Products </h3>
    <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search/Filter for a product.." title="Type in a Product Name"> <br>

    <h4>Please select the products to update</h4><br><br>
    <form action="" method="POST">
        <div>
            <input type="text" id="searchBar" placeholder="Search for products" />
            <button name="searchSubmit" style="margin-left:2%;" class="btn btn-dark">Search</button>
        </div>
        <ul id="rawList" class="noBulletPoints" onkeyup="filterResults()">
            <?php
            // TODO: put an ifisset check on searchSubmit button, introduce one also make a searchbox, one search is run generate this:
            // die( print_r( sqlsrv_errors(), true)); --> this probably needs to go to an error log when re-implemented
            // we want to first let the user search for results, and then let them narrow them down by filtering even more:
            if(isset($_POST['searchSubmit'])) {
                $searchInput = $_POST['searchBar'];
                // I'm choosing to not allow search by the number left in stock, but we can introduce this if needed
                $sql = "SELECT * 
                        FROM yellowteam.dbo.inventory 
                        WHERE ? 
                        IN (productName, productSKU, itemDescription, price)
                        ORDER BY productName";
                $stmt = sqlsrv_prepare($conn, $sql, array($searchInput));
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
                }
            }?>
            <br>
				<details open>
				  <summary>
					Price
					<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
					<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
				  </summary>
					<input type='text' name='price' id='price' placeholder="Update Price" />
				</details>
				<br>
				<details>
				  <summary>
					Product SKU
					<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
					<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
				  </summary>
				    <input type='text' name='sku' id='sku' placeholder="Update SKU" />
				</details>
				<br>
				<details>
				  <summary>
					Description
					<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
					<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
				  </summary>
				    <input type='text' name='description' id='description' placeholder="Update Description" />
				</details>
                <br>
                <details>
                    <summary>
                        Stock
                        <svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
                        <svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
                    </summary>
                    <input type='text' name='stock' id='stock' placeholder="Update Stock" />
                </details>
				
            <br><br>
            <button name="filterSubmit" class="btn btn-dark">Update</button>
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
