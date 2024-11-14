<?php 

function connection(){
    $host = "localhost";
    $usesname = "root";
    $password = "";
    $dbname = "CDMSSCVMS";


    $con = new mysqli($host,$usesname,$password,$dbname);


    if ($con->connect_error) {
        echo $con->connect_error;
    } else {
        return $con;
    }
}
?>