<?php
// Database configuration
$servername = "localhost"; // or your server name
$username = "root";      // your database username
$password = "";          // your database password
$dbname = "MaluPetClinic";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include helper functions
require_once 'functions.php';

// Start session for user authentication
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>