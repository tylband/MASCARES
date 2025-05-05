<?php
session_start();

// Set content-type to JSON for API response
header('Content-Type: application/json');

include 'config.php';
// Helper function for sanitizing inputs
function sanitize_input($data) {
    global $conn;
    // Trim, strip slashes, and convert special characters to prevent XSS
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Check if all required fields are received
if (isset($_POST['edit_id'], $_POST['date_issuance'], $_POST['patient_name'], $_POST['name_representative'], $_POST['options'], $_POST['amount_approved'], $_POST['expiry_date'])) {

    // Sanitize inputs to avoid SQL Injection and ensure valid data
    $id = sanitize_input($_POST['edit_id']);
    $date_issuance = sanitize_input($_POST['date_issuance']);
    $patient_name = sanitize_input($_POST['patient_name']);
    $name_representative = sanitize_input($_POST['name_representative']);
    $options = sanitize_input($_POST['options']);
    $amount_approved = sanitize_input($_POST['amount_approved']);
    $expiry_date = sanitize_input($_POST['expiry_date']);

    // Validate if 'id' is an integer (important for security)
    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid ID format'
        ]);
        exit;
    }

    // Validate and ensure the proper format for 'amount_approved' (assumed to be a number)
    if (!is_numeric($amount_approved)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid amount_approved value, it must be numeric'
        ]);
        exit;
    }

    // Validate date format for 'date_issuance' and 'expiry_date'
    if (!validate_date($date_issuance) || !validate_date($expiry_date)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid date format. Please use Y-m-d format.'
        ]);
        exit;
    }

    // Prepare the query to update the record
    $query = "UPDATE assistance SET date_issuance = ?, patient_name = ?, representative_name = ?, options = ?, amount_approved = ?, expiry_date = ? WHERE id = ?";

    if ($stmt = $conn->prepare($query)) {
        // Bind the parameters to the prepared statement
        $stmt->bind_param('ssssisi', $date_issuance, $patient_name, $name_representative, $options, $amount_approved, $expiry_date, $id);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Record updated successfully'
            ]);
        } else {
            error_log("Failed to update record with ID $id", 3, "error_log.txt");
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update record. Please try again.'
            ]);
        }

        $stmt->close();
    } else {
        error_log("Failed to prepare update query", 3, "error_log.txt");
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error: Failed to prepare update query'
        ]);
    }

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
}

// Close the database connection
$conn->close();

// Helper function to validate date format (Y-m-d)
function validate_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
?>
