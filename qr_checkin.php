<?php
require_once 'db.php';

// Read the current valid token from file
$token_file = __DIR__ . '/../current_token.txt';

$data = json_decode(file_get_contents("php://input"), true);
$computer_number = $data['computer_number'] ?? '';
$submitted_token = $data['token'] ?? '';

// Validate inputs
if (empty($computer_number)) {
    echo json_encode(["success" => false, "message" => "Please enter your computer number."]);
    exit();
}

if (empty($submitted_token)) {
    echo json_encode(["success" => false, "message" => "Invalid QR code. Please scan the latest code."]);
    exit();
}

// Validate the token against the stored token
if (file_exists($token_file)) {
    $stored_data = json_decode(file_get_contents($token_file), true);
    $valid_token = $stored_data['token'] ?? '';
    
    if ($submitted_token !== $valid_token) {
        echo json_encode(["success" => false, "message" => "This QR code has expired. Please scan the new QR code from the projector."]);
        exit();
    }
} else {
    // If no token file exists, allow it (fallback — first run)
}

// Must be exactly 10 digits
if (!preg_match('/^\d{10}$/', $computer_number)) {
    echo json_encode(["success" => false, "message" => "Computer number must be exactly 10 digits."]);
    exit();
}

// Find student by computer number
$stmt = $conn->prepare("SELECT id, name, computer_number FROM students WHERE computer_number = ? LIMIT 1");
$stmt->bind_param("s", $computer_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Computer number not found. Please enter your correct computer number."]);
    exit();
}

$student = $result->fetch_assoc();
$student_id = $student['id'];
$student_name = $student['name'];
$date = date('Y-m-d');
$timestamp = round(microtime(true) * 1000);

// Check if already marked present today
$check_stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date_marked = ?");
$check_stmt->bind_param("is", $student_id, $date);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "$student_name, you are already marked as present today."]);
    exit();
}

// Mark attendance
$insert_stmt = $conn->prepare("INSERT INTO attendance (student_id, scan_timestamp, date_marked) VALUES (?, ?, ?)");
$insert_stmt->bind_param("ids", $student_id, $timestamp, $date);

if ($insert_stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Welcome, $student_name! You are now marked as present."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to mark attendance. Please try again."]);
}

$conn->close();
?>
