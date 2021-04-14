<?php

session_start();

// Currently, Guests don't see their history, so redirect if not logged in:
if(!isset($_SESSION["username"])) {
    header("location: login.php");
}

$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
// $connection info to the database
$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
$conn = sqlsrv_connect( $serverName, $connectionInfo);

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
            echo '<a href="history.php">Order History</a>';
        }?>
		 <?php if(isset($_SESSION["accesslevel"])) {
            if ($_SESSION["accesslevel"] > 1) {
                echo '<a href="weeklysales.php">Weekly Sales</a>';
            }
        <?php if(isset($_SESSION["username"])) {
            echo '<a href="logout.php">Logout</a>';
        }?>
    </div>
</div>

<main class="container p-5">
    <div><h3><b> Order History <b></h3></div><br>
    <table class="table">
        <thead class="thead-dark">
        <tr>
            <th scope="col">#</th>
            <th scope="col">Date</th>
            <th scope="col">Receipt #</th>
            <th scope="col">Total</th>
        </tr>
        </thead>

        <tbody>
        <?php
        try {
            // We are selecting the checkout date, receipt # (orderID), total (price * quantity added up for each row) -- will need to join on orderID

            // First we need to find the orderIDs associated with the username:
            $sql = "SELECT yellowteam.dbo.orderhistory.orderTimeStamp,
                    yellowteam.dbo.orderhistory.orderID
                    FROM yellowteam.dbo.orderhistory 
                    WHERE username = ?";
            $stmt = sqlsrv_prepare($conn, $sql, array($_SESSION["username"]), array( "Scrollable" => "buffered"));
            sqlsrv_execute($stmt);

            $receipts = array();
            // We can store the orderIDs inside of an array, if there's at least 1 result (orderID):
            if (sqlsrv_num_rows($stmt) > 0) {
                while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
                    $receipts[] = $row["orderID"];
                }
            }

            $sql = "SELECT yellowteam.dbo.orderhistory.orderTimeStamp,
            yellowteam.dbo.orderhistory.orderID,
            yellowteam.dbo.orders.quantity * yellowteam.dbo.inventory.price AS price 
            FROM yellowteam.dbo.orders
            LEFT JOIN yellowteam.dbo.orderhistory
            ON yellowteam.dbo.orderhistory.orderID=yellowteam.dbo.orders.orderID
            LEFT JOIN yellowteam.dbo.inventory
            ON yellowteam.dbo.inventory.itemID=yellowteam.dbo.orders.itemID
            WHERE yellowteam.dbo.orders.orderID = ?";
            $count = 1;
            foreach ($receipts as $receiptRecord => $receiptID) {
                $stmt = sqlsrv_prepare($conn, $sql, array($receiptID));
                sqlsrv_execute($stmt); ?>
                <tr><?php echo $count ?></tr>
                <?php
                while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) { ?>
                    <tr><?php echo $row["orderTimeStamp"]?></tr>
                    <tr><?php echo $row["orderID"]?></tr>
                    <tr><?php echo $row["price"]?></tr>
                <?php }
                $count++;
                // TODO: throw in while loop spitting all of these out in a table with timestamp being clickable
            }
        } catch (exception $e) {
            echo $e->getmessage();
        } finally {
            sqlsrv_free_stmt($stmt);
        }
        ?>


        </tbody>
    </table>
</main>

</body>
</html>
