<?php

// starts a new session
session_start();

// connection info we should move to conn.php file later
$serverName = "database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com";
$connectionInfo = array( "Database"=>'yellowteam', "UID"=>'admin', "PWD"=>'$LUbx6*xTY957b6');
$conn = sqlsrv_connect( $serverName, $connectionInfo);

// https://medium.com/@sherryhsu/session-vs-token-based-authentication-11a6c5ac45e4 thread for looking into security
// this session check feels weird, we set it to true, but why do we check that it exists?

// checks if the user is already logged in, if they are redirects based on access level
if(isset($_SESSION["username"])) {
    // all access levels greater than 1 (employee+) goes to employees.php
    if ($_SESSION["accesslevel"] > 1) {
        header("location: employees.php");
    } else {
        header("location: index.php");
    }
    exit;
}

// This is setting error and login fields to blank;
// Note: PHP variables are assigned by value, passed to functions by value and when containing/representing objects are passed by reference.
$username = $password = "";
$username_err = $password_err = "";

// isset listens to see if a button with the name 'submit' is clicked inside HTML:
if(isset($_POST['submit'])) {

    $checkpoint1Passed = false;
    // Check if username & password are empty (trim is necessary);
    if(empty(trim($_POST["username"]))) {
        $username_err = "Please enter your username.";
    } elseif(empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $username = htmlspecialchars(trim($_POST["username"]));
        $password = trim($_POST["password"]);
        $checkpoint1Passed = true;
    }
	
	/* Prepared statement (avoids SQL injection) - parameters go inside the array in the SQL sqlsrv_prepare statement;
	 * SIDE NOTE: If you want to insert a dynamic column, for example you don't know if you are targeting username --
	 * such as WHERE ? = ?; (which is invalid) you would have to sanitize input. Prepared statements only allow inserting
	 * values into columns, but not columns themselves. So you'd have to introduce a method that goes through
	 * all the columns in the table as literal strings and checks if the inserted column matches it first.
	 */

    $checkpoint2Passed = false;
    if ($checkpoint1Passed === true) {
        $sql = "SELECT username FROM yellowteam.dbo.users WHERE username = ?";
        $username = $_POST['username'];
        $stmt = sqlsrv_prepare($conn, $sql, array($username), array( "Scrollable" => "buffered"));
        sqlsrv_execute($stmt);

        // check that username returns only 1 result row -- just extra precaution against any db issues;
        // right now this is only logging issues, not preventing login
        if(sqlsrv_num_rows($stmt) != 1) {
            // introduce: if returned rows are not 1 throw custom exception
            echo '<script>console.log("Returned rows not equal to one!\n")</script>';
            $username_err = "Username does not exist.";
        } else {
            echo '<script>console.log("Row check passed.")</script>';
            $checkpoint2Passed = true;
        }
    }

    if ($checkpoint2Passed === true) {
        // Introducing try, catch, finally statement to always close conn/free resources, and handle errors:
        try {

            // Outline of the SQL statement, ? is used for user input to prevent SQL injection
            // this feels vulnerable to some sort of dump though it's server-side --> we should probably select things only if username & password match
            $sql = "SELECT ID, fullname, username, password, accesslevel FROM yellowteam.dbo.users WHERE username = ?";

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

            // checks form password matches database password (used "password_verify" to check between the hash and the palintext) 
            if(password_verify($password, $hashed_password)) {
                $banTime = failCheck($conn, $username);
                if ($banTime === null) {
                    // Store data in session variables, username basically means user is logged in
                    $_SESSION["username"] = $username;
                    $_SESSION["accesslevel"] = $accesslevel;
                    // TODO: this is our login, add cookie logic here:
                    $localCookieID = getLocalCookieID();
                    $databaseCookieID = getDatabaseID($conn, $username);
                    reconcileID($conn, $username, $localCookieID, $databaseCookieID);

                    // divide where user goes based on access level:
                    if ($accesslevel > 1) {
                        welcomeRedirect($fullname, 'employees.php');
                    } else {
                        welcomeRedirect($fullname, 'index.php');
                    }
                } else {
                    $minutes = $banTime / 60;
                    $seconds = $banTime % 60;
                    echo "<script>alert('Allowed failed logins exceeded. Please wait $minutes minutes and $seconds seconds before trying again.')</script>";
                }
            } else {
                // if the password is wrong, display error message & log the datetime stamp in the database:
                $password_err = "The password you've entered is not correct.";
                $sql = "INSERT INTO yellowteam.dbo.failedlogin (username, failedlogin) VALUES (?, CURRENT_TIMESTAMP)";
                $stmt = sqlsrv_prepare($conn, $sql, array($username));
                if(sqlsrv_execute($stmt)) {
                    echo '<script>console.log("Successfully logged failed login attempt.\n")</script>';
                } else {
                    echo '<script>console.log("Error in logging failed login attempt.\n")</script>';
                }

                // call failCheck() function to see if it returns null or the time remaining until login ban expires:
                $banTime = failCheck($conn, $username);
                if ($banTime !== null) {
                    // unlike strongly-typed languages, integer division needs floored in PHP to return a decimal value:
                    $minutes = floor($banTime / 60);
                    $seconds = $banTime % 60;
                    echo "<script>alert('Allowed failed logins exceeded. Please wait $minutes minutes and $seconds seconds before trying again.".
                        '\n\n'."Note: Trying to log in during this window will extend the time you must wait.')</script>";
                }
            }
        } catch (exception $e) {
            // Need to look up and introduce error handling logic here:
        } finally {
            // This ALWAYS executes (even after return!) & we always want to close the connection & free query result resources;
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
        }
    }
    // this ends checkpoint 3 logic
}

