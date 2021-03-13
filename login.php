<?php

// connection info we should move to conn.php file later
$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
$conn = sqlsrv_connect( $serverName, $connectionInfo);

// starts a new session
session_start();

// https://medium.com/@sherryhsu/session-vs-token-based-authentication-11a6c5ac45e4 thread for looking into security
// this session check feels weird, we set it to true, but why do we check that it exists?

// checks if the user is already logged in, if they are redirects based on access level
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // all access levels greater than 1 (employee+) goes to employees.php
    if ($_SESSION["accesslevel"] > 1) {
        header("location: employees.php");
    } else {
        header("location: customers.php");
    }
    exit;
}

// This is setting error and login fields to blank;
// Note: PHP variables are assigned by value, passed to functions by value and when containing/representing objects are passed by reference.
$username = $password = "";
$username_err = $password_err = "";

// isset listens to see if a button with the name 'submit' is clicked inside HTML:
if(isset($_POST['submit'])) {
	
    // Check if username & password are empty; introduced returns to not execute SQL if errors are found:
    // Note: need to check if the use of trim is even needed here, empty should be sophisticated enough to check for multiple spaces?
    if(empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
		return;
    } elseif(empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
		return;
    } else {
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
        // this feels vulnerable to some sort of dump though it's server-side --> we should probably select things only if username & password match
		$sql = "SELECT ID, fullname, username, password, accesslevel FROM yellowteam.dbo.user WHERE username = ?";

		// loading username/password from HTML form name to PHP variables
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
		// right now this is only logging issues, not preventing login
		if(sqlsrv_num_rows($stmt) != 1) {
			// introduce: if returned rows are not 1 throw custom exception
			echo '<script>console.log("Returned rows not equal to one!\n")</script>';  
		} else {  
			 echo '<script>console.log("Row check passed.")</script>';
		}
		
		// sqlsrv_fetch_array stores results, and grabs each row
		// this saves sessionID, username, password to variables; will need a hashpassword method to convert $row['password'] for comparison;
		while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
			$ID = $row['ID'];
			$fullname = $row['fullname'];
			$username = $row['username'];
			$hashed_password = $row['password'];
			$accesslevel = $row['accesslevel'];
		}

        /* Will need to reintroduce password_verify($password, $hash) which does a comparison
         * between plaintext password and the hash in the database. Need to see what method
         * can be used to convert plaintext to hash, so all registered users make hashed passwords.
         */

        // checks form password matches database password, and that user access is at least lvl 2
		if($password === $hashed_password && $accesslevel > 1) {

			// helper function for displaying notices:
			function function_alert($message) {
				echo "<script>alert('$message');</script>"; 
			}

			// Store data in session variables
			$_SESSION["loggedin"] = true;
			// why are we storing session id if we don't ever use it?
			$_SESSION["ID"] = $ID;
			$_SESSION["username"] = $username;
			$_SESSION["accesslevel"] = $accesslevel;

			// TODO: passwords match NOW we check if they are on a banned timeout!!!

			// redirect is instant, need to read link below to fix this:
            // https://stackoverflow.com/questions/18305258/display-message-before-redirect-to-other-page
			function_alert("Welcome ".$fullname."!");

			// Redirect user to employees page
			header("location: employees.php");

			// accesslevel = 1 is customers trying to login: customers.php is a placeholder
		} elseif ($password === $hashed_password && $accesslevel === 1) {
            header("location: customers.php");
        } else {
		    // TODO: implement logic to store failed login attempts in fail table


			$password_err = "The password you've entered is not correct.";
			// also need to introduce datetime stamps to add to the database under a failed recent login table:
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
            <div class="form-group">
                <input type="submit" name="submit" class="btn btn-primary" value="Login">
            </div>
        </form>
    </div>    
</body>
</html>
