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
    }
    return $orderID;
}
?>

<!DOCTYPE html>
<html>
  <head>
   <style>
   
   .invoice-box {
  max-width: 800px;
  margin: auto;
  padding: 30px;
  border: 1px solid #eee;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
  font-size: 16px;
  line-height: 24px;
  font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
  color: #555;
}

.invoice-box table {
  width: 100%;
  line-height: inherit;
  text-align: left;
}

.invoice-box table td {
  padding: 5px;
  vertical-align: top;
}

.invoice-box table tr td:nth-child(n + 2) {
  text-align: right;
}

.invoice-box table tr.top table td {
  padding-bottom: 20px;
}

.invoice-box table tr.top table td.title {
  font-size: 45px;
  line-height: 45px;
  color: #333;
}

.invoice-box table tr.information table td {
  padding-bottom: 40px;
}

.invoice-box table tr.heading td {
  background: #eee;
  border-bottom: 1px solid #ddd;
  font-weight: bold;
}

.invoice-box table tr.details td {
  padding-bottom: 20px;
}

.invoice-box table tr.item td {
  border-bottom: 1px solid #eee;
}

.invoice-box table tr.item.last td {
  border-bottom: none;
}

.invoice-box table tr.item input {
  padding-left: 5px;
}

.invoice-box table tr.item td:first-child input {
  margin-left: -5px;
  width: 100%;
}

.invoice-box table tr.total td:nth-child(2) {
  border-top: 2px solid #eee;
  font-weight: bold;
}

.invoice-box input[type="number"] {
  width: 60px;
}

@media only screen and (max-width: 600px) {
  .invoice-box table tr.top table td {
    width: 100%;
    display: block;
    text-align: center;
  }

  .invoice-box table tr.information table td {
    width: 100%;
    display: block;
    text-align: center;
  }
}

/** RTL **/
.rtl {
  direction: rtl;
  font-family: Tahoma, "Helvetica Neue", "Helvetica", Helvetica, Arial,
    sans-serif;
}

.rtl table {
  text-align: right;
}

.rtl table tr td:nth-child(2) {
  text-align: left;
}

   
   </style>
  </head>
  <body>
<div class="invoice-box">
  <table cellpadding="0" cellspacing="0">
    <tr class="top">
      <td colspan="4">
        <table>
          <tr>
            <td class="title">
				Nuts and Bolts
            </td>

            <td>
               Print Cart<br> Created: <?php echo date("m/d/Y"); ?> <br> Receipt #:
                <?php
                proc_nice(8); // TODO: positive higher numbers are lower priority, making sure $_SESSION orderID is available before we display
                echo $_SESSION['orderID'] ?>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <tr class="heading">
	 
      <td>Item</td>
      <td>Unit Cost</td>
      <td>Quantity</td>
      <td>Price</td>
	  <td></td>
    </tr>
	
	<?php
        $cookieID = isset($_COOKIE['cookieID']) ? $_COOKIE['cookieID'] : null;
        $orderID = isset($_COOKIE['cookieID']) ? getOrderID($conn, $cookieID) : null;

        if ($cookieID !== null && $orderID !== null) {
            try {
                $_SESSION['orderID'] = $orderID; // TODO: make sure we don't use session order ID for anything but printing a receipt!!
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
                $total = 0;
                $count = 1;
                while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
            ?>

    <tr class="item" v-for="item in items">
	  
      <td><?php echo $row["productName"]." - ".$row["productSKU"]; ?></td>
      <td><?php echo '$'.number_format($row["price"]/$row["quantity"], 2, '.', ','); ?></td>
      <td><?php echo $row["quantity"]; ?></td>
      <td><?php echo '$'.number_format($row["price"], 2, '.', ','); ?></td>
	  
    </tr>
            <?php
                $count++;
                $total += $row["price"];
            } ?>

    <tr class="total">
      <td colspan="3"></td>
      <td>Total:</td>
	  <td><?php echo '$'.number_format($total, 2, '.', ','); ?></td>
	  <td></td>
    </tr>
	<?php
            } catch (Exception $e) {
                // Probably adjust to have this log to console?
                echo $e->getmessage();
            } finally {
                sqlsrv_free_stmt($stmt);
            }
	} else { ?>
  </table>
      <?php
    echo ($cookieID !== null && $orderID === null) ? "<p>We couldn't find your cart.</p>" : "<p>Your cart is empty.</p>";
    }
    ?>
</div>
  </body>
</html>
