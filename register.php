<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

include 'config.php';
session_start();

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$username = $email = $password = $confirmPassword = '';
$response = ['success' => false, 'message' => '', 'redirect_url' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = sanitizeInput($_POST['password']);
    $confirmPassword = sanitizeInput($_POST['confirm-password']);

    if (empty($username)) {
        $response['message'] = "Username is required.";
        echo json_encode($response);
        exit();
    }

    if (empty($email)) {
        $response['message'] = "Email is required.";
        echo json_encode($response);
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format.";
        echo json_encode($response);
        exit();
    }

    if (empty($password)) {
        $response['message'] = "Password is required.";
        echo json_encode($response);
        exit();
    } elseif (strlen($password) < 6) {
        $response['message'] = "Password must be at least 6 characters.";
        echo json_encode($response);
        exit();
    }

    if (empty($confirmPassword)) {
        $response['message'] = "Please confirm your password.";
        echo json_encode($response);
        exit();
    } elseif ($password !== $confirmPassword) {
        $response['message'] = "Passwords do not match.";
        echo json_encode($response);
        exit();
    }

    $checkSql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $response['message'] = "Username or email is already taken.";
        echo json_encode($response);
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32)); // Generate secure token

    $insertSql = "INSERT INTO users (username, email, password, token) VALUES (?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ssss", $username, $email, $hashedPassword, $token);

    if ($insertStmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Registration successful!";
        $response['token'] = $token;
        $response['redirect_url'] = "dashboard.php?token=" . $token;
    } else {
        $response['message'] = "Error: Could not register the user.";
    }

    echo json_encode($response);
    exit();
}
?>
