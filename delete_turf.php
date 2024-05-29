<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['turf_id'])) {
    $conn = new mysqli('localhost', 'root', '', 'playarena');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $turf_id = $_POST['turf_id'];

    // Delete turf from the database
    $delete_query = "DELETE FROM play_turf WHERE id = '$turf_id'";
    if ($conn->query($delete_query) === TRUE) {
        echo "Turf deleted successfully";
    } else {
        echo "Error deleting turf: " . $conn->error;
    }

    $conn->close();
} else {
    echo "Invalid request";
}
?>
