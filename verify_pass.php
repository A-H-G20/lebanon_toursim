<?php
session_start();
include 'connection.php'; // Database connection file

// Check for email in several possible sources
$userEmail = '';

// 1. Check if email is in session from previous page
if (isset($_SESSION['reset_email'])) {
    $userEmail = $_SESSION['reset_email'];
    unset($_SESSION['reset_email']); // Clear after use
}

// 2. Check if email is passed as GET parameter
if (empty($userEmail) && isset($_GET['email'])) {
    $userEmail = $_GET['email'];
}

// 3. Check if email is in POST data
if (empty($userEmail) && isset($_POST['email'])) {
    $userEmail = $_POST['email'];
}

// If no email found, redirect or show error
if (empty($userEmail)) {
    $_SESSION['error_message'] = "Email parameter is missing.";
    header("Location: forgetPass.php");
    exit();
}

if (isset($_POST['verify_pass_reset'])) {
    $enteredCode = $_POST['code1'] . $_POST['code2'] . $_POST['code3'] . $_POST['code4'] . $_POST['code5'] . $_POST['code6'];

    // Retrieve the reset code and its expiration time from the database
    $sql = "SELECT reset_code, reset_code_expires FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $userEmail, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $storedResetCode = $result['reset_code'];
        $resetCodeExpires = $result['reset_code_expires'];
        $currentTime = date("Y-m-d H:i:s");

        // Check if reset code is valid and not expired
        if ($storedResetCode === null) {
            $error_message = "No reset code found. Please request a new password reset.";
        } elseif ($currentTime > $resetCodeExpires) {
            $error_message = "Reset code has expired. Please request a new password reset.";
        } elseif ($enteredCode == $storedResetCode) {
            // Code is correct and not expired
            // Nullify the reset code and its expiration time
            $updateSql = "UPDATE users SET reset_code = NULL, reset_code_expires = NULL WHERE email = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindValue(1, $userEmail, PDO::PARAM_STR);
            $updateStmt->execute();

            // Store email in session for password reset page
            $_SESSION['reset_email'] = $userEmail;

            // Redirect to the password reset page
            header("Location: reset_password.php");
            exit();
        } else {
            $error_message = "Invalid verification code. Please try again.";
        }
    } else {
        $error_message = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Callkiki</title>
  <link rel="stylesheet" href="./css/verify.css" />
  <link href="photos/logo.png" rel="icon" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Righteous&family=Secular+One&display=swap');
    .error-message { color: red; margin: 10px 0; text-align: center; }
  </style>
</head>
<body>
  <div class="opt-form">
    <div class="header-form">
      <div class="auth-icon">
        <i class="fa-regular fa-bell"></i>
      </div>
      <h4>Password Reset Verification</h4>
      <p>Please enter the verification code sent to your email.</p>
    </div>
    
    <?php 
    // Display error message if exists
    if (isset($error_message)) {
        echo "<div class='error-message'>" . htmlspecialchars($error_message) . "</div>";
    }
    ?>
    
    <form action="" method="POST">
      <input type="hidden" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" />
      <div class="auth-pin-wrap">
        <input type="number" name="code1" class="code-input" required />
        <input type="number" name="code2" class="code-input" required />
        <input type="number" name="code3" class="code-input" required />
        <input type="number" name="code4" class="code-input" required />
        <input type="number" name="code5" class="code-input" required />
        <input type="number" name="code6" class="code-input" required />
      </div>
      <div class="btn-wrap">
        <button type="submit" name="verify_pass_reset">Confirm</button>
      </div>
    </form>
  </div>
  <script>
    const inputs = document.querySelectorAll(".code-input");

    inputs.forEach((input, index) => {
      input.addEventListener("input", (e) => {
        const value = e.target.value;
        if (value.length > 1) e.target.value = value.slice(0, 1); // Only allow 1 digit
        if (value && index < inputs.length - 1) inputs[index + 1].focus(); // Focus next
      });

      input.addEventListener("keydown", (e) => {
        if (e.key === "Backspace" && !e.target.value && index > 0) inputs[index - 1].focus();
      });
    });
  </script>
</body>
</html>