<?php
// Receives the current token from the admin dashboard and stores it
$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? '';

if (empty($token)) {
    echo json_encode(["success" => false, "message" => "Token is required"]);
    exit();
}

$token_file = __DIR__ . '/../current_token.txt';

$token_data = json_encode([
    "token" => $token,
    "updated_at" => time()
]);

if (file_put_contents($token_file, $token_data)) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save token"]);
}
?>
