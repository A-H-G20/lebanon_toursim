<?php
include 'config.php'; // Include your database connection file

$code = $_GET['code'] ?? '';

if (empty($code)) {
    die("Verification code missing.");
}

// Check if user exists with that verification code
$stmt = $conn->prepare("SELECT id, verified, verified_at FROM users WHERE verification_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or expired verification code.");
} else {
    $user = $result->fetch_assoc();
    if ((int)$user['verified'] === 1 || !empty($user['verified_at'])) {
        die("Your email is already verified.");
    } else {
        $update = $conn->prepare("UPDATE users SET verified = 1, verified_at = NOW() WHERE id = ?");
        $update->bind_param("i", $user['id']);
        if ($update->execute()) {
            // Redirect to login page
            header("Location: login.php");
            exit();
        } else {
            die("Failed to verify email. Please try again.");
        }
    }
}

$conn->close();
?>
