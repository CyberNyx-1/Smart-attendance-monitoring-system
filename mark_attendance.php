<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$identifier = $data['identifier'] ?? '';
$type = $data['type'] ?? 'qr';

// Always return 200 with success/error in JSON body
if (empty($identifier)) {
    echo json_encode(["success" => false, "message" => "Identifier is required"]);
    exit();
}

// Find the student by student_id OR computer_number
$stmt = $conn->prepare("SELECT id, name, computer_number FROM students WHERE student_id = ? OR computer_number = ? LIMIT 1");
$stmt->bind_param("ss", $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    if ($type === 'manual') {
        echo json_encode(["success" => false, "message" => "Please enter your correct computer number"]);
    } else {
        echo json_encode(["success" => false, "message" => "Student not found"]);
    }
    exit();
}

$student = $result->fetch_assoc();
$student_internal_id = $student['id'];
$student_name = $student['name'];
$date = date('Y-m-d');
$timestamp = round(microtime(true) * 1000);

// Check if already present today
$check_stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date_marked = ?");
$check_stmt->bind_param("is", $student_internal_id, $date);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    if ($type === 'manual') {
        echo json_encode(["success" => false, "message" => "This computer number has already been entered"]);
    } else {
        echo json_encode(["success" => false, "message" => "Already marked present today"]);
    }
    exit();
}

// Mark attendance
$insert_stmt = $conn->prepare("INSERT INTO attendance (student_id, scan_timestamp, date_marked) VALUES (?, ?, ?)");
$insert_stmt->bind_param("ids", $student_internal_id, $timestamp, $date);

if ($insert_stmt->execute()) {
    if ($type === 'manual') {
        echo json_encode(["success" => true, "message" => "$student_name is present"]);
    } else {
        echo json_encode(["success" => true, "message" => "Attendance marked successfully"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Failed to mark attendance"]);
}

$conn->close();
?>
