<?php
require_once 'db.php';

$date = date('Y-m-d');

// Delete attendance for today
$stmt = $conn->prepare("DELETE FROM attendance WHERE date_marked = ?");
$stmt->bind_param("s", $date);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Attendance reset for today"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to reset attendance"]);
}

$conn->close();
?>
