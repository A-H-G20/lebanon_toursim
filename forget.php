<?php
session_start();
require 'config.php'; // database connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
  $email = trim($_POST['email']);

  // Check if email exists
  $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if ($user) {
    $reset_code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);

    // Save reset code to database (you must have a column 'reset_code' in users table)
    $update = $conn->prepare("UPDATE users SET reset_code = ? WHERE id = ?");
    $update->bind_param("si", $reset_code, $user['id']);
    $update->execute();

    // Send email
    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      include 'email.php'; // Include your email configuration file
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;

      $mail->setFrom('email@gmail.com', 'Lebanon Tours Password Recovery');
      $mail->addAddress($email, $user['name']);
      $mail->isHTML(true);
      $mail->Subject = 'Password Reset Code';
      $mail->Body = "
                <div style='font-family: Arial; text-align: center; padding:20px;'>
                    <h2 style='color:#119DA4;'>Reset Your Password</h2>
                    <p>Hi <b>{$user['name']}</b>,</p>
                    <p>Use this code to reset your password:</p>
                    <h1 style='color:#ff5a5f;'>{$reset_code}</h1>
                    <p>If you didn't request a reset, ignore this email.</p>
                </div>
            ";

      $mail->send();

      $_SESSION['reset_email'] = $email;
      $success = "Reset code sent to your email.";
    } catch (Exception $e) {
      $error = "Error sending email: " . $mail->ErrorInfo;
    }
  } else {
    $error = "Email address not found.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Password Recovery</title>
  <link rel="stylesheet" href="css/forgetPassPage.css">
</head>

<body>

  <?php if (!empty($error)): ?>
    <div class="error-message"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <script>
      setTimeout(() => {
        showStep(2);
      }, 1000);
    </script>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="container step active" id="step1">
      <h2>Forgot Password?</h2>
      <div class="input-group">
        <input type="email" name="email" placeholder="Enter your email" required />
      </div>
      <button type="submit">Send Verification Code</button>
    </div>
  </form>

  <div class="container step" id="step2">
    <h2>Verification Code</h2>
    <form method="POST" action="verify_code.php">
      <div class="code-inputs">
        <input type="text" name="code[]" maxlength="1" required />
        <input type="text" name="code[]" maxlength="1" required />
        <input type="text" name="code[]" maxlength="1" required />
        <input type="text" name="code[]" maxlength="1" required />
        <input type="text" name="code[]" maxlength="1" required />
        <input type="text" name="code[]" maxlength="1" required />
      </div>
      <button type="submit">Verify Code</button>
    </form>
  </div>

  <div class="container step" id="step3">
    <h2>Set New Password</h2>
    <form method="POST" action="reset_password.php">
      <div class="input-group">
        <input type="password" name="new_password" placeholder="New Password" required />
      </div>
      <div class="input-group">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required />
      </div>
      <button type="submit">Reset Password</button>
    </form>
  </div>

  <script>
    function showStep(stepNumber) {
      document.querySelectorAll(".step").forEach((step) => {
        step.classList.remove("active");
      });
      document.getElementById(`step${stepNumber}`).classList.add("active");
    }

    const codeInputs = document.querySelectorAll(".code-inputs input");
    codeInputs.forEach((input, index) => {
      input.addEventListener("input", (e) => {
        if (e.target.value.length === 1 && index < codeInputs.length - 1) {
          codeInputs[index + 1].focus();
        }
      });
    });
  </script>

</body>

</html>