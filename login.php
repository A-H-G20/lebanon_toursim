<?php
session_start(); // Start the session to store user data after login
require_once 'config.php'; // Assuming you have a config.php file for database connection

// Handle login when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username']; // Username (email or phone number)
    $password = $_POST['password']; // Password entered by the user

    // Validate input
    if (empty($username) || empty($password)) {
        echo "Please enter both username and password.";
        exit;
    }

    // Check if the username is email or phone number
    $query = "SELECT id, email, phone_number, password, role FROM users WHERE (email = ? OR phone_number= ?) AND verified = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on user role
            if ($user['role'] == 'admin') {
                header('Location: admin/index.php');
            } elseif ($user['role'] == 'user') {
                header('Location: index.php');
            } elseif ($user['role'] == 'operator') {
                header('Location: tour_oprator/index.php');
            } else {
                echo "Invalid role.";
            }
            exit; // Stop further execution
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that email or phone number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link rel="stylesheet" href="css/login.css" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
</head>

<body>
    <main class="login-container">
        <section class="form-section">
            <header class="login-header">
                <h1>Log In</h1>
                <p>Welcome back! Log in to your account.</p>
            </header>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="username">Email Or PhoneNumber</label>
                    <input class="orangecolor" type="text" id="username" name="username" required autocomplete="username" />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input">
                        <input class="yellowcolor" type="password" id="password" name="password" required autocomplete="current-password" />
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fa fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <label class="container">
                            <input type="checkbox" class="item-checkbox" id="select-all">
                            <div class="checkmark"></div>
                        </label>
                        <p style="color: #2a6559;">Remember Me</p>
                    </div>
                    <a href="forget.php" class="forgot-password">Forgot password?</a>
                </div>
                <button type="submit">Sign In</button>

                <footer class="register-link">
                    <p>Don't have an account? <a href="register.php">Register now</a></p>
                </footer>
            </form>
        </section>
    </main>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.querySelector('.password-toggle i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>