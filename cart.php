<?php

session_start();

$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
// $connection info to the database
$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
$conn = sqlsrv_connect( $serverName, $connectionInfo);

$cookieID = isset($_COOKIE['cookieID']) ? $_COOKIE['cookieID'] : null;
$orderID = isset($_COOKIE['cookieID']) ? getOrderID($conn, $cookieID) : null;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "Guest";

if(isset($_POST['clearCart'])) {
    clearCart($conn, $orderID);
}

if(isset($_POST["removeFromCart"])) {
    $itemID = isset($_POST["itemID"]) ? $_POST["itemID"] : null;
    $quantity = isset($_POST["quantity"]) ? $_POST["quantity"] : null;
    $currentQuantity = isset($_POST["currentQuantity"]) ? $_POST["currentQuantity"] : null;
    removeItem($conn, $orderID, $itemID, $currentQuantity, $quantity);
}

function removeItem($conn, $orderID, $itemID, $currentQuantity, $quantity) {
    echo '<script>console.log("orderID is :'.$orderID.'")</script>';
    echo '<script>console.log("itemID is :'.$itemID.'")</script>';
    echo '<script>console.log("currentQuantity is :'.$currentQuantity.'")</script>';
    echo '<script>console.log("quantity is :'.$quantity.'")</script>';
    if ($itemID !== null && $currentQuantity !== null && $quantity !== null) {
        try {
            // TODO: if item gets reduced to more than 0 use an update statement, else use a delete statement to remove row from database
            if ($currentQuantity - $quantity > 0) {
                $sql = "UPDATE yellowteam.dbo.orders SET quantity = ? WHERE orderID= ? AND itemID = ?";
                $stmt = sqlsrv_prepare($conn, $sql, array($currentQuantity - $quantity, $orderID, $itemID));
                sqlsrv_execute($stmt);
            } else { // we are removing all of the product from our cart
                $sql = "DELETE FROM yellowteam.dbo.orders WHERE orderID = ? AND itemID = ?";
                $stmt = sqlsrv_prepare($conn, $sql, array($orderID, $itemID));
                sqlsrv_execute($stmt);
            }
        } catch (exception $e) {
            // Need to look up and introduce error handling logic here:
        } finally {
            //sqlsrv_free_stmt($stmt);
            //sqlsrv_close($conn);
        }
    }
}

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
    }
    return $orderID;
}

function clearCart($conn, $orderID) {
    try {
        $sql = "DELETE FROM yellowteam.dbo.orders WHERE orderID = ?";
        $stmt = sqlsrv_prepare($conn, $sql, array($orderID));
        sqlsrv_execute($stmt);
    } catch (exception $e) {
        // Probably adjust to have this log to console?
        echo $e->getmessage();
    } finally {
        sqlsrv_free_stmt($stmt);
    }
}

$sql = "SELECT yellowteam.dbo.inventory.itemID, 
        yellowteam.dbo.inventory.stock,
        yellowteam.dbo.inventory.productName, 
        yellowteam.dbo.inventory.productSKU, 
        yellowteam.dbo.orders.quantity, 
        yellowteam.dbo.orders.quantity * yellowteam.dbo.inventory.price AS price 
        FROM yellowteam.dbo.orders 
        LEFT JOIN yellowteam.dbo.inventory 
        ON yellowteam.dbo.inventory.itemID=yellowteam.dbo.orders.itemID
        WHERE yellowteam.dbo.orders.orderID = ?";
$stmt = sqlsrv_prepare($conn, $sql, array($orderID), array( "Scrollable" => "buffered"));
sqlsrv_execute($stmt);
$isCartLoaded = (sqlsrv_num_rows($stmt) === false) ? 0 : sqlsrv_num_rows($stmt);

// TODO: couldn't find any viable way to store a false/true global variable, page seems to reload and reset it when table is made
// TODO: unless we find another way, the only option is to query the information again from the database and see if nothing overdraws stock


