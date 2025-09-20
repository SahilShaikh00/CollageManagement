<?php 
$conn = new mysqli("localhost","root","","Collagemanagement");

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
?>