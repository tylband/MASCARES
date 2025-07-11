<?php
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: http://192.168.100.100");
header("Content-Type: application/json");

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit();
    }

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $token = bin2hex(random_bytes(32));

            $update = $conn->prepare("UPDATE users SET token = ? WHERE username = ?");
            $update->bind_param("ss", $token, $username);
            $update->execute();

        echo json_encode([
    'success' => true,
    'message' => 'Login successful!',
    'token' => $token,
    'redirect_url' => 'https://infosys.malaybalaycity.gov.ph/beta/assistancemayors/dashboard.php'
]);

        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
