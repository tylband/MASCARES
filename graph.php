<?php
session_start();
header('Content-Type: application/json');

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
