<?php

// Adding code to redirect to login page if employee is not logged in:
session_start();
if(!isset($_SESSION['loggedin'])){
   header("location: login.php");
}

//adding logout button as well as PHP logic to execute it:
if(isset($_POST['logout'])) {
	// Unset all of the session variables.
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}

	// Finally, destroy the session.
	session_destroy();
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
		 
		$password = $_POST['password'];
		$firstname = $_POST['employeefirstname'];
		$lastname = $_POST['employeelastname'];
		$username = $_POST['username'];
		
	// placeholders (?) are used in SQL statements to prepare a statement & prevent SQL injection
		$sql = "INSERT INTO yellowteam.dbo.employees (firstname, lastname, username, password)
				VALUES (?, ?, ?, ?)";
			
		// prepares our statement with connection info, all variables inside placeholders in sql:
		$stmt = sqlsrv_prepare( $conn, $sql, array(&$firstname, &$lastname, &$username, &$password));
		
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
  <title>Employees</title>
  <meta name="author" content="Team Yellow">
  <meta name="description" content="Nuts and bolts hardware company employees page">
  <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, products, add, add products">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="css/indexstyle.css" rel="stylesheet">
</head>

<body>
<div class="header">
      <div class="links">
      <a class="active" href="index.html">Home</a>
      <a href="products.html">Products</a>
      <a href="addinventory.php">Add Inventory</a>
	  <a href="updateinventory.php">Update Products</a>
	  <a href="contactus.html">Contact Us</a>	  
	  <a href="aboutus.html">FAQ</a>
	  <a href="employees.php">Employees</a>
	  <div><form action="" method="POST">
	  <button name="logout" class="btn btn-dark">Log out</button>
	  </form></div>
      </div>
	  
      
</div>

<br>


<main class="container p-5">
<div class="content">  
  <h1>Nuts and Bolts Hardware</h1>
  <h3>Employees Page</h3>
  
  <!-- edited action="" since we don't have a PHP file to forward to, added method="POST" -Tomas -->
  <form action="" method="POST">
  <label for="employeefirstname">First Name:</label><br>
  <input type="text" id="employeefirstname" name="employeefirstname" placeholder="First Name" value=""><br>
  
  <label for="employeelastname">Last Name:</label><br>
  <input type="text" id="employeelastname" name="employeelastname" placeholder="Last Name" value=""><br>
  
    <label for="username">Username:</label><br>
	<!-- old pattern for username: "[a-z].{1,}" new pattern: ^[a-z]*$ -->
  <input type="text" id="username" name="username" value="" placeholder="Username" pattern="^[a-z]*$" title="username can only be lowercase with no spaces."><br>
  
    <label for="password">Password:</label><br>
  <input type="text" id="password" name="password" value="" placeholder="Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*?[~`!@#$%\^&*()\-_=+[\]{};:\x27.,\x22\\|/?><]).{1,}" title="Password must have at least one lowercase letter, one uppercase, one number, and a special character."><br>

<label for="password">Re-enter Password:</label><br>
  <input type="text" id="repassword" name="repassword" value="" placeholder="Re-enter Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*?[~`!@#$%\^&*()\-_=+[\]{};:\x27.,\x22\\|/?><]).{1,}" title="Password must have at least one lowercase letter, one uppercase, one number, and a special character."><br>

  
	<br>
  <button name="addemployee" value="Create Employee Account" class="btn btn-dark">Create Employee Account</button>
  
  </form>
</div>

       </main>

        <footer>
		<div>
            Service provided by YellowTeam 2021
		</div>
        </footer>
</body>
</html>
