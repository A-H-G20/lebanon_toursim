<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password</title>
    <link href="photos/logo.png" rel="icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link rel="stylesheet" href="./css/signIn.css" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Righteous&family=Secular+One&display=swap');
        .error-message { color: red; margin: 10px 0; text-align: center; }
        .success-message { color: green; margin: 10px 0; text-align: center; }
    </style>
</head>
<body>

<header class="small-header">
    <div class="nav__logo">
        <img src="Photos/logo.png" height="60px" width="100px" alt="Logo">
    </div>
</header>

<main class="login-container">
    <section class="form-section">
        <header class="login-header">
            <p>Reset Your Password</p>
        </header>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-input">
                    <input
                        class="yellowcolor"
                        type="password"
                        id="new_password"
                        name="new_password"
                        required
                        autocomplete="new-password"
                    />
                    <span class="password-toggle" onclick="togglePassword('new_password')">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="password-input">
                    <input
                        class="yellowcolor"
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        required
                        autocomplete="new-password"
                    />
                    <span class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
            </div>

            <button type="submit">Reset Password</button>
        </form>
    </section>
</main>

<script>
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const eyeIcon = passwordInput.nextElementSibling.querySelector('i');

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
<?php
session_start();
require 'config.php'; // Correct file name for connection

// Check if email is in session from verification page
if (!isset($_SESSION['reset_email'])) {
    $_SESSION['error_message'] = "Please verify your email first.";
    header("Location: forgetPass.php");
    exit();
}

$reset_email = $_SESSION['reset_email'];
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validation
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $reset_email);

        if ($stmt->execute()) {
            unset($_SESSION['reset_email']);
            $_SESSION['success_message'] = "Password reset successfully. Please log in.";
            header("Location: signIn.php");
            exit();
        } else {
            $error_message = "Something went wrong. Please try again.";
        }
    }
}
?>
