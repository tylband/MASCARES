<?php
header("Access-Control-Allow-Origin: *");

// Allow specific HTTP methods
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type");

// Allow credentials (optional; only use with specific origins, not *)
header("Access-Control-Allow-Credentials: true");
session_start();
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

// Redirect to the login page
header("Location: login.php");
exit();
?>