<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'playarena');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['turf_id'])) {
    $turf_id = $_GET['turf_id'];
    
    // Retrieve turf details
    $query = "SELECT * FROM play_turf WHERE id = '$turf_id'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $turf_name = $row['turf_name'];
        $location = $row['location'];
        $time_slot = $row['time_slot'];
        $size = $row['size'];
        $address = $row['address'];
        $image = $row['image'];
        $vacancy_status = $row['vacancy_status'];
        // Add more fields as needed
    } else {
        echo "Turf not found";
        exit;
    }
} else {
    echo "Turf ID not provided";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $new_turf_name = $_POST['turf_name'];
    $new_location = $_POST['location'];
    $new_time_slot = $_POST['time_slot'];
    $new_size = $_POST['size'];
    $new_address = $_POST['address'];
    $new_vacancy_status = $_POST['vacancy_status'];
    
    // Check if a file is uploaded
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_path = '../images/' . $image_name;
        
        // Move uploaded file to images directory
        if(move_uploaded_file($image_tmp, $image_path)) {
            // Update image path in the database
            $update_query = "UPDATE play_turf SET turf_name = '$new_turf_name', location = '$new_location', time_slot = '$new_time_slot', size = '$new_size', address = '$new_address', image = '$image_path', vacancy_status = '$new_vacancy_status' WHERE id = '$turf_id'";
            if ($conn->query($update_query) === TRUE) {
                echo "Turf details updated successfully";
            } else {
                echo "Error updating turf details: " . $conn->error;
            }
        } else {
            echo "Failed to move uploaded file";
        }
    } else {
        // No file uploaded, update other details without changing the image path
        $update_query = "UPDATE play_turf SET turf_name = '$new_turf_name', location = '$new_location', time_slot = '$new_time_slot', size = '$new_size', address = '$new_address', vacancy_status = '$new_vacancy_status' WHERE id = '$turf_id'";
        if ($conn->query($update_query) === TRUE) {
            echo "Turf details updated successfully";
        } else {
            echo "Error updating turf details: " . $conn->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Turf - PlayArena</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="../css/admin_turf_edit.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h1 {
            text-align: center;
        }

        form {
            width: 50%;
            margin: 0 auto;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: #fff;
            cursor: pointer;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            float: right;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Edit Turf</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?turf_id=' . $turf_id; ?>" method="post" enctype="multipart/form-data">
        <label for="turf_name">Turf Name:</label>
        <input type="text" id="turf_name" name="turf_name" value="<?php echo $turf_name; ?>"><br><br>
        
        <label for="location">Location:</label>
        <input type="text" id="location" name="location" value="<?php echo $location; ?>"><br><br>
        
        <label for="time_slot">Time Slot:</label>
        <input type="text" id="time_slot" name="time_slot" value="<?php echo $time_slot; ?>"><br><br>

        <label for="size">Size:</label>
        <input type="text" id="size" name="size" value="<?php echo $size; ?>"><br><br>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" value="<?php echo $address; ?>"><br><br>

        <label for="image">Image:</label>
        <input type="file" id="image" name="image"><br><br>

        <label for="vacancy_status">Vacancy Status:</label>
        <input type="text" id="vacancy_status" name="vacancy_status" value="<?php echo $vacancy_status; ?>"><br><br>
        <!-- Add more fields as needed -->

        <input type="submit" value="Submit">
    </form>
</body>
</html>
