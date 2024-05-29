    <?php
    session_start();
    include '../connection/db.php';

    // Check if turf_id is set in the URL
    if (isset($_GET['turf_id'])) {
        $turf_id = $_GET['turf_id'];
        $_SESSION['turf_id'] = $turf_id;
        // Fetch the selected turf details from the database
        $stmt = $conn->prepare("SELECT * FROM play_turf WHERE id = ?");
        $stmt->bind_param("i", $turf_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $turf = $result->fetch_assoc();
            // Now you can display the time slot options for this turf
        } else {
            echo "Turf not found.";
            exit;
        }
    } else {
        echo "No turf ID provided.";
        exit;
    }

    // Check if the form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book'])) {
        // Retrieve form data
        $turf_id = $_POST['turf_id'];
        $user_id = $_SESSION['user_id']; // Assuming the user's ID is stored in the session
        $booking_date = $_POST['booking_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $total_price = $_POST['total_price']; // Retrieve the total price from the form

        // Server-side validation to ensure start time is not the same as end time
        if ($start_time === $end_time) {
            echo "Start time and end time cannot be the same. Please choose a different end time.";
            exit; // Stop script execution
        }

        // Check for overlapping bookings and successful payments
$stmt = $conn->prepare("SELECT * FROM turf_booking WHERE turf_id = ? AND booking_date = ? AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?)) AND payment_status = 'success'");
$stmt->bind_param("isssss", $turf_id, $booking_date, $start_time, $start_time, $end_time, $end_time);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Display alert using JavaScript
    echo "<script>alert('The selected time slot is already booked. Please choose a different time slot.');";
    echo "window.location.href = '../pages/dashboard.php'; // Redirect to the same page";
    echo "</script>";
    exit;
}


        // Prepare the SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO turf_booking (turf_id, user_id, booking_date, start_time, end_time, price) VALUES (?, ?, ?, ?, ?, ?)");

        // Bind parameters and execute the statement
        $stmt->bind_param("iisssi", $turf_id, $user_id, $booking_date, $start_time, $end_time, $total_price);

        if ($stmt->execute()) {
            // Store total price in session
            $_SESSION['booking_date'] = $booking_date;
            $_SESSION['start_time'] = $start_time;
            $_SESSION['end_time'] = $end_time;
            $_SESSION['total_price'] = $total_price;
            // Redirect to razorpay_api.php
            header("Location: ../razorpay/razorpay_api.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
            // Handle error
        }
        

        // Close the statement
        $stmt->close();
    }


    // Close the connection
    $conn->close();
    ?>

