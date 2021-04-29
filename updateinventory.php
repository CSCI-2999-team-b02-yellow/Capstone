<?php

session_start();

	$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
	// $connection info to the database.
	$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
	$conn = sqlsrv_connect( $serverName, $connectionInfo);
	
 // if statement when the Submit button is pushed.

if(!isset($_SESSION["username"])){
    header("location: login");
} else {
    if ($_SESSION["accesslevel"] < 2) {
        header("location: login");
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
	$columnValues['category'] = $_POST['category'];
    $columnValues['itemDescription'] = $_POST['description'];
    $columnValues['price'] = $_POST['price'];
    $columnValues['stock'] = $_POST['stock'];
	
	//This part here is for image upload
	$file = $_FILES['file'];
		//Because of "multipart/form-data" on form, the $file will be an array of words(name, tmpName which is where the file is, the size, error, and type of file),
		//we have to separate them to change them as we want to.
	$fileName = $_FILES['file']['name'];
	$fileTmpName = $_FILES['file']['tmp_name'];
	$fileSize = $_FILES['file']['size'];
	$fileError = $_FILES['file']['error'];
	$fileType = $_FILES['file']['type'];

    // https://stackoverflow.com/questions/33205087/sql-update-where-in-list-or-update-each-individually
    // foreach ($arrayName as $key => $value) https://www.w3schools.com/php/php_arrays_associative.asp
    // since we are using a modular approach we can now use a single update statement:
    $count = 0;

    // TODO: possible syntax fix for multiple checkmarked items going through: https://www.sqlservertutorial.net/sql-server-basics/sql-server-insert-multiple-rows/
    foreach ($columnValues as $column => $userInput) {
        if(!$userInput == "") { // TODO: frustrating php data types why does strict === not work, only ==
            $selection = implode("', '", $_POST['selection']); // $_POST['selection'] comes from name="selection[]" in li input
            // echo "<script>console.log('selection is: $selection column is: $column userInput is: $userInput')</script>"; TODO: turn on for testing
            $sql = "UPDATE yellowteam.dbo.inventory SET ".$column." = ? WHERE itemID in ('" .$selection. "')";
            $stmt = sqlsrv_prepare($conn, $sql, array($userInput, $selection));
            sqlsrv_execute($stmt);
            $count++;

		}
    }
	// this part is for image upload
	// using $fileName to check if a file is uploaded or not. we can use $fileTmpName too because it is also empty when file is not uploaded.
	if (!empty($fileName)){
		//print_r($file); this is just to test the out put of the array $_FILES['file'].
		$selection = implode("', '", $_POST['selection']);
		$sql = "SELECT * FROM yellowteam.dbo.inventory WHERE itemID in ('" .$selection. "')";
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
					$count++;// this cout to follow Tomas logic of displaying alerts.
				}else{
					echo "There was an error uploading your file! it is too big. We only accept less than 1MB.";
				}
				
			}else{
				echo "There was an error uploading your file!";
			}
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
        <a href="index">Home</a>
        <a href="products">Products</a>
        <a href="cart">Cart</a>
        <?php if(isset($_SESSION["accesslevel"])) {
            if ($_SESSION["accesslevel"] > 1) {
                echo '<a href="addinventory">Add Inventory</a>';
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
    </div>
</div>

<main class="container p-5">
    <h2> Update Products </h2>
    <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search/Filter for a product.." title="Type in a Product Name"> <br>
    Please select the products to update<br><br>
    <form action="" method="POST" enctype="multipart/form-data">
        <ul id="myUL" class="noBulletPoints">
            <?php
            // I'm choosing to not allow search by the number left in stock, but we can introduce this if needed
            $sql = "SELECT * 
                    FROM yellowteam.dbo.inventory 
                    ORDER BY category, productName";
            $stmt = sqlsrv_prepare($conn, $sql);
            sqlsrv_execute($stmt);
            while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
                // name="selection[]" explanation: https://stackoverflow.com/questions/4688880/html-element-array-name-something-or-name-something
                // this stores the itemID values for checkmarked boxes in a list(array) called selection[]
                // this list of itemIDs is later used in an UPDATE SET WHERE IN statement, where a single field is updated for every itemID
                ?>

			<li><a>
                    <input type="checkbox" name="selection[]" value="<?php echo $row["itemID"]; ?>" >
                    <label for="">
						<img src=".\images\<?php echo ($row["itemID"]); ?>.jpg" alt="Image Test" style="width:50px;height:60px;" onclick=window.open("images/<?php echo ($row["itemID"]) ?>.jpg","demo","width=550,height=300,left=150,top=200,toolbar=0,status=0,") target="_blank">
                        <?php echo
                            $row["productName"]." - "
							.$row["category"]."  "
                            .$row["productSKU"]."  $"
                            .round($row["price"],2)." "
                            // .$row["itemDescription"]TODO: reintroduce when more readable
                            ." In Stock: " .$row["stock"];
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
					<input type='number' name='price' id='price' placeholder="Update Price" step="0.01" min="0">
				</details>
				<br>
				<details>
				  <summary>
					Category
					<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
					<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
				  </summary>
					<input type='text' name='category' id='category' placeholder="Update Category">
				</details>
				<br>
				<details>
				  <summary>
					Product SKU
					<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
					<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
				  </summary>
				    <input type='text' name='sku' id='sku' placeholder="Update SKU">
				</details>
				<br>
				<details>
				  <summary>
					Description
					<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
					<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
				  </summary>
				    <input type='text' name='description' id='description' placeholder="Update Description">
				</details>
                <br>
                <details>
                    <summary>
                        Stock
                        <svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
                        <svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
                    </summary>
                    <input type='number' name='stock' id='stock' placeholder="Update Stock">
                </details>
                <br>
                <details>
                    <summary>
                        Image
                        <svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
                        <svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
                    </summary>
					Please select image less than 1MB with (jpg, jpeg, or png) extension, or it won't be accepted.<br>
                    <input type="file" name="file">
					
                </details>
                <br><br>
                <button name="submit" class="btn btn-dark">Update</button>


    </form>
    <br>
</main>

<script>
function myFunction() {
    var filter, li, a, i, txtValue;
    filter = document.getElementById("myInput").value.toUpperCase();
    li = document.getElementsByTagName("li");
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
