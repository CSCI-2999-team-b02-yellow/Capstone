<?php

session_start();
// Adding code to redirect to login page if employee is not logged in:

// SQLSRV are Microsoft developed drivers, PDO_SQLSRV or PDO are community developed drivers for MSSQL
// $serverName is just the URL our database is hosted on:
$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
// $connection info is an array which can only take database, user & password:
$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
$conn = sqlsrv_connect( $serverName, $connectionInfo);


// checks if HTML element by the name of 'addInv' has been clicked
// Note: PHP supports try catch blocks, error handling should be later rewritten using this logic
if(isset($_POST['registeruser'])){
	
	function function_alert($message) { 
      
		// Display the alert box  
	echo "<script>alert('$message');</script>"; }
	
	//----------------------------------------------------------------------------------------------------------------  
	// Using $_POST['addInv'] to trigger form, and then using $_POST['name'] to pull user input into variables.
	//----------------------------------------------------------------------------------------------------------------  
	
	if($_POST['password'] <> $_POST['repassword']){
		
		// Function call 
		function_alert("The two passwords do not match. Please check.");
	
	// Other conditions to update the database.	
	}else{

		$fullname = $_POST['fullname'];
		$username = $_POST['username'];
		// Hashing the password using "password_hash" function (PHP built in function) before saving it on the database.
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
		
	// placeholders (?) are used in SQL statements to prepare a statement & prevent SQL injection
		$sql = "INSERT INTO yellowteam.dbo.users (fullname, username, password, accesslevel)
				VALUES (?, ?, ?, 1)";
			
		// prepares our statement with connection info, all variables inside placeholders in sql:
		$stmt = sqlsrv_prepare( $conn, $sql, array(&$fullname, &$username, &$password, &$accesslevel));
		
		// checks the statement for errors, sqlsrv_prepare returns false if there's an error:
		if( $stmt )  
		{  
			 echo '<script>console.log("Statement prepared.\n")</script>';  
		}  
		else  
		{  
			 echo '<script>console.log("Error in preparing statement.\n")</script>';  
			 // $log = print_r( sqlsrv_errors(), true);  
		} 
		
		// This actually uses the statement on the database, prints out errors if something happens:
		if( sqlsrv_execute( $stmt))  
		{  
			  echo '<script>console.log("Statement executed.\n")</script>';  
		}  
		else  
		{  
			 echo '<script>console.log("Error in executing statement.\n")</script>';  
			 // $log = print_r( sqlsrv_errors(), true);   
		}  
		
		// Closes the connection and also releases statement resources:
		sqlsrv_free_stmt( $stmt);  
		sqlsrv_close( $conn); 
		}
	}

?>
<html>
<head>

 <!-- documentation at http://getbootstrap.com/docs/4.1/, alternative themes at https://bootswatch.com/ -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">
		
  <meta charset="utf-8">
  <title>Register Users</title>
  <meta name="author" content="Team Yellow">
  <meta name="description" content="Nuts and bolts hardware company register users page">
  <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, products, add, add products">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="css/index.css" rel="stylesheet">
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

<div class="main">
<main class="container p-5">
<div class="content">
  <h3>User Registration</h3>
  <form action="" method="POST">
      <label for="fullname">Full Name:</label><br>
      <input type="text" id="fullname" name="fullname" placeholder="Full Name" value=""><br>
      <label for="username">Username:</label><br>
      <input type="text" id="username" name="username" value="" placeholder="Username" pattern="^[a-z]*$" title="username can only be lowercase with no spaces."><br>
      <label for="password">Password:</label><br>
      <input type="password" id="password" name="password" value="" placeholder="Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*?[~`!@#$%\^&*()\-_=+[\]{};:\x27.,\x22\\|/?><]).{1,}" title="Password must have at least one lowercase letter, one uppercase, one number, and a special character."><br>
      <label for="password">Re-enter Password:</label><br>
      <input type="password" id="repassword" name="repassword" value="" placeholder="Re-enter Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*?[~`!@#$%\^&*()\-_=+[\]{};:\x27.,\x22\\|/?><]).{1,}" title="Password must have at least one lowercase letter, one uppercase, one number, and a special character."><br>
      <br>
      <button name="registeruser" value="Register User" class="btn btn-dark">Register User</button>
  </form>
</div>
</main>
</div>

</body>
</html>
