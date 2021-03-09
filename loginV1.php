<?php

// connection info we should move to conn.php file later
$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
$conn = sqlsrv_connect( $serverName, $connectionInfo);

// starts a new session
session_start();
// checks if the user is already logged in
// https://medium.com/@sherryhsu/session-vs-token-based-authentication-11a6c5ac45e4 thread for looking into security
// this session check feels weird, we set it to true, but why do we check that it exists?
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.html");
    exit;
}

// This is setting error and login fields to blank; interestingly PHP evaluates this as $password = ""; $username = $password; in that order!
$username = $password = "";
$username_err = $password_err = ""; 

//$password_err is getting triggered despite a username existing

// Processing form data when form is submitted -- editing out for now as I'm not sure how this works:
// if($_SERVER["REQUEST_METHOD"] == "POST"){
	
// a little bit more confident this works:
if(isset($_POST['submit'])) {
	
    // Check if username & password are empty; introduced returns to not execute SQL if errors are found:
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
		return;
    } elseif(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
		return;
    } else{
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);
    }
	
	/* Prepared statement (avoids SQL injection) - parameters go inside the array in the SQL sqlsrv_prepare statement;
	 * SIDE NOTE: If you want to insert a dynamic column, for example you don't know if you are targeting username --
	 * such as WHERE ? = ?; (which is invalid) you would have to sanitize input. Prepared statements only allow inserting
	 * values into columns, but not columns themselves. So you'd have to introduce a method that goes through
	 * all the columns in the table as literal strings and checks if the inserted column matches it first.
	 */
	 
	// Introducing try, catch, finally statement to always close conn/free resources, and handle errors:
	try {
		// Outline of the SQL statement, ? is used for user input to prevent SQL injection
		$sql = "SELECT ID, username, password FROM yellowteam.dbo.users WHERE username = ?";
		// loading username/password from form name to php variable
		$username = $_POST['username'];
		$password = $_POST['password'];
		// Loads connection info, our sql, and parameters ($username) into the prepared statement
		$stmt = sqlsrv_prepare($conn, $sql, array($username));
		
		// checks the prepared statement for errors, sqlsrv_prepare returns false if there's an error;
		// not sure if this is necessary in try-catch-finally block though, will check later -- maybe convert elses to throws?
		if($stmt) {  
			 echo '<script>console.log("Statement prepared.\n")</script>';  
		} else {  
			 echo '<script>console.log("Error in preparing statement.\n")</script>';
		} 
		
		// use the prepared statement on the database: returns true/false;
		if(sqlsrv_execute($stmt)) {  
			  echo '<script>console.log("Statement executed.\n")</script>';  
		} else {  
			 echo '<script>console.log("Error in executing statement.\n")</script>';
		}
		
		// check that username returns only 1 result row -- just extra precaution against any db issues;
		if(sqlsrv_num_rows($stmt) != 1) {
			// if returned rows are not 1 throw exception on 0 or on more than 1
			// introduce catches for thrown exceptions
			echo '<script>console.log("Returned rows not equal to one!\n")</script>';  
		} else {  
			 echo '<script>console.log("Row check passed.")</script>';
		}
		
		// sqlsrv_fetch_array stores results, and grabs each row, which we confirmed is only 1 before!
		// this saves sessionID, username, password to variables; will need a hashpassword method to convert $row['password'] for comparison;
		while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
			$ID = $row['ID'];
			$username = $row['username'];
			$hashed_password = $row['password'];
		}
		
		// checks that password entered matches password in database;
		
		// password_verify($password, $hash) takes 2 parameters and compares them, not sure about salt;
		// need to reintroduce this to not store plain-text passwords for security purposes later:
		// also not sure if to use equal (==) or strict equal (===) here?
		if($password == $hashed_password){
			// Password is correct, so start a new session -- we already had a new session, why are we doing this again?
			// session_start();
			// Store data in session variables
			$_SESSION["loggedin"] = true;
			$_SESSION["ID"] = $ID;
			$_SESSION["username"] = $username;                            
			// Redirect user to welcome page
			header("location: index.html");
		} 
	} catch (exception $e) {
		// Need to look up and introduce error handling logic here:
	} finally {
		// This ALWAYS executes & we always want to close the connection & free query result resources;
		sqlsrv_free_stmt($stmt);
		sqlsrv_close($conn);
	}
	
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    
    </style>
</head>
<body>
    <h1>Login</h1>
      <div class="wrapper">
        <p>Login Info</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
			<!-- added name="submit" so our PHP can trigger off the button, not sure about $_SERVER functionality -->
            <div class="form-group">
                <input type="submit" name="submit" class="btn btn-primary" value="Login">
            </div>
        </form>
    </div>    
</body>
</html>
