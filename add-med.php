<?php
// Set headers for CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

include 'config.php';

// Get token from POST or GET
$token = $_POST['token'] ?? $_GET['token'] ?? '';

if (empty($token)) {
    echo json_encode(["status" => "error", "message" => "Token is missing."]);
    exit();
}

// Validate token
$stmt = $conn->prepare("SELECT username FROM users WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode(["status" => "error", "message" => "Invalid or expired token."]);
    exit();
}

$userRow = $result->fetch_assoc();
$user = $userRow['username']; // Authenticated user

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $date_issuance = filter_input(INPUT_POST, 'date_issuance', FILTER_SANITIZE_STRING);
    $patient_name = filter_input(INPUT_POST, 'patient_name', FILTER_SANITIZE_STRING);
    $representative_name = filter_input(INPUT_POST, 'name_representative', FILTER_SANITIZE_STRING);
    $options = filter_input(INPUT_POST, 'options', FILTER_SANITIZE_STRING);
    $amount_approved = filter_input(INPUT_POST, 'amount_approved', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $expiry_date = filter_input(INPUT_POST, 'expiry_date', FILTER_SANITIZE_STRING);
    $activity = "add";

    // Medical Assistance Details
    $medical_details_array = [];
    if (!empty($_POST['medical_assistance']) && is_array($_POST['medical_assistance'])) {
        foreach ($_POST['medical_assistance'] as $index => $assistance) {
            $medical_details_array[] = [
                'assistance_details' => trim($assistance),
                'hospital' => trim($_POST['hosp_name_medical'][$index] ?? ''),
                'treatment' => trim($_POST['treatment_type'][$index] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
            ];
        }
    }
    $medical_details_json = json_encode($medical_details_array, JSON_UNESCAPED_UNICODE);

    // Medicine Assistance Details
    $medicine_details_array = [];
    if (!empty($_POST['medicine_assistance']) && is_array($_POST['medicine_assistance'])) {
        foreach ($_POST['medicine_assistance'] as $index => $assistance) {
            $medicine_details_array[] = [
                'assistance_details' => trim($assistance),
                'hospital_name' => trim($_POST['hosp_name_medicine'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
            ];
        }
    }
    $medicine_details_json = json_encode($medicine_details_array, JSON_UNESCAPED_UNICODE);

    // Burial Assistance Details
    $burial_details_array = [];
    if (!empty($_POST['relationship_bur']) && is_array($_POST['relationship_bur'])) {
        foreach ($_POST['relationship_bur'] as $index => $assistance) {
            $burial_details_array[] = [
                'assistance_details' => trim($assistance),
                'funeral' => trim($_POST['funeral_bur'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
            ];
        }
    }
    $burial_details_json = json_encode($burial_details_array, JSON_UNESCAPED_UNICODE);

    // Educational Assistance Details
    $educational_details_array = [];
    if (!empty($_POST['educational_assistance']) && is_array($_POST['educational_assistance'])) {
        foreach ($_POST['educational_assistance'] as $index => $assistance) {
            $educational_details_array[] = [
                'assistance_details' => trim($assistance),
                'school' => trim($_POST['name_school_educ'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
            ];
        }
    }
    $educational_details_json = json_encode($educational_details_array, JSON_UNESCAPED_UNICODE);

    // Validate required fields
    if (empty($patient_name) || empty($amount_approved)) {
        echo json_encode(["status" => "error", "message" => "Patient name and amount approved are required."]);
        exit();
    }

    // Generate unique control number
    $control_number = date('Y') . '-' . rand(1000, 9999);

    // Check for duplicate patient name in the last 3 months
    $stmt = $conn->prepare("SELECT * FROM assistance WHERE patient_name = ? AND date_issuance >= DATE_SUB(NOW(), INTERVAL 3 MONTH)");
    $stmt->bind_param("s", $patient_name);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "The patient's name has been used within the last 3 months."]);
        exit();
    }
    $stmt->close();

    // Insert new record
    $stmt = $conn->prepare("
        INSERT INTO assistance (
            control_number, date_issuance, patient_name, representative_name,
            options, amount_approved, expiry_date,
            medical_details, medicine_details, burial_details, educational_details,
            user, activity
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Failed to prepare SQL insert."]);
        exit();
    }

    $stmt->bind_param(
        "sssssdsssssss",
        $control_number,
        $date_issuance,
        $patient_name,
        $representative_name,
        $options,
        $amount_approved,
        $expiry_date,
        $medical_details_json,
        $medicine_details_json,
        $burial_details_json,
        $educational_details_json,
        $user,
        $activity
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "New Assistance record added successfully!", "control_number" => $control_number]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to insert data."]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

$conn->close();
?>
