<!-- Modify the PHP section to include the About section -->
<?php 
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not, redirect to login page
    header('Location: login.php');
    exit;    
}

// Retrieve user details from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];

// Include database connection
include '../connection/db.php';

// Fetch names, ratings, and feedback from the feedback table
$sql = "SELECT name, rating, feedback FROM feedback WHERE rating BETWEEN 4 AND 5";
$result = $conn->query($sql);

// Initialize arrays to store names, ratings, and feedback
$names = array();
$ratings = array();
$feedbacks = array();

// Fetch the data and store it in arrays
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $names[] = $row['name'];
        $ratings[] = $row['rating'];
        $feedbacks[] = $row['feedback'];
    }
}
// Fetch turf details from the database
$turfs = array();
$sql = "SELECT id, turf_name, location, size, address, image, time_slot, vacancy_status FROM play_turf";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        $turfs[] = $row;
    }
} else {
    echo "0 results";
}

// Fetch successful turf bookings and their payment status for the logged-in user
$sql = "SELECT b.turf_id, t.turf_name, t.image, b.payment_status, g.qr_code_image_path, b.booking_date, b.start_time, b.end_time, b.price
        FROM turf_booking b 
        INNER JOIN play_turf t ON b.turf_id = t.id 
        INNER JOIN generate_qr g ON b.booking_id = g.booking_id
        WHERE b.user_id = ? AND b.payment_status = 'Success'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$successful_bookings = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $successful_bookings[] = $row;
    }
} else {
    echo "No successful bookings found.";
}

// Fetch turf details from the database
$turfs = array();
$sql = "SELECT id, turf_name, location, size, address, image, time_slot, vacancy_status FROM play_turf";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        $turfs[] = $row;
    }
} else {
    echo "0 results";
}
// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    // Fetch user's name and email from the session
    session_start();
    $name = null;
    $email = null;
    if (isset($_SESSION['username'])) {
        $name = $_SESSION['username'];
    }
    if (isset($_SESSION['email'])) {
        $email = $_SESSION['email'];
    }

    $rating = $_POST['rating'];
    $feedback = $_POST['feedback'];

    // Prepare and execute SQL statement to insert feedback into database
    $stmt = $conn->prepare("INSERT INTO feedback (name, email, rating, feedback) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $name, $email, $rating, $feedback);
    $stmt->execute();

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Redirect back to dashboard or any other page
    header('Location: dashboard.php');
    exit;
}
// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
    <style>
        #my-bookings {
    display: none;
    
}
 /* CSS for big star rating */
 .star-rating {
        font-size: 36px; /* Adjust the size of stars as needed */
    }

    .big-star {
        cursor: pointer;
        color: black; /* Default star color */
        margin-right: 10px; /* Space between stars */
    }

    /* CSS for text input */
input[type="text"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box; /* Ensure padding and border are included in width */
}

/* CSS for submit button */
button[type="submit"] {
    width: 100%;
    padding: 10px;
    background-color: #007bff; /* Button background color */
    color: white; /* Button text color */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s, color 0.3s;
}

button[type="submit"]:hover {
    background-color: #0056b3; /* Darker color on hover */
}

.navbar {
            background-color: #333; /* Background color */
            overflow: hidden;
        }

        .navbar a {
            float: left; /* Float the links to the left */
            display: block; /* Display them as block elements */
            color: white; /* Text color */
            text-align: center; /* Center-align text */
            padding: 14px 20px; /* Padding */
            text-decoration: none; /* Remove underline */
            font-size: 17px; /* Font size */
        }

        .navbar a:hover {
            background-color: #ddd; /* Change background color on hover */
            color: black; /* Change text color on hover */
        }

        .navbar img {
            display: inline-block; /* Make the logo inline with text */
            height: 40px; /* Set the height of the logo */
            margin-right: 20px; /* Add some margin to the right of the logo */
        }

        /* CSS for the feedback form */
form {
    max-width: 400px; /* Adjust the width as needed */
    margin: 0 auto; /* Center the form horizontally */
}

