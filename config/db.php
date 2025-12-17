<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$servername = "makanmystery-db.c16c2yeq25d0.ap-southeast-1.rds.amazonaws.com";
$username = "admin";
$password = "030219#Mm";
$database = "mm_sdg12";

// Establish the MySQLi Connection
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>