<?php
// Database connection details
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'medicine_mayors';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
