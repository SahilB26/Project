<?php
session_start();
include '../connection/db.php';

// Retrieve payment response from Razorpay
$response = json_decode(file_get_contents('php://input'));

// Retrieve session values
$booking_date = $_SESSION['booking_date'];
$start_time = $_SESSION['start_time'];
$end_time = $_SESSION['end_time'];
$turf_id = $_SESSION['turf_id'];
$user_id = $_SESSION['user_id'];

// Update payment status in the database
$stmt = $conn->prepare("UPDATE turf_booking SET payment_status = 'success' WHERE turf_id = ? AND user_id = ? AND booking_date = ? AND start_time = ? AND end_time = ?");
$stmt->bind_param("iisss", $turf_id, $user_id, $booking_date, $start_time, $end_time);

if ($stmt->execute()) {
    // Retrieve booking_id from the database
    $booking_id_query = "SELECT booking_id FROM turf_booking WHERE turf_id = ? AND user_id = ? AND booking_date = ? AND start_time = ? AND end_time = ?";
    $booking_id_stmt = $conn->prepare($booking_id_query);
    $booking_id_stmt->bind_param("iisss", $turf_id, $user_id, $booking_date, $start_time, $end_time);
    $booking_id_stmt->execute();
    $booking_id_result = $booking_id_stmt->get_result();
    $booking_id_row = $booking_id_result->fetch_assoc();

    // Add booking_id to session
    $_SESSION['booking_id'] = $booking_id_row['booking_id'];

    // Close statement
    $booking_id_stmt->close();

    echo "Payment successful.";
} else {
    echo "Error updating payment status: " . $conn->error;
}

// Close database connection
$stmt->close();
$conn->close();
?>
