<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'config.php';

// Get token from request header or JS fetch call (for now, from query or cookie/localStorage simulation)
$token = $_GET['token'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: No token provided.']);
    exit;
}

$sql = "SELECT * FROM users WHERE token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Now the user is authenticated, you can continue rendering the page normally
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Invalid token.']);
}
?>
