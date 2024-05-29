<?php
// Include session start and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Create connection
$conn = new mysqli('localhost', 'root', '', 'playarena');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define variables to hold booking details and total price
$booking_details = '';
$total_price = 0;

// Check if form is submitted
if (isset($_GET['turf_id']) && isset($_GET['booking_date'])) {
    $turf_id = $_GET['turf_id'];
    $booking_date = $_GET['booking_date'];

    // Query to fetch booking details for the selected turf and date with payment status "success"
    $query = "SELECT b.booking_id, b.user_id, b.booking_date, b.start_time, b.end_time, b.payment_status, b.price, u.username, u.email, u.phone_number
              FROM turf_booking b 
              INNER JOIN play_users u ON b.user_id = u.id
              WHERE b.turf_id = '$turf_id' 
              AND b.booking_date = '$booking_date' 
              AND b.payment_status = 'success'";
    $result = $conn->query($query);

    // Check if there are any bookings for the selected turf and date
    if ($result->num_rows > 0) {
        // Build the HTML table for booking details
        $booking_details .= "<table border='1'>
                <tr>
                    <th>Booking ID</th>
                    <th>User ID</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Booking Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Payment Status</th>
                    <th>Price</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            $booking_details .= "<tr>
                    <td>" . $row['booking_id'] . "</td>
                    <td>" . $row['user_id'] . "</td>
                    <td>" . $row['username'] . "</td>
                    <td>" . $row['email'] . "</td>
                    <td>" . $row['phone_number'] . "</td>
                    <td>" . $row['booking_date'] . "</td>
                    <td>" . $row['start_time'] . "</td>
                    <td>" . $row['end_time'] . "</td>
                    <td>" . $row['payment_status'] . "</td>
                    <td>" . $row['price'] . "</td>
                </tr>";
            // Add the price to the total
            $total_price += $row['price'];
        }
        $booking_details .= "</table>";
    } else {
        $booking_details = "No bookings found for Turf ID: $turf_id on Date: $booking_date";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PlayArena</title>
    <link rel="stylesheet" href="../css/admindashboard.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Custom CSS styles */
/* Custom styles for the dropdown */
.turf-dropdown {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 100%;
    max-width: 300px; /* Adjust width as needed */
    background-color: #fff; /* Dropdown background color */
    color: #333; /* Text color */
    appearance: none; /* Remove default dropdown arrow */
    -webkit-appearance: none; /* Remove default dropdown arrow for Safari */
    background-image: url('data:image/svg+xml;utf8,<svg fill="#000000" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18px" height="18px"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>'); /* Custom dropdown arrow */
    background-repeat: no-repeat;
    background-position: right 10px center;
    cursor: pointer;
}

/* Hover effect */
.turf-dropdown:hover {
    border-color: #3498db;
}

/* Focus effect */
.turf-dropdown:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.5); /* Example box shadow on focus */
}

        /* List view */
        .nav-links {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .nav-links li {
            margin: 0 10px;
        }

        /* Date picker */
        .datepicker {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            margin-right: 10px;
        }

        .check-turf-button {
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
        }

        .check-turf-button:hover {
            background-color: #2ecc71;
        }
        .add-turf-button {
    background-color: #3498db; /* Blue background color */
    color: #fff; /* Text color */
    border: none;
    border-radius: 20px; /* Rounded edges */
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
}

.add-turf-button:hover {
    background-color: #2ecc71; /* Green hover color */
}


        /* Additional styles for booking details */
        .booking-details table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .booking-details th,
        .booking-details td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .booking-details th {
            background-color: #f2f2f2;
            color: #333;
        }

        .booking-details tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .booking-details tr:hover {
            background-color: #ddd;
        }

        /* Responsive styles */
        .container {
            width: 90%;
            margin: auto;
        }
        .turf-details {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }

        .turf-card {
            margin: 10px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            text-align: center;
        }

        .turf-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .edit-link {
            color: #3498db;
            text-decoration: none;
            margin-top: 10px;
            display: inline-block;
        }

        .edit-link:hover {
            text-decoration: underline;
        }

        @media screen and (max-width: 600px) {
            .nav-links {
                flex-direction: column;
                align-items: center;
            }

            .nav-links li {
                margin: 10px 0;
            }

            .main-content {
                padding: 20px;
            }

            .datepicker {
                width: calc(100% - 80px); /* Adjust width to fit the container */
                margin-bottom: 10px;
            }

            .check-turf-button {
                width: 100%; /* Make the button full-width */
            }

            .booking-details th,
            .booking-details td {
                padding: 5px;
                font-size: 12px;
            }
        }
        .feedback-dropdown {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 100%;
    max-width: 300px;
    background-color: #fff;
    color: #333;
    appearance: none;
    -webkit-appearance: none;
    background-image: url('data:image/svg+xml;utf8,<svg fill="#000000" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18px" height="18px"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>');
    background-repeat: no-repeat;
    background-position: right 10px center;
    cursor: pointer;
    margin-bottom: 10px;
}

.feedback-dropdown:hover {
    border-color: #3498db;
}

.feedback-dropdown:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.5);
}