function checkStock($conn, $orderID) {
    $stockExceeded = false;
    $sql = "SELECT yellowteam.dbo.inventory.stock, 
        yellowteam.dbo.orders.quantity 
        FROM yellowteam.dbo.orders 
        LEFT JOIN yellowteam.dbo.inventory 
        ON yellowteam.dbo.inventory.itemID=yellowteam.dbo.orders.itemID
        WHERE yellowteam.dbo.orders.orderID = ?";
    $stmt = sqlsrv_prepare($conn, $sql, array($orderID), array( "Scrollable" => "buffered"));
    sqlsrv_execute($stmt);
    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
        if ($stockExceeded === true) {
            break;
        } else {
            ($row["stock"] - $row["quantity"]) > -1 ?: $stockExceeded = true;
        }
    }
    return $stockExceeded;
}

if(isset($_POST["checkout"])) {
    $stockExceeded = checkStock($conn, $orderID);

    if ($stockExceeded !== true && $isCartLoaded > 0) {
        echo '<script>console.log("Going in... orderID is :'.$orderID.'")</script>';
        echo '<script>console.log("Going in... username is :'.$username.'")</script>';
        // first we have to put the orderID into the new orderHistory table
        $sql = "INSERT INTO yellowteam.dbo.orderhistory (username, orderID, orderTimeStamp) VALUES (?, ?, (SELECT CURRENT_TIMESTAMP AS orderTimeStamp))";
        $stmt = sqlsrv_prepare($conn, $sql, array($username, $orderID));
        sqlsrv_execute($stmt);

        // TODO: logic to subtract items in quantity from stock
        // get the quantities we have in the cart
        $sql = "SELECT itemID, quantity FROM yellowteam.dbo.orders WHERE orderID = ?";
        $stmt = sqlsrv_prepare($conn, $sql, array($orderID));
        sqlsrv_execute($stmt);

        // place the quantities we have in the cart into an array of key => value pairs;
        $cartPairings = array();
        while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
            $cartPairings[$row["itemID"]] = $row["quantity"];
        }

        $sql = "UPDATE yellowteam.dbo.inventory SET stock = stock - ? WHERE itemID = ?";
        // create a loop of sql statements for each itemID and quantity pairing from cartPairings array:
        foreach($cartPairings as $itemID => $quantity) {
            $stmt = sqlsrv_prepare($conn, $sql, array($quantity, $itemID));
            sqlsrv_execute($stmt);
        }

        // then we can delete the cookieID row from the cookie table and delete the local cookie
        $sql = "DELETE FROM yellowteam.dbo.cookie WHERE cookieID = ?";
        $stmt = sqlsrv_prepare($conn, $sql, array($cookieID));
        sqlsrv_execute($stmt);
        setcookie('cookieID', "", time() - 3600); // Time in the past deletes the cookie on the client
    } elseif ($stockExceeded !== true && $isCartLoaded < 1) {
        echo '<script>alert("There\'s nothing in your cart to check out.")</script>';
    } else {
        echo '<script>alert("Some item(s) in your cart exceed what we have in stock. Please resolve the conflict and try again.")</script>';
    }
    // This is a major bandaid to page reloading after checkout is clicked and screwing up our table, but at least we don't need to learn AJAX:
    // And even if we did learn AJAX, who knows if it would solve our issue...
    header("Refresh:0");
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
	<style>
	div.c {
		text-align: right;
	} 
	</style>
</head>

<body>
<div class="header">
    <div class="links">
          <a href="index">Home</a>
        <a href="products">Products</a>
        <a href="cart">Cart</a>
        <?php if(isset($_SESSION["accesslevel"])) {
            if ($_SESSION["accesslevel"] > 1) {
                echo '<a href="addinventory">Add Inventory</a>';
            }
        }?>
        <?php if(isset($_SESSION["accesslevel"])) {
            if ($_SESSION["accesslevel"] > 1) {
                echo '<a href="updateinventory">Update Products</a>';
            }
        }?>
        <a href="contactus">Contact Us</a>
        <a href="aboutus">FAQ</a>
        <?php if(isset($_SESSION["accesslevel"])) {
            if ($_SESSION["accesslevel"] > 1) {
                echo '<a href="employees">Employees</a>';
            }
        }?>
        <?php if(!isset($_SESSION["username"])) {
            echo '<a href="login">Login</a>';
        }?>
        <?php if(isset($_SESSION["username"])) {
            echo '<a href="history">Order History</a>';
        }?>
        <?php if(isset($_SESSION["accesslevel"])) {
            if ($_SESSION["accesslevel"] > 1) {
                echo '<a href="weeklysales">Weekly Sales</a>';
            }
        }?>
        <?php if(isset($_SESSION["username"])) {
            echo '<a href="logout">Logout</a>';
        }?>
        }?>
    </div>
