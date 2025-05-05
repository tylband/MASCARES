<?php
session_start();

// Set content-type to JSON for API response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Log the API call for debugging
error_log("API called: " . date('Y-m-d H:i:s'));

// Include your database configuration
include 'config.php';

// Check if the database connection was successful
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// SQL query to fetch records where status = 0
$sql = "SELECT id, date_issuance, patient_name, representative_name, options, amount_approved, expiry_date 
        FROM assistance 
        WHERE status = 0";

$result = $conn->query($sql);

// Store the records in an array
$records = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = [
            'id' => $row['id'],
            'patient_name' => $row['patient_name'],
            'date_issuance' => $row['date_issuance'],
            'representative_name' => $row['representative_name'],
            'options' => $row['options'],
            'amount_approved' => $row['amount_approved'],
            'expiry_date' => $row['expiry_date']
        ];
    }

    // Return success response with the records
    echo json_encode([
        'status' => 'success',
        'message' => 'Records fetched successfully',
        'data' => $records
    ]);
} else {
    // If no records are found, return an empty array
    echo json_encode([
        'status' => 'success',
        'message' => 'No records found',
        'data' => []
    ]);
}

// Close the database connection
$conn->close();
?>