/* CSS for the feedback label */
label {
    display: block; /* Make the label a block element */
    margin-bottom: 8px; /* Add some space below the label */
    font-weight: bold; /* Make the label text bold */
}

/* CSS for the feedback textarea */
textarea {
    width: 100%; /* Take up full width */
    padding: 10px; /* Add some padding */
    margin-bottom: 15px; /* Add some space below the textarea */
    border: 1px solid #ccc; /* Add a border */
    border-radius: 5px; /* Add some border radius */
    box-sizing: border-box; /* Ensure padding and border are included in width */
}

/* CSS for the feedback submit button */
button[type="submit"] {
    width: 100%; /* Take up full width */
    padding: 12px; /* Add some padding */
    background-color: #007bff; /* Button background color */
    color: white; /* Button text color */
    border: none; /* Remove border */
    border-radius: 5px; /* Add some border radius */
    cursor: pointer; /* Add pointer cursor */
    font-size: 16px; /* Set font size */
    transition: background-color 0.3s, color 0.3s; /* Add smooth transition */
}

/* Hover state for the submit button */
button[type="submit"]:hover {
    background-color: #0056b3; /* Darker color on hover */
}

/* The Modal (background) */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
    background-color: #fefefe;
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 20%; /* Could be more or less, depending on screen size */
}

/* The Close Button */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
img {
            display: block; /* Make the image a block element */
            margin: 0 auto; /* Center align image */
            width: 200px;
            height: 200px;
        }
/* Default button style */
.book-now-button {
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
    transition: background-color 0.3s, color 0.3s;
}

/* Style for available button */
.available {
    background-color: green;
}

.available:hover {
    background-color: darkgreen;
}

/* Style for not available button */
.not-available {
    background-color: red;
}

.not-available:hover {
    background-color: darkred;
}