<?php
// Mapping of time slots to prices based on the provided image
$time_slot_prices = array(
    "06:00 AM" => "1800/-",
    "07:00 AM" => "1800/-",
    "08:00 AM" => "1800/-",
    "09:00 AM" => "1500/-",
    "10:00 AM" => "1500/-",
    "11:00 AM" => "1500/-",
    "12:00 PM" => "1500/-",
    "01:00 PM" => "1500/-",
    "02:00 PM" => "1500/-",
    "03:00 PM" => "1500/-",
    "04:00 PM" => "1500/-",
    "05:00 PM" => "1500/-",
    "06:00 PM" => "1800/-",
    "07:00 PM" => "1800/-",
    "08:00 PM" => "1800/-",
    "09:00 PM" => "1800/-",
    "10:00 PM" => "1800/-",
    "11:00 PM" => "1800/-",
    "12:00 AM" => "1800/-", // Assuming this is midnight
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <title>Book Turf</title>
    
   
    <link rel="icon" type="image/png" href="../images/logo.png">
    </head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .booking-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 20px auto;
            text-align: center; /* Center align the content */
        }
        h1 {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        select, button {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            background-color: #5cb85c;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: #4cae4c;
        }
        img {
            max-width: 100%; /* Ensure the image fits within the container */
            height: auto; /* Maintain aspect ratio */
            margin-bottom: 20px; /* Space between the image and the form */
        }
    </style>
     <script>
    // Function to validate the time slots before submitting the form
    function validateTimeSlots() {
        var startTime = document.getElementById('start_time').value;
        var endTime = document.getElementById('end_time').value;
        var errorMessage = document.getElementById('error_message');
        
        // Check if start time is the same as end time
        if (startTime === endTime) {
            errorMessage.textContent = 'Start time and end time cannot be the same. Please choose a different end time.';
            return false; // Prevent form submission
        }

        // Clear any previous error message
        errorMessage.textContent = '';
        
        return true; // Allow form submission
    }
</script>


<body>
    <div class="booking-container">
        <h1>Book Turf: <?php echo htmlspecialchars($turf['turf_name']); ?></h1>
        <!-- Display the turf image -->
        <img src="<?php echo htmlspecialchars($turf['image']); ?>" alt="Turf Image">
        <!-- Display booking options for the selected turf -->
        <form action="#" method="post" onsubmit="return validateTimeSlots()">
        <div class="form-group">
    <label for="booking_date">Booking Date:</label>
    <!-- Dynamically set the min attribute to today's date -->
    <input type="date" id="booking_date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>">
</div>
<div class="form-group">
    <label for="start_time">Start Time:</label>
    <select name="start_time" id="start_time" required onchange="updatePrice()">
        <?php foreach ($time_slot_prices as $time => $price): ?>
            <option value="<?php echo $time; ?>"><?php echo $time ; ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label for="end_time">End Time:</label>
    <select name="end_time" id="end_time" required onchange="updatePrice()">
        <?php foreach ($time_slot_prices as $time => $price): ?>
            <option value="<?php echo $time; ?>"><?php echo $time ; ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div id="price_display">Total Price: </div>

            <input type="hidden" name="turf_id" value="<?php echo $turf['id']; ?>">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            <!-- Add this hidden input field to store the total price -->
<input type="hidden" name="total_price" id="total_price" value="">

            <button type="submit" name="book">Book Now</button>
        </form>
    </div>
    <div id="error_message" style="color: red;"></div>

</body>
</html>
<script>
    document.getElementById('booking_date').addEventListener('change', function() {
        var selectedDate = new Date(this.value);
        var today = new Date();
        today.setHours(0, 0, 0, 0); // Remove time part
        
        if (selectedDate < today) {
            alert('Please select a valid date. Previous dates are not allowed.');
            this.value = ''; // Reset the date input
        }
    });
</script>
<script>
    var prices = {
    "06:00": 1800,
    "07:00": 1800,
    "08:00": 1800,
    "09:00": 1500,
    "10:00": 1500,
    "11:00": 1500,
    "12:00": 1500,
    "13:00": 1500,
    "14:00": 1500,
    "15:00": 1500,
    "16:00": 1500,
    "17:00": 1500,
    "18:00": 1800,
    "19:00": 1800,
    "20:00": 1800,
    "21:00": 1800,
    "22:00": 1800,
    "23:00": 1800,
    "00:00": 1800,
};
function updatePrice() {
    var startTimeSelect = document.getElementById('start_time');
    var endTimeSelect = document.getElementById('end_time');
    var startTime = startTimeSelect.value;
    var endTime = endTimeSelect.value;

    // Convert times to 24-hour format for comparison
    var startHour = parseInt(startTime.split(':')[0]);
    var endHour = parseInt(endTime.split(':')[0]);
    if (endTime === "00:00") {
        endHour = 24; // Adjust for midnight
    }

    // Calculate the total price
    var totalPrice = 0;
    for (var time in prices) {
        var hour = parseInt(time.split(':')[0]);
        if (hour >= startHour && hour < endHour) {
            totalPrice += prices[time];
        }
    }

    // Update the hidden input field value with the total price
    document.getElementById('total_price').value = totalPrice;
    
    // Assuming you want to display the price somewhere on the page
    var priceDisplay = document.getElementById('price_display');
    priceDisplay.textContent = "Total Price: " + totalPrice + "/-";
}

</script>
