<?php
$host = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS attendance_app";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("attendance_app");

// Create students table
$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    computer_number VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL
)";
if ($conn->query($sql) === TRUE) {
    echo "Table students created successfully.<br>";
} else {
    echo "Error creating table students: " . $conn->error . "<br>";
}

// Create attendance table
$sql = "CREATE TABLE IF NOT EXISTS attendance (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT(6) UNSIGNED NOT NULL,
    scan_timestamp BIGINT NOT NULL,
    date_marked DATE NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Table attendance created successfully.<br>";
} else {
    echo "Error creating table attendance: " . $conn->error . "<br>";
}

// Populate students if empty
$result = $conn->query("SELECT COUNT(*) AS cnt FROM students");
$row = $result->fetch_assoc();
if ($row['cnt'] == 0) {
    $firstNames = ['James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth', 'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica', 'Thomas', 'Sarah', 'Charles', 'Karen', 'Oliver', 'Emma', 'Noah', 'Olivia', 'Liam', 'Ava'];
    $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];
    
    $stmt = $conn->prepare("INSERT INTO students (student_id, computer_number, name) VALUES (?, ?, ?)");
    
    $total_students = 137;
    for ($i = 1; $i <= $total_students; $i++) {
        $stu_id = "STU" . str_pad($i, 4, "0", STR_PAD_LEFT);
        $comp_num = "2023" . str_pad($i, 6, "0", STR_PAD_LEFT);
        $fName = $firstNames[array_rand($firstNames)];
        $lName = $lastNames[array_rand($lastNames)];
        $name = $fName . " " . $lName;
        
        $stmt->bind_param("sss", $stu_id, $comp_num, $name);
        $stmt->execute();
    }
    echo "Inserted $total_students mock students.<br>";
    $stmt->close();
} else {
    echo "Students table already populated.<br>";
}

$conn->close();
echo "Setup complete.";
?>
