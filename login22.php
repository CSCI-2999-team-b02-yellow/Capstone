<?php

$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
// $connection info to the database.
$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
$conn = sqlsrv_connect( $serverName, $connectionInfo);

// Initialize 
session_start();
// Check if the user is already logged in
//https://medium.com/@sherryhsu/session-vs-token-based-authentication-11a6c5ac45e4 LOOK INTO IF CREATING SESSIONS IS SECURE
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.html");
    exit;
}

// what is this doing exactly? $password_err is getting triggered despite a username existing
$username = $password = "";
$username_err = $password_err = ""; 

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement - PARAMAETERS HAVE TO BE PREPARED IN THE SQLSRV STATEMENT
        $sql = "SELECT sessionid, username, password FROM yellowteam.dbo.login WHERE username = ?";   
        if($stmt = sqlsrv_prepare($conn, $sql, array($username))){
            // Bind variables to the prepared statement as parameters
            //mysqli_stmt_bind_param($stmt, "s", $param_username);
            // Set parameters
            //$param_username = $username;
            // Attempt to execute the prepared statement
            if(sqlsrv_execute($stmt)){
                // Store result - *CHECK THAT RESULT SET IS NOT EMPTY*
                sqlsrv_fetch_array($stmt);
                // Check if username exists, if yes then verify password
                if(sqlsrv_num_rows($stmt) == 1){                    
                    // Bind result variables
                    //mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
                        $id = $row['sessionID'];
                        $username = $row['username'];
                        $hashed_password = $row['password'];
                    }
                    if(password_verify($password, $hashed_password)){
                        // Password is correct, so start a new session
                        session_start();
                        // Store data in session variables
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;                            
                        // Redirect user to welcome page
                        header("location: index.html");
                    } else{
                        // Display an error message if password is not valid
                        $password_err = "The password you entered was not valid.";
                    }
            } else{
                    // Display an error message if username doesn't exist
                    $username_err = "No account found with that username.";
                }
            } else{
                echo "There was an error logging in. Please try again.";
            }
            // Close statement
            sqlsrv_free_stmt($stmt);
        }
    }
    // Close connection
    sqlsrv_close($conn);
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
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
        </form>
    </div>    
</body>
</html>