function getLocalCookieID() {
    // TODO: When I log in successfully, check if a cookie exists locally with a cookieID.
    $localCookieID = null;
    if(isset($_COOKIE['cookieID'])) {
        $localCookieID = $_COOKIE['cookieID'];
    }
    return $localCookieID;
}

function getDatabaseID($conn, $username) {
    // TODO: When I log in successfully, check if a cookieID exists for the username in the database.
    $databaseCookieID = null;
    try {
        $sql = "SELECT cookieID FROM yellowteam.dbo.cookie WHERE username = ?";
        $stmt = sqlsrv_prepare($conn, $sql, array($username), array( "Scrollable" => "buffered"));
        sqlsrv_execute($stmt);
        while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
            $databaseCookieID = $row['cookieID'];
        }
        echo '<script>console.log("Database CookieID is: '.$databaseCookieID.'")</script>';
    } catch (exception $e) {
        // Need to look up and introduce error handling logic here:
    } finally {
        sqlsrv_free_stmt($stmt);
    }
    return $databaseCookieID;
}


//TODO: Given database cookieID, we make an update statement to replace all instances of local cookieID with database cookieID in the "orders" table.
function updateDatabaseID($conn, $username, $localCookieID, $databaseCookieID) {
    try {
        // first we get the orderID associated with the username:
        $databaseOrderID = null;
        $sql = "SELECT orderID FROM yellowteam.dbo.cookie WHERE username = ?";
        $stmt = sqlsrv_prepare($conn, $sql, array($username), array( "Scrollable" => "buffered"));
        sqlsrv_execute($stmt);
        while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
            $databaseOrderID = $row['orderID'];
        }
        
        // second we get the orderID associated with the local cookieID:
        $localOrderID = null;
        $sql = "SELECT orderID FROM yellowteam.dbo.cookie WHERE cookieID = ?";
        $stmt = sqlsrv_prepare($conn, $sql, array($localCookieID), array( "Scrollable" => "buffered"));
        sqlsrv_execute($stmt);
        while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
            $localOrderID = $row['orderID'];
        }

        // third we update the cookieID and orderID in the orders table to that of the ones associated with the username
        $sql = "UPDATE yellowteam.dbo.orders SET orderID = ? WHERE orderID = ?";
        $stmt = sqlsrv_prepare($conn, $sql, array($databaseOrderID, $localOrderID), array( "Scrollable" => "buffered"));
        sqlsrv_execute($stmt);

        // we can then remove the row containing the local cookieID with no username attached once we have the orderID:
        $sql = "DELETE FROM yellowteam.dbo.cookie WHERE cookieID = ?";
        $stmt = sqlsrv_prepare($conn, $sql, array($localCookieID), array( "Scrollable" => "buffered"));
        sqlsrv_execute($stmt);

        // TODO: after updating login merge, we need to set local cookie value to that of the database:
        setcookie('cookieID', $databaseCookieID, time() + (86400 * 30), "/"); // 30-day expiration: 86400 is the seconds in a day

        // TODO: Error Handling: If the sql statement failed (for example, syntax error etc.), we do nothing. Don't wipe the cart by accident!
        // once finished we can change local cookie ID to the database cookie ID!

    } catch (exception $e) {
        // Need to look up and introduce error handling logic here:
    } finally {
        sqlsrv_free_stmt($stmt);
    }
}

