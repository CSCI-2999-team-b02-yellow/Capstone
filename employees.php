<?php

session_start();
// Adding code to redirect to login page if employee is not logged in:

if(!isset($_SESSION["username"])){
    header("location: login");
} else {
    if ($_SESSION["accesslevel"] < 2) {
        header("location: login");
    }
}

// SQLSRV are Microsoft developed drivers, PDO_SQLSRV or PDO are community developed drivers for MSSQL
// $serverName is just the URL our database is hosted on:
$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
// $connection info is an array which can only take database, user & password:
$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
$conn = sqlsrv_connect( $serverName, $connectionInfo);


// checks if HTML element by the name of 'addInv' has been clicked
// Note: PHP supports try catch blocks, error handling should be later rewritten using this logic
if(isset($_POST['addemployee'])){
	
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
				VALUES (?, ?, ?, 2)";

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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Employees</title>
  <meta name="author" content="Team Yellow">
  <meta name="description" content="Nuts and bolts hardware company employee registration page">
  <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, employees, employee registration">
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
    </div>
</div>

<div class="main">
<main class="container p-5">
<div class="content">
  <h3>Employees Registration</h3>

  <form action="" method="POST">
  <label for="employeefirstname">First Name:</label><br>
  <input type="text" id="fullname" name="fullname" placeholder="Full Name" value=""><br>

  <label for="username">Username:</label><br>
  <input type="text" id="username" name="username" value="" placeholder="Username" pattern="^[a-z]*$" title="username can only be lowercase with no spaces."><br>
  
  <label for="password">Password:</label><br>
  <input type="password" id="password" name="password" value="" placeholder="Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*?[~`!@#$%\^&*()\-_=+[\]{};:\x27.,\x22\\|/?><]).{1,}" title="Password must have at least one lowercase letter, one uppercase, one number, and a special character."><br>

  <label for="password">Re-enter Password:</label><br>
  <input type="password" id="repassword" name="repassword" value="" placeholder="Re-enter Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*?[~`!@#$%\^&*()\-_=+[\]{};:\x27.,\x22\\|/?><]).{1,}" title="Password must have at least one lowercase letter, one uppercase, one number, and a special character."><br>

	<br>
  <button name="addemployee" value="Create Employee Account" class="btn btn-dark">Create Employee Account</button>
  
  </form>
</div>
</main>
</div>

</body>
</html>
