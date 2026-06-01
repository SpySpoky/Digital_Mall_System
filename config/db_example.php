<?php
//Rename that file to db.php and insert your info
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'digital_mall';

$conn = mysqli_connect($host, $user, $password, $database);

if(!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>