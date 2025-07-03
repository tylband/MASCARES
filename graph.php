<?php
header("Access-Control-Allow-Origin: *");

// Allow specific HTTP methods
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type");

// Allow credentials (optional; only use with specific origins, not *)
header("Access-Control-Allow-Credentials: true");
session_start();


include 'config.php';
// SQL to fetch data (group by options)
$sql = "SELECT kind_assistance FROM assistance";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    // Grouping by options
    while($row = $result->fetch_assoc()) {
        $data[] = $row['kind_assistance'];
    }
}

// Count occurrences of each option
$optionCounts = array_count_values($data);

// Return the counts as JSON
echo json_encode($optionCounts);

$conn->close();
?>
