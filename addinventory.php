<?php

session_start();

$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if(!isset($_SESSION["username"])){
    header("location: login.php");
} else {
    if ($_SESSION["accesslevel"] < 2) {
        header("location: login.php");
    }
}

if(isset($_POST['addInv'])){
	
		//This part here is for image upload
	$file = $_FILES['file'];
		//Because of "multipart/form-data" on form, the $file will be an array of words(name, tmpName which is where the file is, the size, error, and type of file),
		//we have to separate them to change them as we want to.
	$fileName = $_FILES['file']['name'];
	$fileTmpName = $_FILES['file']['tmp_name'];
	$fileSize = $_FILES['file']['size'];
	$fileError = $_FILES['file']['error'];
	$fileType = $_FILES['file']['type'];

	$sql = "INSERT INTO yellowteam.dbo.inventory (category, productName, productSKU, itemDescription, price, stock)
			VALUES (?, ?, ?, ?, ?, ?)";

	$category = $_POST['p-category'];
	$productName = $_POST['p-name'];
	$productSKU = $_POST['p-sku'];
	$itemDescription = $_POST['p-desc'];
	$price = $_POST['p-price'];
    $stock = $_POST['p-stock'];
	
	// prepares our statement with connection info, all variables inside placeholders in sql:
	$stmt = sqlsrv_prepare( $conn, $sql, array($category, $productName, $productSKU, $itemDescription, $price, $stock));
    sqlsrv_execute($stmt);
	sqlsrv_free_stmt( $stmt);
	
	
	// this part is for image upload
	// using $fileName to check if a file is uploaded or not. we can use $fileTmpName too because it is also empty when file is not uploaded.
	if (!empty($fileName)){
		//print_r($file); this is just to test the out put of the array $_FILES['file'].
		$sql = "SELECT * FROM yellowteam.dbo.inventory WHERE productSKU in ('" .$productSKU. "')";
		$stmt = sqlsrv_prepare($conn, $sql);
		sqlsrv_execute($stmt);
		
		$row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC); // use this row to extract 'nameID' to rename the image file following it 
		
		// Separate the extension from the file to test it and Allowing only 'jpg, jpeg, or png' as file to be upload
		$fileExt = explode('.', $fileName);
		$fileActualExt = strtolower(end($fileExt));
		$allowed = array('jpg', 'jpeg', 'png', 'jfif');
		if(in_array($fileActualExt, $allowed)){
			if($fileError === 0){
				if($fileSize < 1000000){ // allowing file size up to 1MB
					$fileNameNew = $row["itemID"] .".jpg"; // take the Product ID as name to be associate with the product.
					$filenameDestination = 'images/'.$fileNameNew; 
					move_uploaded_file($fileTmpName, $filenameDestination); //this php function is to move file to destination we want.
					//header("Location: ") to do if we want to redirect and have a message displayed.
				
				}else{
					echo "There was an error uploading your file! it is too big. We only accept less than 1MB.";
				}
				
			}else{
				echo "There was an error uploading your file!";
			}
		}
		
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Add Products</title>
  <meta name="author" content="Team Yellow">
  <meta name="description" content="Nuts and bolts hardware company add products page">
  <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, products, add, add products">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- documentation at http://getbootstrap.com/docs/4.1/, alternative themes at https://bootswatch.com/ -->
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet"> <!-- also makes page responsive -->
  <link href="css/index.css" rel="stylesheet">
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
        }?>
        <?php if(isset($_SESSION["username"])) {
            echo '<a href="logout.php">Logout</a>';
        }?>
    </div>
</div>

<div class="main">
  <h3>Add Products</h3>
  <form action="" method="POST" enctype="multipart/form-data">
      <input type="text" id="p-category" name="p-category" placeholder="Category"><br><br>
      
      <input type="text" id="p-name" name="p-name" placeholder="Product Name"><br><br>
      
      <input type="text" id="p-sku" name="p-sku" placeholder="Product SKU"><br><br>
      
      <input type="text" id="p-desc" name="p-desc" placeholder="Product Description"><br><br>
      
      <input type="number" id="p-price" name="p-price" step="0.01" placeholder="Product Price"><br><br>
      
      <input type="number" id="p-stock" name="p-stock" placeholder="Product Stock"><br><br>
	  
	  <label for="p-sku">Product Image:</label>
	  <input type="file" name="file"><br><br>
	  <input type="submit" name="addInv" value="Submit"><br><br>
  </form>
</div>

</body>
</html>