function reconcileID($conn, $username, $localCookieID, $databaseCookieID) {
    // TODO: (Optional rare case). If the cookieID exists in the database, but not locally, update/create cookie with cookieID from database.
    if ($localCookieID === null && $databaseCookieID !== null) {
        $localCookieID = $databaseCookieID;
        setcookie('cookieID', $localCookieID, time() + (86400 * 30), "/"); // 30-day expiration: 86400 is the seconds in a day
    }

    echo '<script>console.log("Preparing to associate local cookie with username in the database")</script>';
    // TODO: If a cookie exists locally, but not in the database, update it in the database
    if ($localCookieID !== null && $databaseCookieID === null) {
        echo '<script>console.log("Local CookieID is: '.$localCookieID.'")</script>';
        echo '<script>console.log("username is: '.$username.'")</script>';
        try {
            $sql = "UPDATE yellowteam.dbo.cookie SET username = ? WHERE cookieID = ?";
            $stmt = sqlsrv_prepare($conn, $sql, array($username, $localCookieID));
            sqlsrv_execute($stmt);
        } catch (exception $e) {
            // Need to look up and introduce error handling logic here:
        } finally {
            sqlsrv_free_stmt($stmt);
        }
    }

    // TODO: If the cookieID in the cookie (local) does not match the database (server), handle the conflict:
    // If neither local nor database IDs are null, and they are not the same, update local ID with database ID:
    if ($localCookieID !== null && $databaseCookieID !== null && $localCookieID !== $databaseCookieID) {
        updateDatabaseID($conn, $username, $localCookieID, $databaseCookieID);
    }
}

// This function generates a pseudo-random 50-character alphanumeric string, our db stores 50 varchar cookieIDs
function genCookieID() {
    return bin2hex(random_bytes(25)); // Note: in hex a byte is 2 characters, so 25 hexes are actually 50 characters here
}

function welcomeRedirect($fullname, $url) {
    // Redirects user to a specific URL. Note this must be done in JavaScript! Hope you've got it installed
    // Refer to https://stackoverflow.com/questions/18305258/display-message-before-redirect-to-other-page for WHY:
    echo '<script>alert("Welcome '.$fullname.' !")</script>';
    echo "<script>setTimeout(\"location.href = '$url';\",400);</script>";
    // Extra reading: https://stackoverflow.com/questions/15466802/how-can-i-auto-hide-alert-box-after-it-showing-it
    // That could introduce an auto-closing alert box instead of it needing to have OK clicked to go away
}

