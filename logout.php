<?php

session_start();
unset($_SESSION["username"]);
unset($_SESSION["accesslevel"]);
header("location: login");
exit;

?>