<?php
session_start();

$loginError = ""; // Initialize login error message

// Handle admin login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $mysqli = new mysqli('localhost', 'root', '', 'playarena');

    if ($mysqli->connect_error) {
        die('Connection failed: ' . $mysqli->connect_error);
    }

    $stmt = $mysqli->prepare("SELECT id, password FROM play_admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // After password verification
        if (password_verify($password, $hashed_password)) {
            // Set session variables
            $_SESSION['admin_id'] = $id;
            // Redirect to admin dashboard
            header('Location: ../pages/admin_dashboard.php');
            exit; // Make sure no further code is executed after redirection
        } else {
            $loginError = "Invalid username or password.";
        }
    } else {
        $loginError = "Invalid username or password.";
    }

    $stmt->close();
    $mysqli->close();
}
?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];

    // Create a connection
    $mysqli = new mysqli('localhost', 'root', '', 'playarena');

    // Check connection
    if ($mysqli->connect_error) {
        die('Connection failed: ' . $mysqli->connect_error);
    }

    // Prepare statement to avoid SQL injection
    $stmt = $mysqli->prepare("INSERT INTO play_users (username, email, phone_number, password) VALUES (?, ?, ?, ?)");
    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("ssss", $username, $email, $phone_number, $hashed_password);
    
    // Execute the statement
    // After successful registration
if ($stmt->execute()) {
    // Fetch the last inserted ID
    $user_id = $mysqli->insert_id;

    // Store user details in session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    // Redirect to dashboard.php
    header('Location: dashboard.php');
    exit;
} else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/adminlogreg.css">
</head>

</head>
<body class="body">    

    <div class="container" id="container">
	<div class="form-container sign-up-container">
	<form action="" method="POST">
    <h1>Create Account</h1>
    <div class="social-container">
    <a href="https://www.facebook.com/tejas.bhojane.75?mibextid=ZbWKwL" class="social"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.google.com" class="social"><i class="fab fa-google-plus-g"></i></a>
                    <a href="https://www.linkedin.com" class="social"><i class="fab fa-linkedin-in"></i></a>
</div>

    <span>or use your email for registration</span>
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="phone_number" placeholder="Phone Number" required>
    <input type="password" name="password" placeholder="Password" required>
    <button name="register">Sign Up</button>
</form>
	</div>
	<div class="form-container sign-in-container">
    <form action="" method="POST">
			<h1>Sign in</h1>
			<div class="social-container">
            <a href="https://www.facebook.com/tejas.bhojane.75?mibextid=ZbWKwL" class="social"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.google.com" class="social"><i class="fab fa-google-plus-g"></i></a>
                    <a href="https://www.linkedin.com" class="social"><i class="fab fa-linkedin-in"></i></a>
			</div>
			<span>or use your account</span>
			<input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
			
			<button type="submit" name="login">Login</button>
		</form>
	</div>
	<div class="overlay-container">
		<div class="overlay">
			<div class="overlay-panel overlay-left">
				<h1>Welcome Back!</h1>
				<p>To keep connected with us please login with your personal info</p>
				<button class="ghost" id="signIn">Sign In</button>
			</div>
			<div class="overlay-panel overlay-right">
    <h1>Not An Admin!</h1>
    <p>Click To Login As User</p>
    <button class="ghost" id="userLogin">User Login</button>
</div>

		</div>
	</div>
</div>


</body>
</html>
<script>



// Existing code for the sign-up/sign-in buttons
const signUpButton = document.getElementById("signUp");
const signInButton = document.getElementById("signIn");
const container = document.getElementById("container");

signUpButton.addEventListener("click", () => {
    container.classList.add("right-panel-active");
});

signInButton.addEventListener("click", () => {
    container.classList.remove("right-panel-active");
});
</script>
<script>
    const userLoginButton = document.getElementById("userLogin");

userLoginButton.addEventListener("click", () => {
    window.location.href = "login.php"; // Redirect to login.php
});

</script>
<script>
// Check if the login error message is not empty, then display the popup
if ("<?php echo $loginError; ?>" !== "") {
    alert("<?php echo $loginError; ?>");
}
</script>
