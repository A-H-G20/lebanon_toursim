<?php
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_number']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, phone_number, password, role, verified) VALUES (?, ?, ?, ?, 'admin', 1)");
    $stmt->bind_param("ssss", $name, $email, $phone, $password);

    if ($stmt->execute()) {
        header("Location: admin.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
