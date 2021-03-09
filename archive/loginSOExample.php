<?php


#starts a new session
session_start();

#includes a database connection
include 'connection.php';

#catches user/password submitted by html form
$user = $_POST['user'];
$password = $_POST['password'];

#checks if the html form is filled
if(empty($_POST['user']) || empty($_POST['password'])){
    echo "Fill all the fields!";
}else{

#searches for user and password in the database
$query = "SELECT * FROM [DATABASE_NAME].[dbo].[users] WHERE user='{$user}' AND"
         ."password='{$password}' AND active='1'";
$result = sqlsrv_query($conn, $query);  //$conn is your connection in 'connection.php'

#checks if the search was made
if($result === false){
     die( print_r( sqlsrv_errors(), true));
}

#checks if the search brought some row and if it is one only row
if(sqlsrv_has_rows($result) != 1){
       echo "User/password not found";
}else{

#creates sessions
    while($row = sqlsrv_fetch_array($result)){
       $_SESSION['id'] = $row['id'];
       $_SESSION['name'] = $row['name'];
       $_SESSION['user'] = $row['user'];
       $_SESSION['level'] = $row['level'];
    }
#redirects user
    header("Location: restrict.php");
}
}

?>
