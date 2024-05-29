
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center; /* Center align content */
        }
        h1 {
            color: #333;
        }
        p {
            margin: 10px 0; /* Adjust margin */
            text-align: center; /* Center align text */
        }
        .bold {
            font-weight: bold;
        }
        img {
            display: block; /* Make the image a block element */
            margin: 0 auto; /* Center align image */
            width: 200px;
            height: 200px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Booking Confirmation</h1>
        <?php
        session_start();
        // Include database connection
        include '../connection/db.php';
        require_once('../phpqrcode/qrlib.php'); // Adjust the path as necessary

        
        // Retrieve session values
        $booking_date = $_SESSION['booking_date'];
        $start_time = $_SESSION['start_time'];
        $end_time = $_SESSION['end_time'];
        $turf_id = $_SESSION['turf_id'];
        $user_id = $_SESSION['user_id'];
        $booking_id = $_SESSION['booking_id'];

        // Query to retrieve turf details
        $turf_query = "SELECT * FROM play_turf WHERE id = $turf_id";
        $turf_result = $conn->query($turf_query);
        $turf_row = $turf_result->fetch_assoc();

        // Query to retrieve user details
        $user_query = "SELECT * FROM play_users WHERE id = $user_id";
        $user_result = $conn->query($user_query);
        $user_row = $user_result->fetch_assoc();
        $total_price = $_SESSION['total_price'];

        // Generate QR code data
$qrData = "Turf Name: " . $turf_row['turf_name'] . "\n";
$qrData .= "Booking ID: " . $booking_id . "\n";
$qrData .= "User Name: " . $user_row['username'] . "\n";
$qrData .= "Booking Date: " . $booking_date . "\n";
$qrData .= "Start Time: " . $start_time . "\n";
$qrData .= "End Time: " . $end_time . "\n";
$qrData .= "Price: Rs." . $total_price . "/-\n";
$qrCodeFilePath = '../payment/booking_qr_' . $booking_id . '.png';
// Output the QR code directly to the browser
QRcode::png($qrData, $qrCodeFilePath, QR_ECLEVEL_L, 10);
// Prepare an INSERT statement
// Prepare a SELECT statement to check if the record exists
$checkStmt = $conn->prepare("SELECT COUNT(*) FROM generate_qr WHERE booking_id = ?");
$checkStmt->bind_param("i", $booking_id);
$checkStmt->execute();
$checkStmt->bind_result($count);
$checkStmt->fetch();
$checkStmt->close();

if ($count > 0) {
    // Record exists
    echo "<p>A record with this booking ID already exists.</p>";
} else {
    // No record exists, proceed with insertion
    $stmt = $conn->prepare("INSERT INTO generate_qr (turf_name, booking_id, user_name, booking_date, start_time, end_time, price, qr_code_image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissssss", $turf_row['turf_name'], $booking_id, $user_row['username'], $booking_date, $start_time, $end_time, $total_price, $qrCodeFilePath);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<p>QR code details saved successfully.</p>";
    } else {
        echo "<p>Error saving QR code details: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Start output buffering
 // Adjust the path for the src attribute


// Display the image

        ?>
        
        <p>Your booking details:</p>
        <p><span class="bold">Turf Name:</span> <?php echo $turf_row['turf_name']; ?></p>
        <p><span class="bold">Booking ID:</span> <?php echo $booking_id; ?></p>
        <p><span class="bold">User Name:</span> <?php echo $user_row['username']; ?></p>
        <p><span class="bold">Booking Date:</span> <?php echo $booking_date; ?></p>
        <p><span class="bold">Start Time:</span> <?php echo $start_time; ?></p>
        <p><span class="bold">End Time:</span> <?php echo $end_time; ?></p>
        <p><span class="bold">Price:</span> Rs.<?php echo $total_price; ?>/-</p>
        <!-- You can add more details about the booking here -->
        <img src="<?php echo $qrCodeFilePath; ?>" alt="Booking QR Code" />
    </div>
</body>
</html>
