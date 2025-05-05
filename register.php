<?php
// Include database connection (ensure this file exists and contains correct DB connection)
include 'config.php';

// Start session for error handling
session_start();

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Initialize variables
$username = $email = $password = $confirmPassword = '';
$response = ['success' => false, 'message' => '', 'redirect_url' => ''];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = sanitizeInput($_POST['password']);
    $confirmPassword = sanitizeInput($_POST['confirm-password']);

    // Validate username (ensure it's not empty)
    if (empty($username)) {
        $response['message'] = "Username is required.";
        echo json_encode($response);
        exit();
    }

    // Validate email (ensure it's not empty and in correct format)
    if (empty($email)) {
        $response['message'] = "Email is required.";
        echo json_encode($response);
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format.";
        echo json_encode($response);
        exit();
    }

    // Validate password (ensure it's not empty and meets minimum length)
    if (empty($password)) {
        $response['message'] = "Password is required.";
        echo json_encode($response);
        exit();
    } elseif (strlen($password) < 6) {
        $response['message'] = "Password must be at least 6 characters.";
        echo json_encode($response);
        exit();
    }

    // Validate confirm password (ensure it matches the password)
    if (empty($confirmPassword)) {
        $response['message'] = "Please confirm your password.";
        echo json_encode($response);
        exit();
    } elseif ($password !== $confirmPassword) {
        $response['message'] = "Passwords do not match.";
        echo json_encode($response);
        exit();
    }

    // Check if the username or email already exists in the database
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If username or email already exists, show error
        $response['message'] = "Username or email is already taken.";
        echo json_encode($response);
        exit();
    }

    // Hash the password before storing it
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into the database
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        // Registration success, set the response
        $response['success'] = true;
        $response['message'] = "Registration successful! Please log in.";
        $response['redirect_url'] = "login.php"; // Redirect to login page
        echo json_encode($response);
        exit();
    } else {
        $response['message'] = "Error: Could not register the user.";
        echo json_encode($response);
        exit();
    }
}
?>
