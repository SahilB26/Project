<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST'&& isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $mysqli = new mysqli('localhost', 'root', '', 'playarena');

    if ($mysqli->connect_error) {
        die('Connection failed: ' . $mysqli->connect_error);
    }

    $stmt = $mysqli->prepare("SELECT id, password FROM play_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // After password verification
if (password_verify($password, $hashed_password)) {
    // Fetch user's name and email
    $stmt = $mysqli->prepare("SELECT username, email FROM play_users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($username, $email);
    $stmt->fetch();

    // Store user details in session
    $_SESSION['user_id'] = $id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    // Redirect to dashboard.php
    header('Location: dashboard.php');
            exit; // Make sure no further code is executed after redirection
        } else {
            // Invalid username or password, show alert message
            echo '<script>alert("Invalid username or password.");</script>';
        }
    } else {
        // Invalid username or password, show alert message
        echo '<script>alert("Invalid username or password.");</script>';
    }
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
        <head>
    <meta charset="UTF-8">
    <title> Login</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/loginreg.css">
</head>

</head>
<body class="body">    

    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form action="" method="POST" id="signup-form">
                <h1>Create Account</h1>
                <div class="social-container">
                    <a href="https://www.facebook.com/tejas.bhojane.75?mibextid=ZbWKwL" class="social"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.google.com" class="social"><i class="fab fa-google-plus-g"></i></a>
                    <a href="https://www.linkedin.com" class="social"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <span>or use your email for registration</span>
                <input type="text" name="username" placeholder="Username" required minlength="4" maxlength="20" pattern="[a-zA-Z0-9]+" title="Username must contain only letters and numbers (no special characters)." />
                <input type="email" name="email" placeholder="Email" required />
                <input type="tel" name="phone_number" placeholder="Phone Number" required pattern="[0-9]{10}" title="Phone number must be 10 digits." />
                <input type="password" name="password" placeholder="Password" required minlength="8" maxlength="20" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$" title="Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number." />
                <button type="submit" name="register">Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in-container">
            <form action="" method="POST" id="signin-form">
                <h1>Sign in</h1>
                <div class="social-container">
                <a href="https://www.facebook.com/tejas.bhojane.75?mibextid=ZbWKwL" class="social"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.google.com" class="social"><i class="fab fa-google-plus-g"></i></a>
                    <a href="https://www.linkedin.com" class="social"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <span>or use your account</span>
                <input type="text" name="username" placeholder="Username" required />
                <input type="password" name="password" placeholder="Password" required minlength="8" maxlength="20" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$" title="Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number." />
                
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
                    <h1>Hello, Friend!</h1>
                    <p>Enter your personal details and start journey with us</p>
                    <button class="ghost" id="signUp">Sign Up</button>
                    <p></p>
                    <button class="ghost" id="admin">Admin Login</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script>
        // Get the Admin Login button by its ID
        const adminButton = document.getElementById("admin");

        // Add event listener for the click event
        adminButton.addEventListener("click", function() {
            // Redirect to admin_login.php
            window.location.href = "../pages/admin_login.php";
        });

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
        const signupForm = document.getElementById('signup-form');
        const signinForm = document.getElementById('signin-form');

        signupForm.addEventListener('submit', function(event) {
            if (!validateSignupForm()) {
                event.preventDefault();
            }
        });

        signinForm.addEventListener('submit', function(event) {
            if (!validateSigninForm()) {
                event.preventDefault();
            }
        });

        function validateSignupForm() {
            const usernameInput = document.querySelector('input[name="username"]');
            const emailInput = document.querySelector('input[name="email"]');
            const phoneNumberInput = document.querySelector('input[name="phone_number"]');
            const passwordInput = document.querySelector('input[name="password"]');

            if (!isValidUsername(usernameInput.value)) {
                alert("Username must contain only letters and numbers (no special characters).");
                return false;
            }

            if (!isValidPhoneNumber(phoneNumberInput.value)) {
                alert("Phone number must be 10 digits.");
                return false;
            }

            if (!isValidPassword(passwordInput.value)) {
                alert("Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.");
                return false;
            }

            return true;
        }

        function validateSigninForm() {
            const usernameInput = document.querySelector('input[name="username"]');
            const passwordInput = document.querySelector('input[name="password"]');

            if (!isValidUsername(usernameInput.value)) {
                alert("Invalid username.");
                return false;
            }

            if (!isValidPassword(passwordInput.value)) {
                alert("Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.");
                return false;
            }

            return true;
        }

        

        function isValidPhoneNumber(phoneNumber) {
            const regex = /^[0-9]{10}$/;
            return regex.test(phoneNumber);
        }

        function isValidPassword(password) {
            // Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number
            const regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$/;
            return regex.test(password);
        }
    </script>

</body>
</html>
