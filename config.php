<?php
// Database connection details
$host = '192.168.10.248';
$user = 'migz';
$password = '4medicine';
$dbname = 'medicine_mayors';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
