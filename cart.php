<?php

session_start();

$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
// $connection info to the database
$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
$conn = sqlsrv_connect( $serverName, $connectionInfo);

function getOrderID($conn, $cookieID) {
    try {
        // get the orderID based on the cookieID from the cookie database table:
        $sql = "SELECT orderID FROM yellowteam.dbo.cookie WHERE cookieID = ?";
        $stmt = sqlsrv_prepare($conn, $sql, array($cookieID), array( "Scrollable" => "buffered"));
        sqlsrv_execute($stmt);
        $orderID = null;
        while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
            $orderID = $row['orderID'];
        }
    } catch (exception $e) {
        // Need to look up and introduce error handling logic here:
    } finally {
        sqlsrv_free_stmt($stmt);
        //sqlsrv_close($conn);
    }
    return $orderID;
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
            <th scope="col">Quantity</th>
            <th scope="col">Price</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $cookieID = $_COOKIE['cookieID'];
        $orderID = getOrderID($conn, $cookieID);
        if ($orderID !== null) {
            $sql = "SELECT yellowteam.dbo.inventory.productName, 
                    yellowteam.dbo.inventory.productSKU, 
                    yellowteam.dbo.orders.quantity, 
                    yellowteam.dbo.orders.quantity * yellowteam.dbo.inventory.price AS price 
                    FROM yellowteam.dbo.orders 
                    LEFT JOIN yellowteam.dbo.inventory 
                    ON yellowteam.dbo.inventory.itemID=yellowteam.dbo.orders.itemID
                    WHERE yellowteam.dbo.orders.orderID = ?";
            $stmt = sqlsrv_prepare($conn, $sql, array($orderID), array( "Scrollable" => "buffered"));
            sqlsrv_execute($stmt);
            $count = 1;
            while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
            ?>
            <tr>
                <th scope="row"><?php echo $count; ?></th>
                <td><?php echo $row["productName"]; ?></td>
                <td><?php echo $row["productSKU"]; ?></td>
                <td><?php echo $row["quantity"]; ?></td>
                <td><?php echo '$'.round($row["price"], 2); ?></td>
            </tr>
            <?php
                $count++;
            }
        }?>
        </tbody>
    </table>
</main>

</body>
</html>