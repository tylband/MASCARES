<?php
session_start();

// Set content-type to JSON for API response
header('Content-Type: application/json');

include 'config.php';

// Get the user-selected report range and start/end dates
$reportRange = isset($_GET['report_range']) ? $_GET['report_range'] : 'weekly';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Set the date range for the report based on the selected range (Weekly, Monthly, Annually)
if ($reportRange == 'weekly') {
    $startDate = date('Y-m-d', strtotime('-1 week')); // 7 days ago
} elseif ($reportRange == 'monthly') {
    $startDate = date('Y-m-01'); // First day of this month
    $endDate = date('Y-m-t'); // Last day of this month
} elseif ($reportRange == 'annually') {
    $startDate = date('Y-01-01'); // First day of this year
    $endDate = date('Y-12-31'); // Last day of this year
}

// SQL query to fetch records within the selected date range
$sql = "SELECT date_issuance, patient_name, representative_name, options, amount_approved, expiry_date
        FROM assistance
        WHERE date_issuance BETWEEN '$startDate' AND '$endDate'";

// Execute the query
$result = $conn->query($sql);

// Store results in an array
$records = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row; // Store each row in the array
    }
} else {
    // No records found
    $records = [];
}

// Calculate the total amount approved within the selected date range
$totalAmount = 0;
foreach ($records as $row) {
    $totalAmount += $row['amount_approved'];
}

// Format the total amount to 2 decimal places
$totalAmountFormatted = number_format($totalAmount, 2);

// Close the database connection
$conn->close();

// Return the data as a JSON response with the appropriate API format
echo json_encode([
    'status' => 'success',
    'message' => 'Data fetched successfully',
    'data' => [
        'records' => $records,
        'totalAmount' => $totalAmountFormatted
    ]
]);
?>
