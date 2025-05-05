<?php

// Database connection details
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'assistance_mayors';

// Create a new mysqli connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check for connection error
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error, 3, "error_log.txt");
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Check if all required fields are received
if (
    isset(
        $_POST['edit_id'], 
        $_POST['date_issuance'], 
        $_POST['patient_name'], 
        $_POST['name_representative'], 
        $_POST['options'], 
        $_POST['amount_approved'], 
        $_POST['expiry_date']
    )
) {
    // Sanitize and validate input
    $id = intval($_POST['edit_id']);
    $date_issuance = $conn->real_escape_string(trim($_POST['date_issuance']));
    $patient_name = $conn->real_escape_string(trim($_POST['patient_name']));
    $name_representative = $conn->real_escape_string(trim($_POST['name_representative']));
    $options = $conn->real_escape_string(trim($_POST['options']));
    $amount_approved = floatval($_POST['amount_approved']);
    $expiry_date = $conn->real_escape_string(trim($_POST['expiry_date']));

    // Validate date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_issuance) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiry_date)) {
        echo json_encode(['error' => 'Invalid date format']);
        exit;
    }

    // Prepare the query to update the record
    $query = "
        UPDATE assistance 
        SET 
            date_issuance = ?, 
            patient_name = ?, 
            representative_name = ?, 
            options = ?, 
            amount_approved = ?, 
            expiry_date = ? 
        WHERE 
            id = ?
    ";

    if ($stmt = $conn->prepare($query)) {
        // Bind the parameters to the prepared statement
        $stmt->bind_param('ssssisi', $date_issuance, $patient_name, $name_representative, $options, $amount_approved, $expiry_date, $id);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            error_log("Failed to update record with ID $id: " . $stmt->error, 3, "error_log.txt");
            echo json_encode(['error' => 'Failed to update record']);
        }

        $stmt->close();
    } else {
        error_log("Failed to prepare update query: " . $conn->error, 3, "error_log.txt");
        echo json_encode(['error' => 'Failed to prepare update query']);
    }
} else {
    echo json_encode(['error' => 'Missing required fields']);
}

// Close the database connection
$conn->close();

?>
