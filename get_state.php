<?php
require_once 'db.php';

$date = date('Y-m-d');

// Get all students
$students_result = $conn->query("SELECT * FROM students");
$students = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
}

// Get today's attendance
$stmt = $conn->prepare("SELECT a.scan_timestamp, a.student_id, s.name, s.student_id as display_id FROM attendance a JOIN students s ON a.student_id = s.id WHERE a.date_marked = ? ORDER BY a.scan_timestamp DESC");
$stmt->bind_param("s", $date);
$stmt->execute();
$attendance_result = $stmt->get_result();

$recent_scans = [];
$present_student_ids = [];

while ($row = $attendance_result->fetch_assoc()) {
    $present_student_ids[] = $row['student_id'];
    $recent_scans[] = [
        'id' => $row['display_id'],
        'name' => $row['name'],
        'timestamp' => $row['scan_timestamp']
    ];
}

// Merge present status back into students array
foreach ($students as &$student) {
    $student['present'] = in_array($student['id'], $present_student_ids);
    // Find timestamp if present
    $student['timestamp'] = null;
    foreach ($recent_scans as $scan) {
        if ($scan['id'] === $student['student_id']) {
            $student['timestamp'] = $scan['timestamp'];
            break;
        }
    }
}

echo json_encode([
    "students" => $students,
    "recentScans" => $recent_scans
]);

$conn->close();
?>
