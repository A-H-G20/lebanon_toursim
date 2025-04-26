<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['reset_email'] ?? null;
    $enteredCode = implode('', $_POST['code']);

    if ($email) {
        $stmt = $conn->prepare("SELECT reset_code FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['reset_code'] === $enteredCode) {
            header("Location: reset_password.php");
            exit();
        } else {
            echo "<script>alert('Incorrect code'); window.history.back();</script>";
        }
    }
}
?>
