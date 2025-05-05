<?php

include 'config.php';

// Add CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Log the API call for debugging
error_log("API called: " . date('Y-m-d H:i:s'));

// Get the search query from the GET request
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';

// Get pagination parameters from the GET request
$limit = 100;  // Limit the number of records per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;  // Default to page 1 if not specified
$offset = ($page - 1) * $limit;  // Calculate the offset for pagination

// Validate the search query
if (empty($searchTerm)) {
    echo json_encode(array("message" => "Search term is required.")); // Return an error message if search term is empty
    exit;
}

// Sanitize the input search term to prevent SQL injection
$searchTerm = $conn->real_escape_string($searchTerm);

// SQL Query to search for matching terms in the patient and client names with pagination
$sql = "SELECT tac.ID,
               tac.AIFCS_Number,
               CONCAT(tac.Lastname, ', ', tac.Firstname, ' ', LEFT(tac.middlename, 1), '.') AS Client_Fullname,
               CONCAT(tap.Lastname, ', ', tap.Firstname, ' ', LEFT(tap.middlename, 1), '.') AS Patient_Fullname,
               CONCAT(tac.Barangay, ', ', tac.Purok, '. ', tac.City) AS Address,
               tac.Date_Created,
               tac.Remarks_Client,
               tap.Remarks
        FROM tbl_aifcs_client_new tac
        INNER JOIN tbl_aifcs_patient_new tap
            ON tac.AIFCS_Number = tap.AIFCS_Number
        WHERE tac.isDeleted = 0
          AND (tac.Lastname LIKE '%$searchTerm%' OR tac.Firstname LIKE '%$searchTerm%' 
               OR tap.Lastname LIKE '%$searchTerm%' OR tap.Firstname LIKE '%$searchTerm%')
        LIMIT $limit OFFSET $offset"; // Adding limit and offset for pagination

// Execute the query
$result = $conn->query($sql);

if ($result === false) {
    // If query failed, return the error as JSON
    echo json_encode(array("message" => "Query failed: " . $conn->error));
    exit;
}

// Check if the query returns any results
if ($result->num_rows > 0) {
    // Initialize an array to store the results
    $data = array();
    
    // Fetch each row and add it to the data array
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    // Return the data as a simple array of records
    echo json_encode($data);
} else {
    // If no results found, return an empty array
    echo json_encode(array());
}

// Close the connection
$conn->close();
?>
