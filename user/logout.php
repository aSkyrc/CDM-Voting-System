<?php 
session_start();
include("../connection/connection.php");
$con = connection();

unset($_SESSION['UserLogin']);
session_destroy(); // Destroy the session completely

header("Location: ../user/Landing.php"); // Adjust the path as needed
exit; // Ensure script execution stops here
?>