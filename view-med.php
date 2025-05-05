<?php
session_start();

include 'config.php';
// Check if the 'id' parameter is passed in the URL and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare the query to fetch the data for the given id
    $query = "SELECT * FROM assistance WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        // Bind the 'id' parameter to the prepared statement
        $stmt->bind_param('i', $id);

        // Execute the statement
        if ($stmt->execute()) {
            // Get the result
            $result = $stmt->get_result();

            // Fetch the record as an associative array
            $record = $result->fetch_assoc();

            if ($record) {
                // Return the record as JSON
                echo json_encode($record);
            } else {
                // Log the error when no record is found and return it
                error_log("Record with ID $id not found.", 3, "error_log.txt");
                echo json_encode(['error' => 'Record not found']);
            }
        } else {
            // Log query execution error
            error_log("Failed to execute query for ID $id", 3, "error_log.txt");
            echo json_encode(['error' => 'Failed to execute query']);
        }

        // Close the statement
        $stmt->close();
    } else {
        // Log query preparation error
        error_log("Failed to prepare the query for ID $id", 3, "error_log.txt");
        echo json_encode(['error' => 'Failed to prepare the query']);
    }
} else {
    // Log invalid or missing ID error
    error_log("Invalid or missing ID parameter", 3, "error_log.txt");
    echo json_encode(['error' => 'Invalid or missing ID']);
}

// Close the database connection
$conn->close();
?>
