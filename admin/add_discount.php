<?php
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serial = trim($_POST['serial']);
    $description = trim($_POST['description']);
    $amount = intval($_POST['amount']);
    $status = trim($_POST['status']);

    $stmt = $conn->prepare("INSERT INTO discount (serial, description, amount, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $serial, $description, $amount, $status);

    if ($stmt->execute()) {
        header("Location: discount.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
