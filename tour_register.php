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
            <label for="name">Company Name</label>
            <input type="text" id="cname" name="cname" required />
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
    <?php
session_start();
require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $company_name = trim($_POST['cname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_number']);
    $password = trim($_POST['password']);
    $cpassword = trim($_POST['cpassword']);

    if ($password !== $cpassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if email, phone, or company name already exists
        $checkStmt = $conn->prepare("
            SELECT 
                (SELECT COUNT(*) FROM users WHERE email = ?) as email_exists,
                (SELECT COUNT(*) FROM users WHERE phone_number = ?) as phone_exists,
                (SELECT COUNT(*) FROM tour_operator WHERE company_name = ?) as company_exists
        ");
        $checkStmt->bind_param("sss", $email, $phone, $company_name);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result()->fetch_assoc();

        if ($checkResult['email_exists'] > 0) {
            $error = "This email is already registered.";
        } elseif ($checkResult['phone_exists'] > 0) {
            $error = "This phone number is already registered.";
        } elseif ($checkResult['company_exists'] > 0) {
            $error = "This company name is already registered.";
        } else {
            // Everything unique, proceed
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("
                INSERT INTO users (name, email, role, gender, phone_number, password, verified) 
                VALUES (?, ?, 'operator', NULL, ?, ?, 1)
            ");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashedPassword);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;

                $stmt2 = $conn->prepare("
                    INSERT INTO tour_operator (user_id, company_name, business_phone, status) 
                    VALUES (?, ?, ?, 'pending')
                ");
                $stmt2->bind_param("iss", $user_id, $company_name, $phone);

                if ($stmt2->execute()) {
                    // Send Welcome Email
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'ahmadghosen20@gmail.com';
                        $mail->Password = 'bbievwnemblpxuqt';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->setFrom('ahmadghosen20@gmail.com', 'Lebanon Tours Admin');
                        $mail->addAddress($email, $name);
                        $mail->isHTML(true);
                        $mail->Subject = 'Welcome to Lebanon Tours - Account Pending Approval';
                        $mail->Body = "
                            <div style='font-family: Arial, sans-serif; text-align: center; padding:20px;'>
                                <h2 style='color:#119DA4;'>Welcome to Lebanon Tours!</h2>
                                <p>Hi <b>{$name}</b>,</p>
                                <p>Thank you for signing up. Your registration has been received.</p>
                                <p><b>Please note:</b> Your account is currently <span style='color:#ff5a5f;'>pending admin approval</span>.</p>
                                <p>We will notify you as soon as your account gets approved.</p>
                                <br>
                                <p>Best regards,<br>Lebanon Tours Team</p>
                            </div>
                        ";
                        $mail->send();

                        // All successful, redirect
                        header("Location: index.php");
                        exit();
                    } catch (Exception $e) {
                        $error = "Registered successfully but failed to send email.";
                    }
                } else {
                    $error = "Failed to insert into tour_operator table.";
                }
            } else {
                $error = "Failed to insert into users table.";
            }
        }
    }
}
?>
