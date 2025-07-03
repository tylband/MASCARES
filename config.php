<?php
header("Access-Control-Allow-Origin: *");

// Allow specific HTTP methods
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type");

// Allow credentials (optional; only use with specific origins, not *)
header("Access-Control-Allow-Credentials: true");
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
