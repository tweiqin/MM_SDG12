<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root";

// It is often an EMPTY STRING "" for default XAMPP/WAMP
// Use actual MySQL password if set one
$password = "";

// Match the database created in phpMyAdmin.
$database = "mm_sdg12";

// Establish the MySQLi Connection
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>