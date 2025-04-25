<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$conn = new mysqli("localhost", "root", "", "lebanon_toursim");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $role = 'user';
    $verification_code = bin2hex(random_bytes(16));

    if (empty($name) || empty($email) || empty($password) || empty($cpassword) || empty($phone_number) || empty($gender)) {
        $error = "All fields are required.";
    } elseif ($password !== $cpassword) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone_number, gender, role, verification_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $hashed_password, $phone_number, $gender, $role, $verification_code);

        if ($stmt->execute()) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ahmadghosen20@gmail.com'; // Replace with your Gmail
                $mail->Password = 'bbievwnemblpxuqt'; // Replace with your Gmail App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('ahmadghosen20@gmail.com', 'Lebanon Tourism');
                $mail->addAddress($email, $name);
                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email';
                $mail->Body = "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Email Verification</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #f4f4f9;
                            margin: 0;
                            padding: 0;
                        }
                        .email-container {
                            width: 100%;
                            max-width: 600px;
                            margin: 0 auto;
                            background-color: #ffffff;
                            border-radius: 8px;
                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                            padding: 30px;
                            text-align: center;
                        }
                        .email-header {
                            font-size: 24px;
                            font-weight: bold;
                            color: #333;
                            margin-bottom: 20px;
                        }
                        .email-body {
                            font-size: 16px;
                            color: #555;
                            margin-bottom: 30px;
                        }
                        .verify-button {
                            display: inline-block;
                            padding: 10px 20px;
                            font-size: 16px;
                            color: #ffffff;
                            background-color: #ff6f61;
                            border-radius: 5px;
                            text-decoration: none;
                            margin-top: 20px;
                        }
                        .footer {
                            font-size: 14px;
                            color: #888;
                            margin-top: 30px;
                        }
                    </style>
                </head>
                <body>
                    <div class='email-container'>
                        <div class='email-header'>Welcome to Lebanon Tourism!</div>
                        <div class='email-body'>
                            <p>Thank you for registering with us. To verify your email address and complete the registration, please click the button below:</p>
                            <a href='http://localhost/lebanon_toursim/verify.php?code=$verification_code' class='verify-button'>Verify Email</a>
                        </div>
                        <div class='footer'>
                            <p>If you did not create an account, please ignore this email.</p>
                        </div>
                    </div>
                </body>
                </html>
                ";
                

                $mail->send();
                $success = "Registration successful. Please check your email to verify your account.";
            } catch (Exception $e) {
                $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Signup</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <link rel="stylesheet" href="css/login.css" />
</head>
<body>

<main class="login-container">
  <section class="form-section">
    <header class="login-header">
      <h1>Sign Up</h1>
      <p>Welcome to our website! Please sign up to create an account.</p>
    </header>

    <?php if (!empty($error)) : ?>
      <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if (!empty($success)) : ?>
      <p style="color: green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required />
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required />
      </div>
      <div class="form-group">
        <label for="phone_number">Phone Number</label>
        <input type="text" id="phone_number" name="phone_number" required />
      </div>
      <div class="form-group">
        <label for="gender">Gender</label>
        <select id="gender" name="gender" required>
          <option value="">Select Gender</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />
      </div>
      <div class="form-group">
        <label for="cpassword">Confirm Password</label>
        <input type="password" id="cpassword" name="cpassword" required />
      </div>

      <button type="submit">Sign Up</button>

      <footer class="register-link">
        <p>Already have an account? <a href="login.php">Login now</a></p>
      </footer>
    </form>
  </section>
</main>

</body>
</html>
