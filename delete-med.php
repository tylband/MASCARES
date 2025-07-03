<?php
header("Access-Control-Allow-Origin: *");

// Allow specific HTTP methods
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type");

// Allow credentials (optional; only use with specific origins, not *)
header("Access-Control-Allow-Credentials: true");
session_start();

// Set Content-Type for API response
header("Content-Type: application/json");

include 'config.php';

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method. Only POST is allowed.", 405); // Method Not Allowed
    }

    // Validate 'id' parameter
    if (!isset($_POST['id']) || !filter_var($_POST['id'], FILTER_VALIDATE_INT)) {
        throw new Exception("Invalid ID. The 'id' field must be a valid integer.", 400); // Bad Request
    }

    $id = (int)$_POST['id']; // Cast to integer to prevent SQL injection

    // Check if the user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception("User not logged in.", 401); // Unauthorized
    }

    // Get the logged-in user's username from session
    $username = $_SESSION['username'];

    // Set the activity type (for example, 'delete')
    $activity = "delete"; // Adjust as needed for different activities

    // Prepare the SQL query to update the status column and log the activity and user
    $stmt = $conn->prepare("UPDATE assistance SET status = 1, user = ?, activity = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare the SQL statement.", 500); // Internal Server Error
    }

    // Bind the parameters
    $stmt->bind_param("ssi", $username, $activity, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200); // OK
            echo json_encode([
                "success" => true,
                "message" => "Record marked as deleted successfully."
            ]);
        } else {
            throw new Exception("Record not found or already deleted.", 404); // Not Found
        }
    } else {
        throw new Exception("Error updating record.", 500); // Internal Server Error
    }

    $stmt->close();
} catch (Exception $e) {
    // Return error response with appropriate HTTP status code
    http_response_code($e->getCode() ?: 500); // Default to 500 if no code is provided
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
} finally {
    $conn->close(); // Ensure connection is always closed
}
?>
