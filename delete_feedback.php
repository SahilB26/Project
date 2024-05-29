<?php
// Include session start and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Check if the feedback ID is set and not empty
if (isset($_POST['feedback_id']) && !empty($_POST['feedback_id'])) {
    // Sanitize the input to prevent SQL injection
    $feedback_id = $_POST['feedback_id'];

    // Create connection
    $conn = new mysqli('localhost', 'root', '', 'playarena');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to delete feedback from the database
    $query = "DELETE FROM feedback WHERE id = ?";
    $stmt = $conn->prepare($query);

    // Bind parameters
    $stmt->bind_param("i", $feedback_id);

    // Execute the statement
    if ($stmt->execute()) {
        // Feedback deleted successfully
        echo "<script>alert('Feedback deleted successfully');</script>";
        header('Location: admin_dashboard.php'); // Redirect back to the admin dashboard
        exit;
    } else {
        // Failed to delete feedback
        echo "<script>alert('Failed to delete feedback');</script>";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Feedback ID not provided or empty
    echo "<script>alert('Feedback ID not provided');</script>";
}

// Redirect back to the admin dashboard if the deletion failed
header('Location: admin_dashboard.php');
exit;
?>
