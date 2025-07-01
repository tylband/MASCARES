<?php
session_start();

// Set Content-Type for API response
header("Content-Type: application/json");

// Database connection
include 'config.php';
// Initialize response
$response = ["status" => "success"];

try {
    // Validate the request method
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method. Only POST is allowed.", 405); // Method Not Allowed
    }

    // Check if patient_name is provided
    if (!isset($_POST['patient_name'])) {
        throw new Exception("Field 'patient_name' is missing.", 400); // Bad Request
    }

    // Sanitize input
    $field = 'patient_name';
    $name = mysqli_real_escape_string($conn, $_POST[$field]);

    // Prepare the SQL query
    $query = "SELECT * FROM assistance WHERE $field = ? AND date_issuance >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Failed to prepare the query.", 500); // Internal Server Error
    }

    // Bind parameters and execute the query
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the record exists
    if ($result->num_rows > 0) {
        throw new Exception("The name has been used within the last 3 months.", 409); // Conflict
    }

    // If no record is found, return success response
    $response["message"] = "The name is available for use.";

} catch (Exception $e) {
    // Handle exceptions and set appropriate HTTP status codes
    $response["status"] = "error";
    $response["message"] = $e->getMessage();
    http_response_code($e->getCode() ?: 500); // Default to 500 if no code is set
}

// Send the response
echo json_encode($response);

// Close connection
$conn->close();
?>