.delete-feedback-button {
    background-color: #e74c3c;
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-top: 10px;
    cursor: pointer;
}

.delete-feedback-button:hover {
    background-color: #c0392b;
}

    </style>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize Flatpickr with calendar view
        flatpickr('.datepicker', {

            enable: [
                function (selectedDate) {
                    return selectedDate <= new Date(); // Disable future dates
                }
            ],
            dateFormat: 'Y-m-d', // Date format (e.g., YYYY-MM-DD)
            minDate: 'today', // Minimum selectable date (today)
            // Add more options as needed
        });
    </script>
</head>
<body>

<nav>
    <div class="logo">PlayArena Admin</div>
    <ul class="nav-links">
        <li><a href="#">Bookings</a></li>
        <li><a href="#">Payments</a></li>
        <li><a href="#">Turf Viewing</a></li>
        <li><a href="../pages/logout.php">Logout</a></li>
    </ul>
</nav>
<div class="main-content">
    <h1>Welcome, Admin!</h1>
    <div class="container">
        <div class="section bookings">
            <h2>Bookings</h2>
            <form action="admin_dashboard.php" method="get">
                <!-- Dropdown to select turf ID -->
                <?php
                // Query to fetch turf IDs and names from the database
                $query = "SELECT * FROM play_turf";
                $result = $conn->query($query);
                ?>
                <!-- Dropdown to select turf ID -->
                <select name="turf_id" class="turf-dropdown">
                    <?php
                    // Output dropdown options dynamically from database
                    $options = array();
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Add each row to the options array
                            $options[] = $row;
                        }
                        // Output options from the array
                        foreach ($options as $row) {
                            // Check if it's the first row, then set it as the default selected option
                            $selected = ($row === $options[0]) ? 'selected' : '';
                            echo "<option value='" . $row['id'] . "' $selected>" . $row['turf_name'] . "</option>";
                        }
                    }
                    ?>
                </select>

                <!-- Date picker input -->
                <input type="date" name="booking_date" class="datepicker" placeholder="Select booking date" required>

                <!-- Submit button -->
                <input type="submit" class="check-turf-button" value="Check Turf Bookings">
            </form>
            <!-- Booking details section -->
            <div class="booking-details">
                <?php
                // Output booking details if available
                echo $booking_details;
                ?>
            </div>
        </div>
    </div>
    <br>

    <div class="container">
        <div class="section payments">
            <h2>Payments</h2>
            <?php
            // Display the total price
            echo "<p>Total Price: $total_price</p>";
            ?>
        </div>
    </div>
    <br>
    <div class="container">
        <div class="section turf-viewing">
            <h2>Turf Viewing</h2>
            <div class="turf-details">
                <?php
                // Query to fetch all turfs from the database
                $query = "SELECT * FROM play_turf";
                $result = $conn->query($query);

                // Check if there are any turfs
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='turf-card'>";
                        echo "<img src='" . $row['image'] . "' alt='" . $row['turf_name'] . "' class='turf-image'>";
                        echo "<h3>" . $row['turf_name'] . "</h3>";
                        echo "<p>Location: " . $row['location'] . "</p>";
                       
                        echo "<a href='edit_turf.php?turf_id=" . $row['id'] . "' class='edit-link'>Edit Details</a>";
                        echo "</div>";
                    }
                } else {
                    echo "No turfs found";
                }
                ?>
            </div>
        </div>
    </div>
    <br></br>
    <div class="container">
    <div class="section add-turf">
        <h2>Add Turf</h2>
        
        <button onclick="window.location.href='../pages/admin_turf_update.php'" class="add-turf-button">Add New Turf</button>
    </div>
</div>
</div>

<div class="container">
    <div class="section feedback">
        <h2>Feedback</h2>
        <form action="delete_feedback.php" method="post">
            <!-- Dropdown to select feedback to delete -->
            <select name="feedback_id" class="feedback-dropdown" required>
                <option value="" disabled selected>Select Feedback to Delete</option>
                <?php
                // Query to fetch feedback from the database
                $query = "SELECT id, name, feedback FROM feedback";
                $result = $conn->query($query);

                // Check if there are any feedback
                if ($result->num_rows > 0) {
                    // Output dropdown options dynamically from database
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . " - " . $row['feedback'] . "</option>";
                    }
                } else {
                    echo "<option value='' disabled>No Feedback Found</option>";
                }
                ?>
            </select>

            <!-- Submit button to delete feedback -->
            <input type="submit" class="delete-feedback-button" value="Delete Feedback">
        </form>
    </div>
</div>


<footer>
    <p>&copy; 2024 PlayArena. All rights reserved.</p>
</footer>

<script src="admin_dashboard.js"></script>
<script>
    flatpickr('.datepicker', {

        dateFormat: 'Y-m-d', // Date format (e.g., YYYY-MM-DD)
        // Minimum selectable date (today)
        // Add more options as needed
    });
</script>
</body>
</html>