/* Define card container style */
.card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 20px;
        }

        /* Define card style */
        .card {
            width: 300px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Define card hover effect */
        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        /* Define title style */
        .card h3 {
            font-size: 20px;
            color: #333;
            margin-bottom: 10px;
        }

        /* Define text style */
        .card p {
            font-size: 16px;
            color: #666;
            margin-bottom: 5px;
        }

        /* Define star style */
        .stars {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }

        /* Define star icon style */
        .star-icon {
            color: yellow;
            font-size: 24px;
            margin: 0 2px;
            text-shadow: 0 0 1px black; /* Add black stroke */
        }
    </style>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Dashboard</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    
</head>
<body>
<div class="navbar">
    <a><img src="../images/logo.png" alt="Your Logo" style="width: 100px; height: auto;"></a>
    <a href="#home-content">Home</a>
    <a href="#my-bookings">My Bookings</a>
    <a href="#contact">Contact</a>
    <a href="#about">About</a>
    <a href="#feedback">Feedback & Ratings</a>
    <a href="logout2.php">Logout</a> <!-- Add this line for logout -->
</div>



    <div class="user-info">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    </div>
    <div id="home-content">
    <div class="turf-container">
        <!-- Display each turf in a card -->
       <!-- Display each turf in a card -->
<?php foreach ($turfs as $turf): ?>
    <div class="turf-card">
        <img src="<?php echo htmlspecialchars($turf['image']); ?>" alt="Turf Image">
        <div class="turf-info">
            <h3><?php echo htmlspecialchars($turf['turf_name']); ?></h3>
            <p>Location: <?php echo htmlspecialchars($turf['location']); ?></p>
            <p>Size: <?php echo htmlspecialchars($turf['size']); ?></p>
            <p>Address: <?php echo htmlspecialchars($turf['address']); ?></p>
            <p>Time Slot: <?php echo htmlspecialchars($turf['time_slot']); ?></p>
            <p>Vacancy: <?php echo $turf['vacancy_status'] ? 'Available' : 'Not Available'; ?></p>
            <!-- Form submission that passes the turf ID to booking.php -->
            <form action="booking.php" method="get">
                <input type="hidden" name="turf_id" value="<?php echo $turf['id']; ?>">
               <!-- Inside the PHP loop to display turfs --><!-- Inside the PHP loop to display turfs -->
<?php 
    $buttonClass = $turf['vacancy_status'] ? ($turf['vacancy_status'] > 0 ? 'available' : 'not-available') : 'not-available';
    $buttonDisabled = $turf['vacancy_status'] ? '' : 'disabled';
?>
<button type="submit" class="book-now-button <?php echo $buttonClass; ?>" <?php echo $buttonDisabled; ?>>Book Now</button>

            </form>
        </div>
    </div>
<?php endforeach; ?>
    </div>
    </div>
    <div id="my-bookings" style="display:none;">
    <h2>My Successful Bookings</h2>
    <div class="turf-container">
        <?php foreach ($successful_bookings as $booking): ?>
            <div class="turf-card">
                <img src="<?php echo htmlspecialchars($booking['image']); ?>" alt="Turf Image">
                <div class="turf-info">
                    <h3><?php echo htmlspecialchars($booking['turf_name']); ?></h3>
                    <p>Payment Status: <?php echo htmlspecialchars($booking['payment_status']); ?></p>
                    <p>Booking Date: <?php echo htmlspecialchars($booking['booking_date']); ?></p>
                    <p>Start Time: <?php echo htmlspecialchars($booking['start_time']); ?></p>
                    <p>End Time: <?php echo htmlspecialchars($booking['end_time']); ?></p>
                    <p>Price: <?php echo htmlspecialchars($booking['price']); ?></p>
                    <!-- Show Details button -->
                    <button onclick="showDetails(<?php echo $booking['turf_id']; ?>, '<?php echo htmlspecialchars($booking['qr_code_image_path']); ?>')" class="details-button">Show QR</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Booking Details Modal -->
<div id="bookingDetailsModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h1>Booking Confirmation</h1>
        <p>Your booking qr:</p>
       
        <img id="modalQRCode" class="img" src="" alt="Booking QR Code" />
    </div>
</div>

<div id="contactModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Contact Us</h2>
        <p>Email: playarena@gmail.com</p>
        <p>Phone: +91 8689888488</p>
        <!-- Additional contact details can be added here -->
    </div>
</div>

<!-- Add the About section -->
<div id="about" class="modal">
    <div class="modal-content">
    <span class="close" onclick="closeAboutModal()">&times;</span>
        <h2>About Us</h2>
        <p>Welcome to PlayArena! We're passionate about sports and making it easy for you to find and book turf venues. Whether you're playing competitively or just for fun, we've got you covered. Join us and discover the perfect place to play!</p>

        <!-- Additional information about the website can be added here -->
    </div>
</div>
<!-- Feedback & Ratings Modal -->
<div id="feedback" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeFeedbackModal()">&times;</span>
        <h2>Feedback & Ratings</h2>
        <form action="" method="post">
            <label for="rating">Rating:</label>
            <!-- Big star rating selection -->
            <div class="star-rating" onclick="setRating(event)">
                <span class="big-star" data-value="1">★</span>
                <span class="big-star" data-value="2">★</span>
                <span class="big-star" data-value="3">★</span>
                <span class="big-star" data-value="4">★</span>
                <span class="big-star" data-value="5">★</span>
            </div>
            <input type="hidden" name="rating" id="rating" value="0"> <!-- Hidden input to store the selected rating -->
            <!-- End of big star rating selection -->
            <label for="feedback">Your Feedback:</label>
    <textarea name="feedback" id="feedback" rows="4" required placeholder="Enter your feedback here..."></textarea>
    <button type="submit">Submit</button>
        </form>
    </div>
</div>





</body>
<footer>
        <div class="card-container">
            <!-- Display individual ratings in cards -->
            <?php 
                foreach ($ratings as $index => $rating) {
                    echo '<div class="card">';
                    
                    echo '<h3>Rating: ';
                    for ($i = 0; $i < $rating; $i++) {
                        echo '<span class="star-icon">★</span>';
                    }
                    echo '</h3>';
                    
                    echo '<div class="stars">';
                    // Loop to display stars based on the rating
                    
                    echo '</div>';
                    echo '<p><strong>Name:</strong> ' . htmlspecialchars($names[$index]) . '</p>';
                    echo '<p><strong>Feedback:</strong> ' . htmlspecialchars($feedbacks[$index]) . '</p>';
                    echo '</div>';
                }
            ?>
        </div>
    </footer>
</html>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const homeLink = document.querySelector('a[href="#home-content"]');
    const bookingsLink = document.querySelector('a[href="#my-bookings"]');
    const aboutLink = document.querySelector('a[href="#about"]');

    homeLink.addEventListener("click", function() {
        document.getElementById("home-content").style.display = "block"; // Show home content
        document.getElementById("my-bookings").style.display = "none"; // Hide bookings content
    });

    bookingsLink.addEventListener("click", function() {
        document.getElementById("home-content").style.display = "none"; // Hide home content
        document.getElementById("my-bookings").style.display = "block"; // Show bookings content
    });

    aboutLink.addEventListener("click", function(event) {
        event.preventDefault(); // Prevent the default link behavior
        document.getElementById("about").style.display = "block"; // Show the About section
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Function to show booking details
    window.showDetails = function(turfId, qrCodeImagePath) {
    // Fetch booking details from the server using AJAX (if needed)
    // For demonstration, we'll use placeholder data
    // Populate the modal with the fetched details
    // ...
    
    // Set the QR code image
    document.getElementById("modalQRCode").src = qrCodeImagePath;

    // Show the modal
    document.getElementById("bookingDetailsModal").style.display = "block";
};

    // Get the modal
    var modal = document.getElementById("bookingDetailsModal");
    
    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    };

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
});
</script>

