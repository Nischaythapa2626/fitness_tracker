<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "fitness_tracker";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>