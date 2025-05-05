<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

// Get the logged-in user's username from session
$user = $_SESSION['username']; 

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input data
    $date_issuance = filter_input(INPUT_POST, 'date_issuance', FILTER_SANITIZE_STRING);
    $control_number = filter_input(INPUT_POST, 'control_number', FILTER_SANITIZE_STRING);
    $patient_name = filter_input(INPUT_POST, 'patient_name', FILTER_SANITIZE_STRING);
    $representative_name = filter_input(INPUT_POST, 'name_representative', FILTER_SANITIZE_STRING);
    $options = filter_input(INPUT_POST, 'options', FILTER_SANITIZE_STRING);
    $amount_approved = filter_input(INPUT_POST, 'amount_approved', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $expiry_date = filter_input(INPUT_POST, 'expiry_date', FILTER_SANITIZE_STRING);
    $activity = "add"; // The activity for this request is 'add'

    // Validate required fields
    if (empty($patient_name) || empty($amount_approved)) {
        echo json_encode(["status" => "error", "message" => "Patient name and amount approved are required fields."]);
        exit();
    }

    // Debugging: Check if username is correctly retrieved
    if (empty($user)) {
        echo json_encode(["status" => "error", "message" => "Username is not set in the session."]);
        exit();
    }

    // Check if the patient's name has been used in the last 3 months
    $queryPatientCheck = "SELECT * FROM assistance WHERE patient_name = ? AND date_issuance >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
    $stmt = $conn->prepare($queryPatientCheck);
    $stmt->bind_param("s", $patient_name);
    $stmt->execute();
    $resultPatientCheck = $stmt->get_result();

    if ($resultPatientCheck->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "The patient's name has been used within the last 3 months."]);
        exit();
    }

    // Insert the new record into the database
    $insertQuery = "INSERT INTO assistance (date_issuance, patient_name, representative_name, options, amount_approved, expiry_date, user, activity, control_number)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Failed to prepare the SQL statement."]);
        exit();
    }

    // Bind parameters
    $stmt->bind_param("ssssdsisi", $date_issuance, $patient_name, $representative_name, $options, $amount_approved, $expiry_date, $user, $activity, $control_number);

    // Execute the query and check for success
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "New Assistance record added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "An error occurred while adding the record."]);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

// Close the connection
$conn->close();
?>