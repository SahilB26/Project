<?php
// Database connection
$mysqli = new mysqli('localhost', 'root', '', 'playarena');

// Check connection
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Define the admin username and password
$username = 'playarena';
$password = 'Playarena@2024';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare SQL statement to insert into play_admin table
$stmt = $mysqli->prepare("INSERT INTO play_admin (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashed_password);

// Execute the statement
if ($stmt->execute()) {
    echo "Admin account created successfully.";
} else {
    echo "Error: " . $stmt->error;
}

// Close statement and database connection
$stmt->close();
$mysqli->close();
?>