function failCheck($conn, $username) {
    // We query the database and request the 3 most recent logins:
    $sql = "SELECT TOP 3 failedlogin FROM yellowteam.dbo.failedlogin WHERE username = ? ORDER BY failedlogin DESC";
	/* sqlsrv_num_rows requires a client-side, static, or keyset cursor, and will return false if you use a forward 
	 * cursor or a dynamic cursor. (A forward cursor is the default.) 
	 * https://docs.microsoft.com/en-us/sql/connect/php/sqlsrv-num-rows?view=sql-server-ver15
	 * This was a real pain to figure out why sqlsrv_num_rows was not returning a value. Buffered is client-side:
	 */
    $stmt = sqlsrv_prepare($conn, $sql, array($username), array( "Scrollable" => "buffered"));
    if(sqlsrv_execute($stmt)) {
        echo '<script>console.log("Successfully retrieved top 3 failed login attempts.\n")</script>';
    } else {
        echo '<script>console.log("Error in retrieving top 3 failed login attempts.\n")</script>';
    }

    // We take the difference in seconds of current timestamp and last 3 login attempts.
    // Note: we have to check that 3 login attempts were returned! First/second time fails shouldn't trigger.
    $numberOfRows = sqlsrv_num_rows($stmt);
    if ($numberOfRows === 3) {
        $failedlogin = array();
		$i = 0;
        while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
            $failedlogin[$i] = $row['failedlogin'];
			$result = $failedlogin[$i]->format('Y-m-d H:i:s');
			echo '<script>console.log("Failed login #'.$i.' datetime is: '.$result.'")</script>';
            $failedlogin[$i] = $failedlogin[$i]->format('U'); // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
			$i++;
        }
		
        // also getting current time (datetime) from the database:
        $sql = "SELECT CURRENT_TIMESTAMP AS currentTime";
        $stmt = sqlsrv_prepare($conn, $sql, array($username), array( "Scrollable" => "buffered"));
        if(sqlsrv_execute($stmt)) {
            echo '<script>console.log("Successfully retrieved current time.\n")</script>';
        } else {
            echo '<script>console.log("Error in retrieving current time.\n")</script>';
        }

		$row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
		$currentTime = $row['currentTime'];
		$result = $currentTime->format('Y-m-d H:i:s');
		echo '<script>console.log("The current datetime is: '.$result.'")</script>';
        $currentTime = $currentTime->format('U'); // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)

        // looping through and getting the difference between UNIX failtimes and current time in seconds:
        $difference = array();
		$i = 0;
        foreach($failedlogin as $failTime) {
            $difference[$i] = $currentTime - $failTime;
			echo '<script>console.log("The difference is: '.$difference[$i].'")</script>';
			$i++;
        }

        // going to count how many of the 3 most recent fails are actually within 15 minutes * 60 seconds (900 seconds)
        $count = 0;
        $max = 0;
        // counts each fail that is within 15 min from query of 3; finds max (closest to expiring) ban time;
		for ($i = 0; $i < 3; $i++) {
			if ($difference[$i] < 900) {
				if ($difference[$i] > $max) {
					$max = $difference[$i];
				}
				$count++;
			}
		}

        // if there exist 3 recent fails within the 3 returned by the query, return time left in seconds until ban is over
        if($count === 3) {
			echo '<script>console.log("Time remaining until login ban is lifted: '.(900 - $max).'")</script>';
            return 900 - $max;
        } else {
			echo '<script>console.log("No login ban exists.")</script>';
            return null;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="author" content="Team Yellow">
    <meta name="description" content="Nuts and bolts hardware company login page">
    <meta name="keywords" content="Nuts and bolts, hardware, Nuts and bolts hardware, login">
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
            echo '<a href="logout.php">Logout</a>';
        }?>
    </div>
</div>

<?php //Using 'htmlspecialchars' function to prevent from special character injection ?>
<div class="main">
    <p>Login Info</p>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
            <label>Username</label>
			
            <input type="text" name="username" class="form-control" value = "<?php echo htmlspecialchars($username);?>">
        </div>
        <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
            <label>Password</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="form-group">
            <input type="submit" name="submit" class="btn btn-primary" <?php echo htmlspecialchars('value = "Login"', ENT_NOQUOTES); ?>>
        </div>
        <span class="help-block"><?php echo $username_err; ?></span>
        <span class="help-block"><?php echo $password_err; ?></span>
        <p>Don't have an account? <a href="users.php">Sign up now</a>.</p>
    </form>
</div>

</body>
</html>
