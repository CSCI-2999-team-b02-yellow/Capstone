<?php
// checks if HTML element by the name of 'addInv' has been clicked
// Note: PHP supports try catch blocks, error handling should be later rewritten using this logic
if(isset($_POST['addemployee'])){
	
	// SQLSRV are Microsoft developed drivers, PDO_SQLSRV or PDO are community developed drivers for MSSQL
	// $serverName is just the URL our database is hosted on:
	$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
	// $connection info is an array which can only take database, user & password:
	$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
	$conn = sqlsrv_connect( $serverName, $connectionInfo);
	
	// Connects to server, or spits out error log if it fails
	if( $conn ) {
		 echo '<script>console.log("Connection established.\n")</script>';
	}else{
		 echo '<script>console.log("Connection could not be established.\n");</script>';
		 
// placeholders (?) are used in SQL statements to prepare a statement & prevent SQL injection
	$sql = "INSERT INTO yellowteam.dbo.employees (firstname, lastname, username, password)
			VALUES (?, ?, ?, ?)";
		
	//----------------------------------------------------------------------------------------------------------------  
	// Using $_POST['addInv'] to trigger form, and then using $_POST['name'] to pull user input into variables.
	//----------------------------------------------------------------------------------------------------------------  
	$firstname = $_POST['employeefirstname'];
	$lastname = $_POST['employeelastname'];
	$username = $_POST['username'];
	$password = $_POST['password'];
	
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
?>

<head>
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
      </div>
	  
      <div class ="logo">
      <img src="inverselogo.png" alt="Italian Trulli" style="width:4.5%;height:4.5%;">
      </div>
</div>

<div class="content">  
  <h1>Nuts and Bolts Hardware</h1>
  <h3>Employees Page</h3>
  
  <!-- edited action="" since we don't have a PHP file to forward to, added method="POST" -Tomas -->
  <form action="" method="POST">
  <label for="employeefirstname">First Name:</label><br>
  <input type="text" id="employeefirstname" name="employeefirstname" value=""><br>
  
  <label for="employeelastname">Last Name:</label><br>
  <input type="text" id="employeelastname" name="employeelastname" value=""><br><br>
  
    <label for="username">Username:</label><br>
  <input type="text" id="username" name="username" value="" pattern="[a-z]"><br>
  
    <label for="password">Password:</label><br>
  <input type="text" id="password" name="password" value="" pattern=""(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*?[~`!@#$%\^&*()\-_=+[\]{};:\x27.,\x22\\|/?><])""><br>
  
  <input type="submit" name="addemployee" value="Create Employee Account">
  
  </form>
</div>

</body>
</html>