</div>

<main class="container p-5">

<div class="c">
    <form action="" method="POST">
        <button type="submit" name="clearCart" class="btn btn-link">Clear Cart</button>
    </form>
    
</div>

    <div><h3><b> Products <b></h3></div><br>
    <table class="table">
        <thead class="thead-dark">
        <tr>
            <th scope="col">#</th>
            <th scope="col">Product Name</th>
            <th scope="col">SKU</th>
            <th scope="col">Unit Cost</th>
            <th scope="col">Quantity</th>
            <th scope="col">Price</th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($cookieID !== null && $orderID !== null) {
            try {
                $total = 0;
                $count = 1;
                while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
                    // TODO: figure out AJAX for page reloading? https://stackoverflow.com/questions/2866063/submit-form-without-page-reloading
            ?>
            <tr>
                <th scope="row"><?php echo $count; ?></th>
                <td><?php echo $row["productName"]; ?></td>
                <td><?php echo $row["productSKU"]; ?></td>
                <td><?php echo '$'.number_format($row["price"]/$row["quantity"], 2, '.', ','); ?></td>
                <td><?php echo $row["quantity"]; ?></td>
                <td><?php echo '$'.number_format($row["price"],2, '.', ','); ?></td>
                <td><form action="" method="POST">
					<?php //Using 'htmlspecialchars' function to prevent from special character injection, and the "ENT_NOQUOTES" to not convert any quotes ?>
                        <input type="hidden" name="currentQuantity" <?php echo htmlspecialchars('value = " '. $row["quantity"]. '"', ENT_NOQUOTES); ?>/>
                        <input type="hidden" name="stock" <?php echo htmlspecialchars('value = " '. $row["stock"]. '"', ENT_NOQUOTES); ?> />
                        <input type="hidden" name="itemID" <?php echo htmlspecialchars('value = " '. $row["itemID"]. '"', ENT_NOQUOTES); ?> />
                        <button type="submit" name="removeFromCart" class="btn btn-dark">Remove from Cart</button>
                        <input type="number" min="1" name="quantity" style="width:20%;" <?php echo htmlspecialchars('value="1"', ENT_NOQUOTES)?> class="form-control" />
                        <?php if(($row['stock'] - $row['quantity']) < 0) { ?>
                            <span class="customBadge warning">Only <?php echo $row["stock"]; ?> Available</span>
                        <?php } ?>
                </form></td>
            </tr>
            <?php
                $count++;
                $total += $row["price"];
            } ?>
            <tr>
                <thead class="thead-dark">
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th>Total:</th>
                <th><?php echo '$'.number_format($total, 2, '.', ','); ?></th>
                <th><form method="POST">
                    <button type="submit" name="checkout" class="btn btn-light"
					<?php $stockExceeded = checkStock($conn, $orderID);
                    if ($stockExceeded !== true && $isCartLoaded > 0) { ?>
                            onclick=window.open("../printCart.php","demo","width=550,height=300,left=150,top=200,toolbar=0,status=0,");
                            target="_blank"
                    <?php } ?>
                    >Checkout</button>
                </form></th>
                </thead>
            </tr>
            <?php
            } catch (Exception $e) {
                // Probably adjust to have this log to console?
                echo $e->getmessage();
            } finally {
                sqlsrv_free_stmt($stmt);
            }
        } else { ?>
        </tbody>
    </table>
    <?php
    echo ($cookieID !== null && $orderID === null) ? "<p>We couldn't find your cart.</p>" : "<p>Your cart is empty.</p>";
    }
    ?>
</main>

</body>
</html>
