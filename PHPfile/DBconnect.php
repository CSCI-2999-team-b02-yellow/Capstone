<title>DB Connect</title>
<?php

$connect = mysqli_connect('localhost', 'root', '', 'yellowdb') or die("Connection Error"); 


//mysqli_query($connect, "UPDATE timetable SET time = CURRENT_TIME() WHERE id = 1");

$query=mysqli_query($connect, "SELECT * FROM timetable");

echo "This is your time saved: ";

while($time = mysqli_fetch_assoc($query)){
$s=$time["time"];
echo $s;

}
?>





