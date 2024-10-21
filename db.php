<?php
// Database connection settings
$host = "localhost"; // Database host
$user = "root";      // Database username
$pass = "";          // Database password
$dbname = "exam"; // Database name

// Create a new MySQLi connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
