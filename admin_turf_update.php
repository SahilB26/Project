<?php
include '../connection/db.php'; // Include database connection

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $turf_name = $_POST['turf_name'];
    $location = $_POST['location'];
    $time_slot = $_POST['time_slot'];
    $size = $_POST['size'];
    $address = $_POST['address'];
    $vacancy_status = $_POST['vacancy_status'];

    // Initialize variable for image path
    $image = "";

    // Handle file upload
    $target_dir = "../images/"; // Adjust the path as necessary
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;

    // You can add file validation checks here (e.g., file size, file type)
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo "The file ". htmlspecialchars( basename( $_FILES["image"]["name"])). " has been uploaded.";
        $image = $target_file; // Use the path of the uploaded file
    } else {
        echo "Sorry, there was an error uploading your file.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {   
        // SQL to insert new turf details
        $sql = "INSERT INTO play_turf (turf_name, location, time_slot, size, address, image, vacancy_status) VALUES (?, ?, ?, ?, ?, ?, ?)";

        // Prepare the statement to prevent SQL injection
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("ssssssi", $turf_name, $location, $time_slot, $size, $address, $image, $vacancy_status);

            // Execute the query
            if ($stmt->execute()) {
                echo "New turf details added successfully";
            } else {
                echo "Error: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }

    // Close the connection
    $conn->close();
}
?>

<html>
<head>
    <meta charset="UTF-8">
    <title>Add Turf Details</title>
    <link rel="stylesheet" href="../css/loginreg.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
</head>
<body>
<form action="#" method="post" enctype="multipart/form-data">
    <input type="text" name="turf_name" placeholder="Turf Name" required>
    <input type="text" name="location" placeholder="Location" required>
    <input type="text" name="time_slot" placeholder="Time Slot" required>
    <input type="text" name="size" placeholder="Size" required>
    <input type="text" name="address" placeholder="Address" required>
    <input type="file" name="image" required>
    <select name="vacancy_status">
        <option value="1">Available</option>
        <option value="0">Not Available</option>
    </select>
    <button type="submit">Add Turf Details</button>
</form>
</body>
</html>