<script>
 // Add this JavaScript code
document.addEventListener("DOMContentLoaded", function() {
    const contactLink = document.querySelector('a[href="#contact"]');
    const contactModal = document.getElementById('contactModal');
    const closeContactModal = contactModal.querySelector('.close');

    contactLink.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the default link behavior
        contactModal.style.display = 'block';
    });

    closeContactModal.addEventListener('click', function() {
        contactModal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === contactModal) {
            contactModal.style.display = 'none';
        }
    });
});


    </script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const homeLink = document.querySelector('a[href="#home-content"]');
    const bookingsLink = document.querySelector('a[href="#my-bookings"]');
    const aboutLink = document.querySelector('a[href="#about"]');
    const aboutModal = document.getElementById('about');

    homeLink.addEventListener("click", function() {
        document.getElementById("home-content").style.display = "block"; // Show home content
        document.getElementById("my-bookings").style.display = "none"; // Hide bookings content
    });

    bookingsLink.addEventListener("click", function() {
        document.getElementById("home-content").style.display = "none"; // Hide home content
        document.getElementById("my-bookings").style.display = "block"; // Show bookings content
    });

    aboutLink.addEventListener("click", function(event) {
        event.preventDefault(); // Prevent the default link behavior
        aboutModal.style.display = "block"; // Show the About section
    });
});

function closeAboutModal() {
    const aboutModal = document.getElementById('about');
    aboutModal.style.display = "none";
}
// JavaScript code for handling Feedback & Ratings modal

document.addEventListener("DOMContentLoaded", function() {
    const feedbackLink = document.querySelector('a[href="#feedback"]');
    const feedbackModal = document.getElementById('feedback');
    const closeFeedbackModal = feedbackModal.querySelector('.close');

    feedbackLink.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the default link behavior
        feedbackModal.style.display = 'block';
    });

    closeFeedbackModal.addEventListener('click', function() {
        feedbackModal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === feedbackModal) {
            feedbackModal.style.display = 'none';
        }
    });
});


</script>

<script>
    function setRating(event) {
        var stars = document.querySelectorAll('.big-star'); // Updated selector to target .big-star
        var ratingInput = document.getElementById('rating');
        var selectedRating = parseInt(event.target.getAttribute('data-value'));
        
        // Update the hidden input field value
        ratingInput.value = selectedRating;

        // Change the color of stars
        for (var i = 0; i < stars.length; i++) {
            if (i < selectedRating) {
                stars[i].style.color = 'yellow'; // Set the color of clicked and previous stars to yellow
            } else {
                stars[i].style.color = 'black'; // Set the color of remaining stars to black
            }
        }
    }
</script>
