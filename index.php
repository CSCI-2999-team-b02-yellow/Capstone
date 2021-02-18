<?php

// Defining MySQL database server info, remove to connection.php for security later:
$server = 'yellowteam-database-1.cwszuet1aouw.us-east-1.rds.amazonaws.com';
$user = 'admin';
$password = '$LUbx6*xTY957b6';
$databaseName = 'YellowGroupDatabase';

	// I put the this function here to be able to use it to connect once instead of connecting everytime we push a button
	// MySQLi extension (improved) is a database driver used in PHP scripting for interfacing with MySQL databases
	$conn = mysqli_connect($server, $user, $password, $databaseName) or die("Connection Error"); 
		
if(isset($_POST['timeLog'])){
	
	// using UPDATE instead of INSERT to just use one row in the database. 
	// CURRENT_TIME is a MySQL function, we want to use database time as front-end can always be manipulated
	// Currently database is on +5 hours, which I think is GMT. Looks like this can be changed in my.cnf file in MySQL
	mysqli_query($conn, "UPDATE YellowGroupDatabase.buttontime SET timeClicked = CURRENT_TIME() WHERE buttontimeID = 1;");
	

    
}

if(isset($_POST['getTime'])){
	
	// Using this command to get the first row of the database. 
	$sql = 'SELECT timeClicked FROM YellowGroupDatabase.buttontime WHERE buttontimeID = 1;';
	$result = mysqli_query($conn, $sql);
	
	// This nonsense seems mandatory to be able to store from $result object to a variable in PHP
	while($row = mysqli_fetch_assoc($result)){
		$r=$row["timeClicked"];
	}
	
	// PHP function to run JavaScript alert with a message:
	function function_alert($message) { 
		echo "<script>alert('$message');</script>"; 
	} 

	function_alert("This is your time saved: ". $r);
	

}
	// Closing the connection
	$conn -> close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Team Yellow Sprint Zero</title>

<style>
body {
background-color: grey;
}
.main {
  border: 5px dotted black;
  background-color: lightgrey;    
  text-align: center;
  display: inline-block;
  padding-right: 20px;
  padding-left: 20px; 
  position: absolute; 
  left: 35%; 
  top: 30%; 
}
.main h1 {
  color: black;
  font-size: 60px;
  font-family: "Lucida Console", "Courier New", monospace;
}
.timeLog {
  font-family: "Lucida Console", "Courier New", monospace;
  background-color: black;
  border: dotted;
  color: white;
  padding: 15px 38px;
  text-align: center;
  font-size: 20px;
  margin: 4px 2px;
}
.timeLog:hover{
  font-family: "Lucida Console", "Courier New", monospace;
  background-color: white;
  border: dotted;
  color: black;
  padding: 15px 32px;
  text-align: center;
  font-size: 20px;
  margin: 4px 2px;
  cursor: pointer;
}
.getTime {
  font-family: "Lucida Console", "Courier New", monospace;
  background-color: black;
  border: dotted;
  color: white;
  padding: 15px 38px;
  text-align: center;
  font-size: 20px;
  margin: 4px 2px;
}
.getTime:hover{
  font-family: "Lucida Console", "Courier New", monospace;
  background-color: white;
  border: dotted;
  color: black;
  padding: 15px 32px;
  text-align: center;
  font-size: 20px;
  margin: 4px 2px;
  cursor: pointer;
}
#current {
  font-family: "Lucida Console", "Courier New", monospace;
  font-size: 20px;
}
</style>
<script> 
function timeLoop(){
var time = new Date(); 
var h = time.getHours();
var m = time.getMinutes();
var s = time.getSeconds();
m = singleDigit(m);
s = singleDigit(s);
h = standardTime(h)

document.getElementById("current").innerHTML = h + ":" + m + ":" + s;

}

function standardTime(i) {
if (i > 12) {i = i - 12}; 
return i; 
}
function singleDigit(i) {
    if (i < 10) {i = "0" + i};  
    return i;
}
setInterval(timeLoop, 1000);
</script>
</head>
<body onload="timeLoop()">

<div class="main"> 

<h1>Hello World</h1> 

<div id="current"></div>
<br>

<form action="" method="POST">
    <button name="timeLog" class="timeLog">Log the time</button>
</form>

<form action="" method="POST">
    <button name="getTime" class="getTime">Last Time Recorded</button>
</form>

</div>


</body>
</html>
